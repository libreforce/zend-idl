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
 * A value IDL structure
 */
class Value extends Node
{

    /**
     * The data of value
     * @var mixed
     */
    protected $data;

    /**
     * The native type data of value
     * @var string
     */
    protected $type;

    /**
     * Set the data of the value
     *
     * @param mixed
     * @return Value
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the name of the module
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the native type of the value
     *
     * @param mixed
     * @return Value
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the native type of the value
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * toString
     * @return string
     */
    public function toString($indent=0)
    {
        $tab = str_repeat(Node::STRING_TAB, $indent);
        $output = '';

        $output .= $tab . $this->data;

        return $output;
    }

}
