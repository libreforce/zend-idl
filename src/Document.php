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

use \Zend\Idl\Node;

/**
 * Class used to initialize IDL document
 */
class Document extends Node
{

    /**
     * Version document
     * @var string
     */
    protected $version;

    /**
     * Encoding document
     * @var string
     */
    protected $encoding;

    /**
     * Constructor
     *
     */
    public function __construct(string $version='1.0', string $encoding='UTF-8')
    {
        parent::__construct(NULL, Node::IDL_DOCUMENT_NODE);
        $this->version = $version;
        $this->encoding = $encoding;
    }

    /**
     * Constructor
     *
     */
    public function createNode(string $name)
    {
        $class = '\\Zend\\Idl\\Node';
        $type = Node::IDL_UNKNOW_NODE;
        $filtered_array = array_filter(Node::IDL_NODE_MAP, function ($element) use ($name) { return ($element['name'] == $name); } );
        $filtered_array = array_pop($filtered_array);
        if (is_array($filtered_array)) {
            $type = $filtered_array['type'];
            $class = $filtered_array['class'];
        }
        if (Node::IDL_UNKNOW_NODE==$type) {
            trigger_error("Invalid argument. Class '$name' do not exists", E_USER_ERROR);
            return NULL;
        }

        $node = new $class($this, $type);

        return $node;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        return Node::toString($indent);
    }

}
