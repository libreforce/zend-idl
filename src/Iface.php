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
 * A module IDL structure
 */
class Iface extends Node
{

    /**
     * Name of the module
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    public $isAbstract = FALSE;

    /**
     * @var boolean
     */
    public $isLocal = FALSE;

    /**
     * Inheritance interface
     *
     * @var array
     */
    public $inheritances = [];

    /**
     * Set the interface name
     *
     * @return Iface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the interface
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set if interface is abstract
     *
     * @param boolean $isAbstract
     * @return Iface
     */
    public function setAbstract($isAbstract=TRUE)
    {
        $this->isAbstract = $isAbstract;
        return $this;
    }

    /**
     * Get if interface is abstract
     *
     * @return boolean
     */
    public function getAbstract()
    {
        return $this->isAbstract;
    }

    /**
     * Set if interface is local
     *
     * @param boolean $isLocal
     * @return Iface
     */
    public function setLocal($isLocal=TRUE)
    {
        $this->isLocal = $isLocal;
        return $this;
    }

    /**
     * Get if interface is local
     *
     * @return boolean
     */
    public function isLocal()
    {
        return $this->isLocal;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $tab = str_repeat(Node::STRING_TAB, $indent);
        $output = '';

        $scope = '';
        if ($this->isLocal()) {
            $scope .= 'local ';
        }

        $glue = ' : ';
        $inheritances = '';
        for ($i=0; $i<count($this->inheritances); $i++) {
            $inheritance = $this->inheritances[$i];
            $inheritances .= $glue . $inheritance;
            $glue = ', ';
        }

        $output .= $tab . $scope . 'interface ' . $this->name . $inheritances . ' {' . PHP_EOL;
        $output .= Node::toString($indent+1);
        $output .= $tab . '};' . PHP_EOL;

        return $output;
    }

}
