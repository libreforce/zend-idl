<?php
/**
 * Component candidate to Zend Framework (http://framework.zend.com/)
 *
 * @link      https://github.com/libreforce/zend-idl for the canonical source repository
 * @copyright Copyright (c) 2018 lib-reforce.
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
//declare(strict_types=1);

namespace Zend\Idl;


/**
 * Base class of IDL structure
 */
class Node
{
    /**#@+
     * Node types
     */
    const IDL_ELEMENT_NODE    = 1; //'element';
    const IDL_DOCUMENT_NODE   = 2; //'document';
    const IDL_MODULE_NODE     = 3; //'module';
    const IDL_INTERFACE_NODE  = 4; //'interface';
    const IDL_EXPORT_NODE     = 5; //'export';
    /**#@-*/

    const IDL_NODE_NAMES = array(
        '',
        'element',
        'document',
        'module',
        'interface',
        'export',
    );

    /**
     * Name of the node type
     * @var string
     */
    protected $nodeName;

    /**
     * Type of the node provided
     * @var string
     */
    protected $nodeType;

    /**
     * Node idl
     * @var Zend\Idl\Node
     */
    protected $nodeParent;

    /**
     * Node list
     * @var array of Zend\Idl\Node
     */
    protected $nodeList = [];

    /**
     * Constructor
     *
     * @param Node|null    $parent    Node container
     * @param string|null  $type      Force the node to be of a certain type
     */
    protected function __construct($parent = null, $type = null)
    {
        $this->nodeParent = $parent;
        $this->nodeType = $type;
        $this->nodeName = self::IDL_NODE_NAMES[$type];
    }

    /**
     * Get the type of the node
     *
     * @return integer
     */
    public function getNodeType()
    {
        return $this->nodeType;
    }

    /**
     * Get the name of the node
     *
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

}
