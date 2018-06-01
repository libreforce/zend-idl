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
 * A type IDL structure
 */
class Type extends Node
{

    /**
     * Name of the type
     * @var string
     */
    protected $name;

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
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $tab = str_repeat(Node::STRING_TAB, $indent);
        $output = '';

        $type_name = $this->name == NULL ? 'void' : $this->name;

        $output .= $type_name;

        return $output;
    }

}
