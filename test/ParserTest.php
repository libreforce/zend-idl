<?php
/**
 * Component candidate to Zend Framework (http://framework.zend.com/)
 *
 * @link      https://github.com/libreforce/zend-idl for the canonical source repository
 * @copyright Copyright (c) 2018 lib-reforce.
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Idl;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\Error\Error;

use \Zend\Idl\Document;
use \Zend\Idl\Parser;

/**
 * @covers Zend\Idl\Document
 * @covers Zend\Idl\Node
 * @covers Zend\Idl\Exception\RuntimeException
 */
class ParserTest extends TestCase
{
    protected $parser;

    public function setUp()
    {
        $this->parser = new \Zend\Idl\Parser();
    }

    public function testDocumentModule()
    {
        $content = "module Dom { };";

        $idl_document = NULL;
        $this->parser->parse($content, $idl_document);

        $this->assertInstanceof('\\Zend\\Idl\\Document', $idl_document);
        $idl_module = $idl_document->getNodeAt(0);
        $this->assertInstanceof('\\Zend\\Idl\\Module', $idl_module );
        $this->assertEquals('Dom', $idl_module->getName());
    }

    public function testModule()
    {
        $content = "module Dom { };";

        $idl_module = NULL;
        $this->parser->parse($content, $idl_module, 'module');

        $this->assertInstanceof('\\Zend\\Idl\\Module', $idl_module);
        $this->assertEquals('Dom', $idl_module->getName());
    }

    public function testDocumentInterface()
    {
        $content = "interface Node : DOMObject{ };";

        $idl_document = NULL;
        $this->parser->parse($content, $idl_document);

        $this->assertInstanceof('\\Zend\\Idl\\Document', $idl_document);

        $idl_interface = $idl_document->getNodeAt(0);
        $this->assertInstanceof('\\Zend\\Idl\\Iface', $idl_interface);
        $this->assertEquals('Node', $idl_interface->getName());
    }

    public function testInterface()
    {
        $content = "interface Node { };";

        $idl_interface = NULL;
        $this->parser->parse($content, $idl_interface, 'interface');

        $this->assertInstanceof('\\Zend\\Idl\\Iface', $idl_interface);
        $this->assertEquals('Node', $idl_interface->getName());
        $this->assertFalse($idl_interface->isLocal());
    }

    public function testInterfaceLocal()
    {
        $content = "local interface Node { };";

        $idl_interface = NULL;
        $this->parser->parse($content, $idl_interface, 'interface');

        $this->assertInstanceof('\\Zend\\Idl\\Iface', $idl_interface);
        $this->assertEquals('Node', $idl_interface->getName());
        $this->assertTrue($idl_interface->isLocal());
    }

    public function testInterfaceExtended()
    {
        $content = "interface Node : DOMObject{ };";

        $idl_interface = NULL;
        $this->parser->parse($content, $idl_interface, 'interface');

        $this->assertInstanceof('\\Zend\\Idl\\Iface', $idl_interface);
        $this->assertEquals('Node', $idl_interface->getName());

        // $idl_type = $idl_interface->getInheritanceAt(0);
        //$this->assertInstanceof('\\Zend\\Idl\\Type', $idl_type);
    }

    public function testOperation()
    {
        $content = "boolean hasAttributes()";

        $idl_operation = NULL;
        $this->parser->parse($content, $idl_operation, 'OpDecl');

        $this->assertInstanceof('\\Zend\\Idl\\Operation', $idl_operation);
        $this->assertEquals('hasAttributes', $idl_operation->getName() );
        $this->assertEquals('boolean', $idl_operation->getType()->getName() );
    }

    public function testOperationWithRaises()
    {
        $content = "boolean hasAttributes() raises (DOMException)";

        $idl_operation = NULL;
        $this->parser->parse($content, $idl_operation, 'OpDecl');

        $this->assertInstanceof('\\Zend\\Idl\\Operation', $idl_operation);
        $raise = $idl_operation->getRaiseAt(0);
        $this->assertEquals('DOMException', $raise->getName() );
    }

    public function testAttribute()
    {
        $content = "attribute DOMString nodeValue";

        $idl_attribute = NULL;
        $this->parser->parse($content, $idl_attribute, 'AttrDecl');

        $this->assertInstanceof('\\Zend\\Idl\\Attribute', $idl_attribute);
        $this->assertEquals('nodeValue', $idl_attribute->getName() );
        $this->assertEquals('DOMString', $idl_attribute->getType()->getName() );
        $this->assertFalse($idl_attribute->getReadonly() );
    }

    public function testAttributeReadonly()
    {
        $content = "readonly attribute DOMString nodeName";

        $idl_attribute = NULL;
        $this->parser->parse($content, $idl_attribute, 'AttrDecl');

        $this->assertInstanceof('\\Zend\\Idl\\Attribute', $idl_attribute);
        $this->assertEquals('nodeName', $idl_attribute->getName() );
        $this->assertEquals('DOMString', $idl_attribute->getType()->getName() );
        $this->assertTrue($idl_attribute->getReadonly() );
    }

    public function testConstant()
    {
        $content = "const unsigned short ELEMENT_NODE = 1";

        $idl_constant = NULL;
        $this->parser->parse($content, $idl_constant, 'ConstDecl');

        $this->assertInstanceof('\\Zend\\Idl\\Constant', $idl_constant);
        $this->assertEquals('ELEMENT_NODE', $idl_constant->getName() );
        $this->assertEquals('unsigned short', $idl_constant->getType()->getName() );
        $this->assertEquals(1, $idl_constant->getValue()->getData() );
        $this->assertEquals('int', $idl_constant->getValue()->getType() );
    }

    public function testDocumentWithModuleAndInterfaceAndOperation()
    {
        $content = "module Dom {
          interface Node : DOMObject {
            readonly attribute DOMString nodeName;
            const unsigned short ELEMENT_NODE = 1;
            boolean hasAttributes();
          };
        };";

        $idl_document = NULL;
        $this->parser->parse($content, $idl_document);

        $this->assertInstanceof('\\Zend\\Idl\\Document', $idl_document);
        $this->assertEquals(1, count($idl_document->getNodeList()) );

        $idl_module = $idl_document->getNodeAt(0);
        $this->assertInstanceof('\\Zend\\Idl\\Module', $idl_module);
        $this->assertEquals(1, count($idl_module->getNodeList()) );
        $this->assertEquals('Dom', $idl_module->getName());

        $idl_interface = $idl_module->getNodeAt(0);
        $this->assertInstanceof('\\Zend\\Idl\\Iface', $idl_interface);
        $this->assertEquals(3, count($idl_interface->getNodeList()) );
        //$idl_interface->getConstants()
        //$idl_interface->getAttributes()
        //$idl_interface->getOperations()
        $this->assertEquals('Node', $idl_interface->getName());

    }

    public function testDocumentToString()
    {
        $content = "module Dom {
          local interface Node : DOMObject {
            readonly attribute DOMString nodeName;
            const unsigned short ELEMENT_NODE = 1;
            boolean hasAttributes() raises (DOMException, DomRuntimeException);
          };
        };";
        $expect = "module Dom {
    local interface Node : DOMObject {
        readonly attribute DOMString nodeName;
        const unsigned short ELEMENT_NODE = 1;
        boolean hasAttributes( ) raises (DOMException, DomRuntimeException);
    };
};
";

        $idl_document = NULL;
        $this->parser->parse($content, $idl_document);
        $this->assertEquals($expect, $idl_document->toString());
    }

}
