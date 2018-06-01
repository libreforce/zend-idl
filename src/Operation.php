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
use \Zend\Idl\Type;
use \Zend\Idl\Parameter;

/**
 * A module IDL structure
 */
class Operation extends Node
{

    /**
     * Name of the operation
     * @var string
     */
    protected $name;

    /**
     * Return type
     * @var \Zend\Idl\Type
     */
    public $type;

    /**
     * List of Parameter
     * @var array of \Zend\Idl\Parameter
     */
    public $parameters = [];

    /**
     * Raises
     * @var Node
     */
    public $raises;

    /**
     * Context
     * @var Node
     */
    //public $context;

    /**
     * Scope is static
     * @var boolean
     */
    public $isStatic = FALSE;

    /**
     * Set the name of the operation
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the operation
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the type of the operation
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

    // public function addRaise($raise)
    /**
     * Get the type of the operation
     *
     * @return string|Node
     */
    public function setRaises($raises)
    {
        if ($raises instanceof Node) {
            $this->raises = $raises;
        } else if ( is_string($raises) ) {
            $node = $this->ownerDocument->createNode('element');// raises ( ... )
            $node->setNodeParent($this);
            $this->raises = $node;

            $type = $this->ownerDocument->createNode('type');
            $type->setName($raises);
            $this->raises->appendNode($type);
        } else if ( is_array($raises) ) {
            $node = $this->ownerDocument->createNode('node');// raises ( ... )
            $node->setNodeParent($this);
            $this->raises = $node;
            for ($i=0; $i<count($raises); $i++) {
                $raise = $raises[$i];
                if ( $raise instanceof Type ) {
                    $this->raises->appendNode($raise);
                } else if (is_string($raise)) {
                    $type = $this->ownerDocument->createNode('type');
                    $type->setName($raise);
                    $this->raises->appendNode($type);
                } else {
                    trigger_error("Invalid raise in raises Operation", E_USER_ERROR);
                }
            }
        } else {
            trigger_error("Invalid argument for raises Operation", E_USER_ERROR);
        }

        return $this;
    }

    // setParameter($parameter, $index)

    /**
     * Push parameter to the operation
     *
     * @param array|Parameter
     * @return self
     */
    public function addParameter($parameter)
    {
        if ($parameter instanceof Parameter) {
            $this->parameters[] = $parameter;
        } else if (is_array($parameter)) {
            $node = $this->ownerDocument->createNode('parameter');
            $node->setOptions($parameter);
            $node->setNodeParent($this);
            $this->parameters[] = $node;
        } else {
            // throw new Exception();
        }
        return $this;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $output = '';
        $glue = '';

        $tab = str_repeat(Node::STRING_TAB, $indent);
        $type_name = $this->type == NULL ? 'void' : $this->type->toString();

        $output .= $tab . $type_name . ' ' . $this->name . '( ';
        for ($i=0; $i<count($this->parameters); $i++) {
            $node = $this->parameters[$i];
            $output .= $glue . $node->toString($indent);
            $glue = ', ';
        }

        $output .= ')';
        if ($this->raises != NULL) {
            $output .= ' raises (';
            $glue = '';
            for ($i=0; $i<count($this->raises->nodeList); $i++) {
                $raise = $this->raises->nodeList[$i];
                $output .= $glue . $raise->toString();
                $glue = ', ';
            }
            $output .= ')';
        }
        $output .= ';' . PHP_EOL;

        return $output;
    }

}
