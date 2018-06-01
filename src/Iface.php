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
     * Get the type of the node
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
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $tab = str_repeat(Node::STRING_TAB, $indent);
        $output = '';

        $output .= $tab . 'interface ' . $this->name . ' {' . PHP_EOL;
        $output .= Node::toString($indent+1);
        $output .= $tab . '};' . PHP_EOL;

        return $output;
    }

}
