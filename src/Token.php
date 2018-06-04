<?php

namespace Zend\Idl;

class Token
{
    const TOKEN_NONE = "\0";

    const TOKEN_CURLY_OPEN = 1;//      '{'
    const TOKEN_CURLY_CLOSE = 2;//     '}'
    const TOKEN_BRACE_OPEN = 3;//      '['
    const TOKEN_BRACE_CLOSE = 4;//     ']'
    const TOKEN_BRACKET_OPEN = 5;//    '('
    const TOKEN_BRACKET_CLOSE = 6;//   ')'
    const TOKEN_SEMICOLON = 7;//       ';'
    const TOKEN_COMMA = 8;//           ','
    const TOKEN_DOT = 9;//             '.'
    const TOKEN_DASH = 10;//           '-'
    const TOKEN_UNDERSCORE = 11;//     '_'

    const TOKEN_IDENTIFIER = 256;//    [a-zA-Z_][a-zA-Z_]*

    const TOKEN_UNKNOW = 257;
    const TOKEN_UNFOUND = 258;
    const TOKEN_ERROR = 259;

    const TOKEN_USER = 512;

    public $value;
    public $type;

    public function __construct() {
        $this->value = '';
        $this->type = TOKEN_NONE;
    }
}
