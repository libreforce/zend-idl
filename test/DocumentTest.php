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
        $doc = new Document();
        $element = $doc->createNode('unknow');

        $this->assertInstanceof('\\Zend\\Idl\\Node', $element);
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
}
