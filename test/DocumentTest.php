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

    public function testConstructorDocument()
    {
        $doc = new Document();
        $this->assertEquals(Node::IDL_DOCUMENT_NODE, $doc->getNodeType());
        $this->assertEquals('document', $doc->getNodeName());
    }
}
