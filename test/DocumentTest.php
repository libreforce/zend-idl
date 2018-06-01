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

use Zend\Idl\Node;
use Zend\Idl\Document;

/**
 * @covers Zend\Idl\Document
 * @covers Zend\Idl\Node
 * @covers Zend\Idl\Exception\RuntimeException
 */
class DocumentTest extends TestCase
{

    public function testConstructor()
    {
        $doc = new Document();
        $this->assertEquals(Node::IDL_DOCUMENT_NODE, $doc->getNodeType());
        $this->assertEquals('document', $doc->getNodeName());
    }

    public function testCreateNodeException()
    {
        $this->expectException(Error::class);

        $doc = new Document();
        $element = $doc->createNode('unknow');

        $this->assertEquals(NULL, $element);
    }

    public function testAppendNode()
    {
        $doc = new Document();
        $module = $doc->createNode('module');
        $module->setName('Dom');
        $doc->appendNode($module);

        $this->assertEquals(Node::IDL_DOCUMENT_NODE, $doc->getNodeType());
        $this->assertEquals('document', $doc->getNodeName());
    }

    public function testToString()
    {
        $doc = new Document();

        $module = $doc->createNode('module');
        $module->setName('Dom');
        $doc->appendNode($module);

        $interface = $doc->createNode('interface');
        $interface->setName('Node');
        $module->appendNode($interface);

$output = "module Dom {
    interface Node {
    };
};
";
        $this->assertEquals($output, $doc->toString());
    }


    public function testOperationToString()
    {
        $doc = new Document();

        $module = $doc->createNode('module');
        $module->setName('Dom');
        $doc->appendNode($module);

        $interface = $doc->createNode('interface');
        $interface->setName('Node');
        //TODO:
        //$interface->setLocal();
        //$interface->setAbstract();
        //$interface->setInheritances('App::Object');
        $module->appendNode($interface);

        $operation = $doc->createNode('operation');
        $operation->setName('appendChild');
        $operation->setType('Node');
        $operation->setRaises('DOMException');
        $operation->addParameter(array('Name'=>'newChild', 'Type'=>'Node', 'Attribute'=>'in'));
        $interface->appendNode($operation);

$output = "module Dom {
    interface Node {
        Node appendChild( in Node newChild) raises (DOMException);
    };
};
";

        $this->assertEquals($doc->toString(), $output);
    }

    public function testAttributeToString()
    {
        $doc = new Document();

        $interface = $doc->createNode('interface');
        $interface->setName('Node');
        //$interface->setLocal();
        //$interface->setAbstract();
        //$interface->setInheritances('App::Object');
        $doc->appendNode($interface);

        $attribute = $doc->createNode('attribute');
        $attribute->setName('nodeName');
        $attribute->setType('DOMString');
        $attribute->setReadonly();
        $interface->appendNode($attribute);


// const unsigned short ELEMENT_NODE = 1;
$output = "interface Node {
    readonly attribute DOMString nodeName;
};
";

        $this->assertEquals($doc->toString(), $output);
    }

}
