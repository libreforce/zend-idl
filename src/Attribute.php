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
 * A parameter IDL structure
 */
class Attribute extends Node
{

    /**
     * Name of the type
     * @var string
     */
    protected $name;

    /**
     * Type of Attribute
     * @var \Zend\Idl\Type
     */
    public $type;

    /**
     * Attribute is readonly
     * @var boolean
     */
    public $isReadonly = FALSE;

    /**
     * Get the name of the type
     *
     * @return integer
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the module
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the type of the parameter
     *
     * @param string|Type
     * @return self
     */
    public function setType($type)
    {
        if ($type instanceof Type) {
            $this->type = $type;
        } else if (is_string($type)) {
            $node = $this->ownerDocument->createNode('type');
            $node->setNodeParent($this);
            $node->setName($type);
            $this->type = $node;// $node = $this->ownerDocument->getTypedef($type)->type;
        } else {
            // throw new Exception();
        }
        return $this;
    }

    /**
     * Get the type of the operation
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Put Attribute to readonly
     *
     * @return integer
     */
    public function setReadonly($isReadonly=TRUE)
    {
        $this->isReadonly = $isReadonly;
        return $this;
    }

    /**
     * Attribute is readonly
     *
     * @return boolean
     */
    public function getReadonly()
    {
        return $this->isReadonly;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $tab = str_repeat(Node::STRING_TAB, $indent);
        $output = '';

        $readonly = $this->isReadonly ? 'readonly ' : '';

        $output .= $tab . $readonly . 'attribute ' . $this->type->toString() . ' ' . $this->name . ';' . PHP_EOL;

        return $output;
    }

}
