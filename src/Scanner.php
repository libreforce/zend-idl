<?php

namespace Zend\Idl;


class Scanner
{
    protected $symbols;
    protected $text;
    protected $tokenLength;
    public/*protected*/ $tokens;
    public /*protected*/ $tokenIndex;
    protected $tokenLine;
    protected $tokenPosition;
    protected $transactionTokenIndexs;

    public function __construct() {
        $this->symbols = array();
        $this->text = NULL;
        $this->tokens = array();
        $this->tokenIndex = 0;
        $this->tokenLine = 0;
        $this->tokenPosition = 0;
        //$this->token = new Token("", Token::TOKEN_NONE);
        $this->transactionTokenIndexs = array();
    }

    public function addSymbol($symbol) {
        $this->symbols[] = $symbol;
    }

    public function removeSymbol() {

    }

    public function beginTransaction() {
        array_push($this->transactionTokenIndexs, $this->tokenIndex);
    }

    public function commitTransaction() {
        array_pop($this->transactionTokenIndexs);
    }

    public function rollbackTransaction() {
        $this->tokenIndex = array_pop($this->transactionTokenIndexs);
    }

    public function setText($text) {
        $this->text = $text;
        $keywords = preg_split('#([\{\}\[\]\(\),:;<=>\+\-\*/%&\^"\' ])#', $this->text, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

        $beginStringDeclare = FALSE;
        $beginStringChar = '"';// or "'"
        $stringDeclare = "";
        foreach($keywords as $i=>$keyword) {
            if ( FALSE == $beginStringDeclare ) {
                if ($keyword=='"') {// TODO if using '|"
                    // todo check if \"
                    //$beginStringDeclare = TRUE;
                    //$stringDeclare = "";
                } else if ( $keyword=="'" ) {
                    //$beginStringDeclare = TRUE;
                    //$stringDeclare = "";
                } else {
                    $keyword = trim($keyword);
                    $num = strlen($keyword);
                    if ($num==0) {
                        continue;
                    }
                    $this->tokens[] = $keyword;
                }
            } else {
                //$this->tokens[] = $keyword;
            }
        }
        $this->tokenLength = count($this->tokens);
    }
    /**
     * TODO: line, position of token + std::Pair(value, type)
    **/
    public function hasToken() {
        return $this->tokenIndex < $this->tokenLength;
    }
    public function getCurrentToken() {
        return $this->tokens[$this->tokenIndex];
    }

    public function getToken($offset=0) {
        return $this->tokens[$this->tokenIndex+$offset];
    }

    public function getNextToken() {
        return $this->tokens[$this->tokenIndex+1];
    }

    public function nextToken() {
        $this->tokenIndex++;
    }


}
