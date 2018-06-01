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
    const IDL_UNKNOW_NODE     = 0;
    const IDL_ELEMENT_NODE    = 1; //'element';
    const IDL_DOCUMENT_NODE   = 2; //'document';
    const IDL_MODULE_NODE     = 3; //'module';
    const IDL_INTERFACE_NODE  = 4; //'interface';
    const IDL_OPERATION_NODE  = 5; //'operation';
    const IDL_TYPE_NODE       = 6; //'type';
    const IDL_PARAMETER_NODE  = 7; //'parameter';
    const IDL_ATTRIBUTE_NODE  = 8; //'attribute';
    const IDL_EXPORT_NODE     = 9; //'export';
    /**#@-*/

    const IDL_NODE_MAP = array(
        self::IDL_UNKNOW_NODE    => NULL,
        self::IDL_ELEMENT_NODE   => array('name' => 'element',   'class' => '\\Zend\\Idl\\Node',      'type' => self::IDL_ELEMENT_NODE),
        self::IDL_DOCUMENT_NODE  => array('name' => 'document',  'class' => '\\Zend\\Idl\\document',  'type' => self::IDL_DOCUMENT_NODE),
        self::IDL_MODULE_NODE    => array('name' => 'module',    'class' => '\\Zend\\Idl\\Module',    'type' => self::IDL_MODULE_NODE),
        self::IDL_INTERFACE_NODE => array('name' => 'interface', 'class' => '\\Zend\\Idl\\Iface',     'type' => self::IDL_INTERFACE_NODE),
        self::IDL_OPERATION_NODE => array('name' => 'operation', 'class' => '\\Zend\\Idl\\Operation', 'type' => self::IDL_OPERATION_NODE),
        self::IDL_TYPE_NODE      => array('name' => 'type',      'class' => '\\Zend\\Idl\\Type',      'type' => self::IDL_TYPE_NODE),
        self::IDL_PARAMETER_NODE => array('name' => 'parameter', 'class' => '\\Zend\\Idl\\Parameter', 'type' => self::IDL_PARAMETER_NODE),
        self::IDL_ATTRIBUTE_NODE => array('name' => 'attribute', 'class' => '\\Zend\\Idl\\Attribute', 'type' => self::IDL_ATTRIBUTE_NODE),
        self::IDL_EXPORT_NODE    => array('name' => 'export',    'class' => '\\Zend\\Idl\\Node',      'type' => self::IDL_EXPORT_NODE),
    );

    /**#@+
     * Tab charactere
     */
    const STRING_TAB     = '    ';
    /**#@-*/

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
     * Owner document
     * @var Zend\Idl\Document
     */
    protected $ownerDocument;

    /**
     * Constructor
     *
     * @param Node|null    $parent    Node container
     * @param string|null  $type      Force the node to be of a certain type
     */
    protected function __construct($ownerDocument = null, $type = null)
    {
        $this->ownerDocument = $ownerDocument;
        $this->nodeType = $type;
        $this->nodeName = self::IDL_NODE_MAP[$type]['name'];
    }

    /**
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     * @return Node
     */
    public function setOptions($options)
    {
        if (! is_array($options) && ! $options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable object; received "%s"',
                __METHOD__,
                is_object($options) ? get_class($options) : gettype($options)
            ));
        }
        foreach ($options as $optionName => $optionValue) {
            $methodName = 'set' . $optionName;
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($optionValue);
            } else {
                trigger_error("Method '$methodName' do not exists", E_USER_ERROR);
            }
        }
        return $this;
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

    /**
     * setNodeParent
     *
     * @return Node
     */
    public function setNodeParent($parent)
    {
        $this->nodeParent = $parent;

        return $this;
    }

    /**
     * appendNode
     *
     */
    public function appendNode($node)
    {
        $node->setNodeParent($this);
        $this->nodeList[] = $node;

        return $this;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $output = '';

        for ($i=0; $i<count($this->nodeList); $i++) {
            $node = $this->nodeList[$i];
            $output .= $node->toString($indent);
        }

        return $output;
    }

}
