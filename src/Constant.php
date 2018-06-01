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
use \Zend\Idl\Value;

/**
 * A constant IDL structure
 */
class Constant extends Node
{

    /**
     * Name of the constant
     * @var string
     */
    protected $name;

    /**
     * Type of Constant
     * @var \Zend\Idl\Type
     */
    public $type;

    /**
     * Value of Constant
     * @var \Zend\Idl\Value
     */
    public $value;

    /**
     * Set the name of the constant
     *
     * @return Constant
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the constant
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the type of the constant
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
     * Get the type of the constant
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of the constant
     *
     * @param string|Value
     * @return self
     */
    public function setValue($value)
    {
        if ($value instanceof Value) {
            $this->value = $value;
        } else if (is_int($value)) {
            $node = $this->ownerDocument->createNode('value');
            $node->setNodeParent($this);
            //$node->setType('integer');
            $node->setData($value);
            $this->value = $node;
        } else if (is_string($value)) {
            $node = $this->ownerDocument->createNode('value');
            $node->setNodeParent($this);
            //$node->setType('string');// simple quote, double quote, HerDoc, ...
            $node->setData($value);
            $this->value = $node;
        } else {
            // throw new Exception();
        }
        return $this;
    }

    /**
     * Get the value of the constant
     *
     * @return Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $tab = str_repeat(Node::STRING_TAB, $indent);
        $output = '';

        $output .= $tab . 'const ' . $this->type->toString() . ' ' . $this->name;
        if ($this->value) {
            $output .=  ' = ' . $this->value->toString();
        }
        $output .= ';' . PHP_EOL;

        return $output;
    }

}
