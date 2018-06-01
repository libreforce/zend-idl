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
class Parameter extends Node
{

    /**
     * Name of the type
     * @var string
     */
    protected $name;

    /**
     * Return type
     * @var \Zend\Idl\Type
     */
    public $type;

    /**
     * Return attribute of Parameter
     * @var string
     */
    public $attribute;

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
     * Set the attribute of the Parameter
     *
     * @return \Zend\Idl\Parameter
     */
    public function setAttribute($attribute)
    {
        if ( in_array($attribute, array('in', 'out', 'inout')) ) {
            $this->attribute = $attribute;
        } else {
            trigger_error("Attribute '$attribute' is not a valid attribute", E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Get the name of the module
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $tab = str_repeat(Node::STRING_TAB, $indent);
        $output = '';

        $parameter_type = $this->type == NULL ? 'void' : $this->type->toString();
        if ( is_string($this->name) ) {
            $parameter_name =  $this->name;
        } else {
            trigger_error("Parameter do not have name", E_USER_ERROR);
            $parameter_name =  '?';
        }
        if ( is_string($this->attribute) ) {
            $parameter_attribute =  $this->attribute . ' ';
        } else {
            $parameter_attribute = '';
        }

        $output .= $parameter_attribute . $parameter_type . ' ' . $parameter_name;

        return $output;
    }

}
