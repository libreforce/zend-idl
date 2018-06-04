<?php

namespace Zend\Idl;

function trace($msg) {
    //echo $msg . PHP_EOL;
}

function trace_key($msg) {
    //echo $msg . PHP_EOL;
}

class Parser
{
    protected $scanner = NULL;
    protected $idl_document = NULL;

    protected $currentModuleName = NULL;
    //protected $currentInterfaceName = NULL;

    protected function getScanner() {
        if ($this->scanner ==NULL) {
            $this->scanner = new Scanner();
            //$this->scanner->addSymbol("module", 1);
            //$this->scanner->addSymbol("interface", 2);
            //$this->scanner->addSymbol("attribute", 3);
            //$this->scanner->addSymbol("readonly", 4);
            //$this->scanner->addSymbol("const", 4);
            // ...
        }
        return $this->scanner;
    }

    protected function setScanner($scanner) {
        $this->scanner = $scanner;
    }

    protected function hasToken() {
        return $this->getScanner()->hasToken();
    }
    protected function getToken($offset=0) {
        if ( !$this->hasToken() ) {
            return NULL;
        }
        return $this->getScanner()->getToken($offset);
        //return $this->getScanner()->getCurrentToken();
    }
    protected function getNextToken() {
        return $this->getScanner()->getNextToken();
    }
    protected function nextToken() {
        $this->getScanner()->nextToken();
    }

    /**
     * Parse text
     * @param String $content : The text to parse
     * @param $rule not used
     */
    public function parse($content, &$idl_node=NULL, $rule='specification') {
        trace('parse->');
        $this->getScanner()->setText($content);
        $this->idl_document = new \Zend\Idl\Document();

        $except = Token::TOKEN_UNFOUND;
        $func = 'parse' . ucfirst($rule);
        if ( is_callable(array($this, $func), TRUE ) ) {
            $this->getScanner()->beginTransaction();
            $except = call_user_func_array(array($this, $func), array(&$idl_node) );
            if ( Token::TOKEN_NONE == $except ) {
                $this->getScanner()->commitTransaction();
            } else {
                $this->getScanner()->rollbackTransaction();
            }
        }

        return $except;
    }

    /**
     * <specification> ::= <import>* <definition>+
     *
     * @param $idl_document \Zend\Idl\IDLNode
     */
    protected function parseSpecification(&$idl_document) {
        trace('parseSpecification->');
        $this->getScanner()->beginTransaction();

        $import = 0;
        $definition = 0;
        while ($this->parseImport() == Token::TOKEN_NONE) {
            $import++;
        }
        $idl_container = NULL;
        while ($this->parseDefinition($idl_container) == Token::TOKEN_NONE) {
            $definition++;
            $this->idl_document->appendNode($idl_container);
            $idl_container = NULL;
        }
        if ($import>=0 && $definition>=1) {
            $idl_document = $this->idl_document;
            $this->getScanner()->commitTransaction();
            return Token::TOKEN_NONE;
        }
        $this->getScanner()->rollbackTransaction();
        return Token::TOKEN_UNFOUND;
    }

    /**
     * <import> ::= "import" <imported_scope> ";"
     */
    protected function parseImport() {
        trace('parseImport->');
        $this->getScanner()->beginTransaction();
        $token = $this->getToken();
        if ($token=="import") {
            $this->nextToken();
            if ( Token::TOKEN_NONE == $this->parseImportedScope() ) {
                 $this->getScanner()->commitTransaction();
                 return Token::TOKEN_NONE;
            } else {
                 $this->getScanner()->rollbackTransaction();
                 return Token::TOKEN_IDENTIFIER;
            }
        }
        /*
        echo $this->getNextToken() . PHP_EOL;
        $this->nextToken();
        echo $this->getNextToken() . PHP_EOL;
        */
        $this->getScanner()->rollbackTransaction();
        return "import";
    }

    /**
     * <imported_scope> ::= <scoped_name>
     *                    | STRING_LITERAL
     */
    protected function parseImportedScope() {
        trace('parseImportedScope->');

        return Token::TOKEN_NONE;
    }

    /**
     * <identifier> ::= /^[a-zA-Z_][a-zA-Z0-9_]*$/
     */
    protected function parseIdentifier(&$identifier) {
        trace('parseIdentifier->');
        $token = $this->getToken();
        if ( preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $token) ) {
            $identifier = $token;
            trace_key($token);
            $this->nextToken();
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_IDENTIFIER;
    }

    /**
     *
     * <scoped_name> ::= ("::")? ID ("::" ID)*
     *
     * <scoped_name> ::= <identifier>
     *                 | "::" <identifier>
     *                 | <scoped_name> "::" <identifier>
     * @return Array of namespace
     * exemple: ::My::Project => Array('', 'My', 'Project')
     * exemple: Us => Array('Us')
     */
    protected function parseScopedName(&$scoped_name) {
        trace('parseScopedName->');
        $this->getScanner()->beginTransaction();

        $scoped_name = '';

        // optional
        if ( ':' == $this->getToken() && ':' == $this->getNextToken() ) {
            $this->nextToken();
            $this->nextToken();
            //
            $scoped_name .= '::';
        }

        // required
        $identifier = '';
        if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_IDENTIFIER;
        }
        $scoped_name .= $identifier;

        $success = TRUE;
        while (1) {
            $name = '';
            if ( ':' == $this->getToken() && ':' == $this->getNextToken() ) {
                $this->nextToken();
                $this->nextToken();
                //
                $name .= '::';
            } else {
                break;
            }

            $identifier = '';
            if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
                $success = FALSE;
                break;
            }
            $name .= $identifier;

            $scoped_name .= $name;
        }

        if ( FALSE == $success ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_NONE;
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /*
     * <interface_name> ::= <scoped_name>
     */
    protected function parseInterfaceName(&$interfaceName) {
        trace('parseInterfaceName->');
        $this->getScanner()->beginTransaction();
        $name = '';
        if ( Token::TOKEN_NONE == $this->parseScopedName($name) ) {
            $interfaceName = $name;
            $this->getScanner()->commitTransaction();
            return Token::TOKEN_NONE;
        }

        $this->getScanner()->rollbackTransaction();
        return Token::TOKEN_IDENTIFIER;
    }

    /*
     * <interface_inheritance> ::= ":" <interface_name> ("," <interface_name>)*
     */
    protected function parseInterfaceInheritance(&$interfaceInheritance) {
        trace('parseInterfaceInheritance->');
        $this->getScanner()->beginTransaction();

        if ( ':' == $this->getToken() ) {
            $this->nextToken();
            $interfaceName = '';
            if ( Token::TOKEN_NONE != $this->parseInterfaceName($interfaceName) ) {
                $this->getScanner()->rollbackTransaction();
                return Token::TOKEN_IDENTIFIER;
            }
            $interfaceInheritance[] = $interfaceName;

            while ( ',' == $this->getToken() ) {// FIXME: use while(TRUE)
                $this->nextToken();
                if ( Token::TOKEN_NONE == $this->parseInterfaceName($interfaceName) ) {
                    $interfaceInheritance[] = $interfaceName;
                } else {
                    // TODO:
                    break;
                }
                $interfaceName = '';
            }

            $this->getScanner()->commitTransaction();
            return Token::TOKEN_NONE;
        }

        $this->getScanner()->rollbackTransaction();
        return ':';
    }

    /**
     * <definition> ::= <type_dcl> ";"
     *                | <const_dcl> ";"
     *                | <except_dcl> ";"
     *                | <interface> ";"
     *                | <module> ";"
     *                | <value> ";"
     *
     * @var $idl_definition :
     */
    protected function parseDefinition(&$idl_definition) {
        trace('parseDefinition->');
        if (FALSE == $this->hasToken() ) {
            return Token::TOKEN_UNFOUND;
        }
        $this->getScanner()->beginTransaction();

        $idl_container = NULL;
        if ( Token::TOKEN_NONE == $this->parseModule($idl_container) ) {
            $idl_definition = $idl_container;
        } else if ( Token::TOKEN_NONE == $this->parseInterface($idl_container) ) {
            $idl_definition = $idl_container;
        } else {
            echo 'Token failled: ' . $this->getScanner()->tokenIndex . PHP_EOL;
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }


    /**
     * <interface> ::= <interface_header> LEFT_BRACE <interface_body> RIGHT_BRACE
     *
     * interface_header: ('abstract' | 'local')? 'interface' ID (interface_inheritance_spec)?
     *
     *
     *
     *
     * abstract interface MyObject:MyObject::
     *
     */
    protected function parseInterface(&$idl_interface) {
        trace('parseInterface->');
        if (FALSE == $this->hasToken() ) {
            return Token::TOKEN_UNFOUND;
        }
        $this->getScanner()->beginTransaction();

        $interface = $this->idl_document->createNode('interface');

        $token = $this->getToken();
        if ('abstract' == $token ) {
            $this->nextToken();
            $interface->isAbstract = TRUE;
        } else if ('local' == $token) {
            $this->nextToken();
            $interface->isLocal = TRUE;
        }

        $token = $this->getToken();
        if ('interface'==$token) {
            if (FALSE == $this->hasToken() ) {
                return Token::TOKEN_UNFOUND;
            }
            $this->nextToken();

            $identifier = '';
            if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
                $this->getScanner()->rollbackTransaction();
                return Token::TOKEN_IDENTIFIER;
            }
            $interface->setName($identifier);

            // $listInterfaceNamed
            // $this->getScanner()->beginTransaction();
            $inheritances = array();
            if ( Token::TOKEN_NONE == $this->parseInterfaceInheritance($inheritances) ) {
                $interface->inheritances = $inheritances;
            }
            // $this->getScanner()->commitTransaction();
            // $this->getScanner()->rollbackTransaction();

            $token = $this->getToken();
            if ('{' != $token ) {
                $this->getScanner()->rollbackTransaction();
                return '{';
            }
            if (FALSE == $this->hasToken() ) {
                return Token::TOKEN_UNFOUND;
            }
            $this->nextToken();

            if ( Token::TOKEN_NONE != $this->parseInterfaceBody($interface) ) {
                //return Token::TOKEN_ERROR;
            }

            $token = $this->getToken();
            if ('}' != $token ) {
                $this->getScanner()->rollbackTransaction();
                return '}';
            }
            if (FALSE == $this->hasToken() ) {
                return Token::TOKEN_UNFOUND;
            }
            $this->nextToken();

            $token = $this->getToken();
            if (';' != $token ) {
                $this->getScanner()->rollbackTransaction();
                return ';';
            }
            if (FALSE == $this->hasToken() ) {
                return Token::TOKEN_UNFOUND;
            }
            $this->nextToken();
        }

        $idl_interface = $interface;

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <type_decl> ::=
     */
    protected function parseTypeDecl() {
        trace('parseTypeDecl->');
        // TODO:
        return Token::TOKEN_UNFOUND;
    }



    /**
     * <signed_short_int> ::= "short"
     */
    protected function parseSignedShortInt(&$type) {
        if ( 'short' == $this->getToken() ) {
            $this->nextToken();
            $type->setName( 'short');
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }
    /**
     * <signed_long_int> ::= "long"
     */
    protected function parseSignedLongInt(&$type) {
        if ( 'long' == $this->getToken() ) {
            $this->nextToken();
            $type->setName( 'long');
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }
    /**
     * <signed_longlong_int> ::= "long" "long"
     */
    protected function parseSignedLonglongInt(&$type) {
        if ( 'long' == $this->getToken() && 'long' == $this->getNextToken() ) {
            $this->nextToken();
            $this->nextToken();
            $type->setName( 'long long');
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }

    /**
     * <signed_int> ::= <signed_short_int>
     *                | <signed_long_int>
     *                | <signed_longlong_int>
     */
    protected function parseSignedInt(&$type) {
        if ( Token::TOKEN_NONE == $this->parseSignedShortInt($type) ) {
            return Token::TOKEN_NONE;
        } else if( Token::TOKEN_NONE == $this->parseSignedLongInt($type) ) {
            return Token::TOKEN_NONE;
        } else if( Token::TOKEN_NONE == $this->parseSignedLonglongInt($type) ) {
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }



    /**
     * <signed_short_int> ::= "unsigned" "short"
     */
    protected function parseUnsignedShortInt(&$type) {
        trace('parseUnsignedShortInt->');
        if ( 'unsigned' == $this->getToken()
             && 'short' == $this->getNextToken() ) {
            $this->nextToken();
            $this->nextToken();
            $type->setName( 'unsigned short');
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }
    /**
     * <signed_long_int> ::= "unsigned" "long"
     */
    protected function parseUnsignedLongInt(&$type) {
        if ( 'unsigned' == $this->getToken() && 'long' == $this->getToken() ) {
            $this->nextToken();
            $this->nextToken();
            $type->setName( 'unsigned long');
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }
    /**
     * <signed_longlong_int> ::= "unsigned" "long" "long"
     */
    protected function parseUnsignedLonglongInt(&$type) {
        if ( 'unsigned' == $this->getToken()
              && 'long' == $this->getToken(+1)
              && 'long' == $this->getToken(+2) ) {
            $this->nextToken();
            $this->nextToken();
            $this->nextToken();
            $type->setName( 'unsigned long long');
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }

    /**
     * <unsigned_int> ::= <unsigned_short_int>
     *                  | <unsigned_long_int>
     *                  | <unsigned_longlong_int>
     */
    protected function parseUnsignedInt(&$type) {
        trace('parseUnsignedInt->');
        if ( Token::TOKEN_NONE == $this->parseUnsignedShortInt($type) ) {
            return Token::TOKEN_NONE;
        } else if( Token::TOKEN_NONE == $this->parseUnsignedLongInt($type) ) {
            return Token::TOKEN_NONE;
        } else if( Token::TOKEN_NONE == $this->parseUnsignedLonglongInt($type) ) {
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_ERROR;
    }

    /**
     * <integer_type> ::= <signed_int> | <unsigned_int>
     */
    protected function parseIntegerType(&$type) {
        trace('parseIntegerType->');
        if ( Token::TOKEN_NONE == $this->parseSignedInt($type) ) {
            return Token::TOKEN_NONE;
        } else if( Token::TOKEN_NONE == $this->parseUnsignedInt($type) ) {
            return Token::TOKEN_NONE;
        }
        return Token::TOKEN_IDENTIFIER;
    }

    /**
     * <const_type> ::= <integer_type>
     *                | <char_type>
     *                | <wide_char_type>
     *                | <boolean_type>
     *                | <floating_pt_type>
     *                | <string_type>
     *                | <wide_string_type>
     *                | <fixed_pt_const_type>
     *                | <scoped_name>
     *                | <octet_type>
     */
    protected function parseConstType(&$type) {
        trace('parseConstType->');
        // TODO: continue implementation here!!!
        if ( Token::TOKEN_NONE == $this->parseIntegerType($type) ) {
            return Token::TOKEN_NONE;
        } else {
        }
        return Token::TOKEN_IDENTIFIER;
    }

    protected function parseConstExp(&$idl_value) {
        trace('$this->parseConstExp();');
        return $this->parseOrExpr($idl_value);
    }
    /**
     * <or_expr> = <xor_expr> (PIPE <xor_expr>)*
     */
    protected function parseOrExpr(&$idl_value) {
        trace('parseOrExpr->');
        return $this->parseXorExpr($idl_value);
        //return Token::TOKEN_UNFOUND;
    }
    /**
     * <xor_expr> ::= <and_expr> (CARET <and_expr>)*
     */
    protected function parseXorExpr(&$idl_value) {
        trace('parseXorExpr->');
        return $this->parseAndExpr($idl_value);
    }
    /**
     * <and_expr> ::= <shift_expr> (AMPERSAND <shift_expr>)*
     */
    protected function parseAndExpr(&$idl_value) {
        trace('parseAndExpr->');
        return $this->parseShiftExpr($idl_value);
    }
    /**
     * <shift_expr> ::= <add_expr> ((RIGHT_SHIFT | LEFT_SHIFT) <add_expr>)*
     */
    protected function parseShiftExpr(&$idl_value) {
        trace('parseShiftExpr->');
        return $this->parseAddExpr($idl_value);
    }
    /**
     * <add_expr> ::= <mult_expr> ((PLUS | MINUS) <mult_expr>)*
     */
    protected function parseAddExpr(&$idl_value) {
        trace('parseAddExpr->');
        return $this->parseMultExpr($idl_value);
    }
    /**
     * <mult_expr> ::= <unary_expr> (('*' | SLASH | PERCENT) <unary_expr>)*
     */
    protected function parseMultExpr(&$idl_value) {
        trace('parseMultExpr->');
        return $this->parseUnaryExpr($idl_value);
    }
    /**
     * <unary_expr> ::= <unary_operator> <primary_expr>
     *                | <primary_expr>
     */
    protected function parseUnaryExpr(&$idl_value) {
        trace('parseUnaryExpr->');
        return $this->parsePrimaryExpr($idl_value);
        //return $this->parseUnaryOperator();
    }
    /**
     * <unary_operator> ::= (MINUS | PLUS | TILDE)
     */
    protected function parseUnaryOperator(&$idl_value) {
    }
    /**
     * <primary_expr> ::= <scoped_name>
     *                  | <literal>
     *                  | LEFT_BRACKET <const_exp> RIGHT_BRACKET
     */
    protected function parsePrimaryExpr(&$idl_value) {
        trace('parsePrimaryExpr->');
        return $this->parseLiteral($idl_value);
    }
/*
FLOATING_PT_LITERAL
   : ('0' .. '9') + '.' ('0' .. '9')* EXPONENT? FLOAT_TYPE_SUFFIX?
   | '.' ('0' .. '9') + EXPONENT? FLOAT_TYPE_SUFFIX?
   | ('0' .. '9') + EXPONENT FLOAT_TYPE_SUFFIX?
   | ('0' .. '9') + EXPONENT? FLOAT_TYPE_SUFFIX
   ;
 EXPONENT
   : ('e' | 'E') (PLUS | MINUS)? ('0' .. '9') +

LNUM          [0-9]+
DNUM          ([0-9]*[\.]{LNUM}) | ({LNUM}[\.][0-9]*)
EXPONENT_DNUM [+-]?(({LNUM} | {DNUM}) [eE][+-]? {LNUM})
*/


    protected const HEX_LITERAL            = '#^0[xX][0-9a-fA-F]+$#';
    protected const INTEGER_LITERAL        = '#^0$|^[1-9][0-9]*$#';
    protected const STRING_LITERAL         = '#^".*"$#';
    //protected const WIDE_CHARACTER_LITERAL = '#^$#';
    protected const FLOATING_PT_LITERAL    = '#^[0-9]*\.[0-9]+$|^[0-9]+\.[0-9]*$|^[0-9]+\.[0-9]*[eE][+-]?[0-9]+$#';
    protected const BOOLEAN_LITERAL        = '#^(TRUE)|(FALSE)$#i';
    /**
     * <literal> ::= HEX_LITERAL
     *             | INTEGER_LITERAL
     *             | STRING_LITERAL
     *             | WIDE_STRING_LITERAL
     *             | CHARACTER_LITERAL
     *             | WIDE_CHARACTER_LITERAL
     *             | FIXED_PT_LITERAL
     *             | FLOATING_PT_LITERAL
     *             | BOOLEAN_LITERAL
     */
    protected function parseLiteral(&$idl_value) {
        trace('parseLiteral->');
        $this->getScanner()->beginTransaction();

        $token = $this->getToken();
        if (FALSE) {
        } else if ( preg_match(self::HEX_LITERAL, $token) ) {
            trace_key( 'A hex literal');
        } else if ( preg_match(self::INTEGER_LITERAL, $token) ) {
            trace_key('A integer literal "' . $token . '"');
            //$idl_value->type = 'INTEGER';
            //$idl_value->literal = $token;
            //$idl_value->value = intval($token);
            $idl_value->setData( intval($token) );
            $idl_value->setType( 'int' );
        } else if ( preg_match(self::STRING_LITERAL, $token) ) {
            trace_key('A integer literal');
        } else if ( preg_match(self::FLOATING_PT_LITERAL, $token) ) {
            trace_key( 'A float');
        } else if ( preg_match(self::BOOLEAN_LITERAL, $token) ) {
            trace_key('A boolean');
        } else {
            trace_key('Unknow');
            //$type = new IDLType();
            //$type->name;
            //$type->value;
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }

        $this->nextToken();
        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * '"' (ESCAPE_SEQUENCE | ~ ('\\' | '"'))* '"'
     */
    protected $string_literal = '#"\\|"#';
    protected function parseStringLiteral() {
        trace('parseStringLiteral->');

    }

/*
    const ESCAPE_SEQUENCE = "#\\(b|t|n|f|r|\"|'|\\)|#";
    const OCTAL_ESCAPE    = "#\\(b|t|n|f|r|\"|\\)|#";
    const UNICODE_ESCAPE  = "#\\u#";
    const HEX_DIGIT       = '#[0-9]|[a-f]|[A-F]#';

fragment ESCAPE_SEQUENCE
   : '\\' ('b' | 't' | 'n' | 'f' | 'r' | '"' | '\'' | '\\') | UNICODE_ESCAPE | OCTAL_ESCAPE
   ;


fragment OCTAL_ESCAPE
   : '\\' ('0' .. '3') ('0' .. '7') ('0' .. '7') | '\\' ('0' .. '7') ('0' .. '7') | '\\' ('0' .. '7')
   ;


fragment UNICODE_ESCAPE
   : '\\' 'u' HEX_DIGIT HEX_DIGIT HEX_DIGIT HEX_DIGIT
   ;
*/

    /**
     * <const_decl> ::= "const" <const_type> <identifier> "=" <const_exp>
     */
    protected function parseConstDecl(&$idl_constant) {
        trace('parseConstDecl->');
        $this->getScanner()->beginTransaction();

        $constant = $this->idl_document->createNode('constant');

        //$startIndex = $this->getScanner()->index;
        if ( 'const' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return 'const';
        }
        $this->nextToken();

        /// type of type
        $idl_type = $this->idl_document->createNode('type');//new \Zend\Idl\IDLType();
        if ( Token::TOKEN_NONE != $this->parseConstType($idl_type) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $constant->setType($idl_type);

        /// name
        $identifier = '';
        if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $constant->setName( $identifier);

        if ( '=' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return '=';
        }
        $this->nextToken();

        /// value of type
        $idl_value = $this->idl_document->createNode('value');//new \Zend\Idl\IDLValue();
        if ( Token::TOKEN_NONE != $this->parseConstExp($idl_value) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $constant->setValue($idl_value);

        //var_dump($idl_constant);
        $idl_constant = $constant;

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * floating_pt_type ::= ('float' | 'double' | 'long' 'double')
     */
    protected function parseFloatingPtType(&$idl_type) {
        trace('parseFloatingPtType->');
        $this->getScanner()->beginTransaction();

        if ( 'float' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'float');
        } else if ( 'double' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'double');
        } else if ( 'long' == $this->getToken() && 'double' == $this->getToken(+1) ) {
            $this->nextToken();
            $this->nextToken();
            $idl_type->setName( 'long double');
        } else {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * char_type ::= 'char'
     */
    protected function parseCharType(&$idl_type) {
        trace('parseCharType->');
        $this->getScanner()->beginTransaction();

        if ( 'char' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'char');
        } else {
            $this->getScanner()->rollbackTransaction();
            return 'char';
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * wchar_type ::= 'wchar'
     */
    protected function parseWideCharType(&$idl_type) {
        trace('parseWideCharType->');
        $this->getScanner()->beginTransaction();

        if ( 'wchar' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'wchar');
        } else {
            $this->getScanner()->rollbackTransaction();
            return 'wchar';
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * boolean_type ::= 'boolean'
     */
    protected function parseBooleanType(&$idl_type) {
        trace('parseBooleanType->');
        $this->getScanner()->beginTransaction();

        if ( 'boolean' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'boolean');
        } else {
            $this->getScanner()->rollbackTransaction();
            return 'boolean';
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * octet_type ::= 'octet'
     */
    protected function parseOctetType(&$idl_type) {
        trace('parseOctetType->');
        $this->getScanner()->beginTransaction();

        if ( 'octet' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'octet');
        } else {
            $this->getScanner()->rollbackTransaction();
            return 'octet';
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * any_type ::= 'any'
     */
    protected function parseAnyType(&$idl_type) {
        trace('parseAnyType->');
        $this->getScanner()->beginTransaction();

        if ( 'any' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'any');
        } else {
            $this->getScanner()->rollbackTransaction();
            return 'any';
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /*
     * object_type ::= 'Object'
     */
    protected function parseObjectType(&$idl_type) {
        trace('parseObjectType->');
        $this->getScanner()->beginTransaction();

        if ( 'Object' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'Object');
        } else {
            $this->getScanner()->rollbackTransaction();
            return 'Object';
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * value_base_type ::= 'ValueBase'
     */
    protected function parseValueBaseType(&$idl_type) {
        trace('parseValueBaseType->');
        $this->getScanner()->beginTransaction();

        if ( 'ValueBase' == $this->getToken() ) {
            $this->nextToken();
            $idl_type->setName( 'ValueBase');
        } else {
            $this->getScanner()->rollbackTransaction();
            return 'ValueBase';
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <base_type_spec> ::= <floating_pt_type>
     *                    | <integer_type>
     *                    | <char_type>
     *                    | <wide_char_type>
     *                    | <boolean_type>
     *                    | <octet_type>
     *                    | <any_type>
     *                    | <object_type>
     *                    | <value_base_type>
     */
    protected function parseBaseTypeSpec(&$idl_type) {
        trace('parseBaseTypeSpec->');
        $this->getScanner()->beginTransaction();

        /*
        parseFloatingPtType();
        parseIntegerType();
        parseCharType();
        parseWideCharType();
        parseBooleanType();
        parseOctetType();
        parseAnyType();
        parseObjectType();// DOMString
        parseValueBaseType();
        */

        if ( Token::TOKEN_NONE == $this->parseFloatingPtType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseIntegerType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseCharType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseWideCharType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseBooleanType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseOctetType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseAnyType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseObjectType($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseValueBaseType($idl_type) ) {
        } else {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        trace_key($idl_type->getName() );

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <positive_int_const> ::= <const_exp>
     */
    protected function parsePositiveIntConst() {
        trace('parsePositiveIntConst->');
        $idl_value = new \Zend\Idl\IDLValue();
        return $this->parseConstExp($idl_value);
    }

    /**
     * <string_type> ::= 'string' '<' <positive_int_const> '>'
     *                 | 'string'
     */
    protected function parseStringType() {
        trace('parseStringType->');
        $this->getScanner()->beginTransaction();

        if ( 'string' == $this->getToken() ) {
            $this->nextToken();
            if ( '<' == $this->getToken() ) {
                $this->nextToken();
                if ( Token::TOKEN_NONE != $this->parsePositiveIntConst() ) {
                    $this->getScanner()->rollbackTransaction();
                    return Token::TOKEN_UNFOUND;
                }
                if ( '>' != $this->getToken() ) {
                    $this->getScanner()->rollbackTransaction();
                    return Token::TOKEN_UNFOUND;
                }
                $this->nextToken();
            }
        } else {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <param_type_spec> ::= <base_type_spec>
     *                     | <string_type>
     *                     | <wide_string_type>
     *                     | <scoped_name>
     */
    protected function parseParamTypeSpec(&$idl_type) {
        trace('parseParamTypeSpec->');
        $this->getScanner()->beginTransaction();
        $scoped_name = NULL;

        if ( Token::TOKEN_NONE == $this->parseBaseTypeSpec($idl_type) ) {
        } else if ( Token::TOKEN_NONE == $this->parseStringType() ) {
        } else if ( FALSE/*Token::TOKEN_NONE == $this->parseWideStringType()*/ ) {
        } else if ( Token::TOKEN_NONE == $this->parseScopedName($scoped_name) ) {
            $idl_type->setName( $scoped_name);
        } else {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }


        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }


    // TODO:
    // readonly attribute DOMString nodeName, nodeType;
    // readonly attribute DOMString nodeName raises(DOMException);
    /*
     * <raises_expr> ::= 'raises' '(' <scoped_name> (',' <scoped_name>)* ')'
     */
    /*
     * <ID> ::= LETTER (LETTER | ID_DIGIT)*
     */
    /*
     * <simple_declarator> ::= <ID>
     */
    /*
     * <readonly_attr_declarator> ::= <simple_declarator> <raises_expr>
     *                              | <simple_declarator> (',' <simple_declarator>)*
     */
    protected function parseReadonlyAttrDeclarator(&$idl_decl) {
        trace('parseReadonlyAttrDeclarator->');
        $this->getScanner()->beginTransaction();

        $identifier = NULL;
        if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_decl = $identifier;

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }


    /**
     * <readonly_attr_spec> ::= "readonly" "attribute"
     *                          <param_type_spec> <readonly_attr_declarator>
     */
    protected function parseReadonlyAttrSpec(&$idl_attribute) {
        trace('parseReadonlyAttrSpec->');
        $this->getScanner()->beginTransaction();

        if ( 'readonly' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return 'readonly';
        }
        $this->nextToken();
        $idl_attribute->setReadonly();

        if ( 'attribute' != $this->getToken()) {
            $this->getScanner()->rollbackTransaction();
            return 'attribute';
        }
        $this->nextToken();

        $idl_type = $this->idl_document->createNode('type');//new \Zend\Idl\IDLType();
        if ( Token::TOKEN_NONE != $this->parseParamTypeSpec($idl_type) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_attribute->type = $idl_type;

        // ID = nodeName
        $idl_id = NULL;
        if ( Token::TOKEN_NONE != $this->parseReadonlyAttrDeclarator($idl_id) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_attribute->setName( $idl_id);

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /*
     * <get_excep_expr> ::=
     *
     */
    /*
     * <set_excep_expr> ::=
     *
     */
    /*
     * <attr_raises_expr> ::= get_excep_expr (set_excep_expr)?
     *                      | set_excep_expr
     */
    /*
     * <attr_declarator> ::= simple_declarator attr_raises_expr
     *                     | simple_declarator (COMA simple_declarator)*
     */
    /**
     * <attr_spec> ::= "attribute" <param_type_spec> <attr_declarator>
     */
    protected function parseAttrSpec(&$idl_attribute) {
        trace('parseAttrSpec->');
        $this->getScanner()->beginTransaction();

        if ( 'attribute' != $this->getToken()) {
            $this->getScanner()->rollbackTransaction();
            return 'attribute';
        }
        $this->nextToken();

        $idl_type = $this->idl_document->createNode('type');//new \Zend\Idl\IDLType();
        if ( Token::TOKEN_NONE != $this->parseParamTypeSpec($idl_type) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_attribute->type = $idl_type;

        // TODO: attr_declarator
        $identifier = NULL;
        //if ( Token::TOKEN_NONE != $this->parseAttrDeclarator() ) {
        if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_attribute->setName( $identifier);

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <attr_decl> ::= <readonly_attr_spec> | <attr_spec>
     */
    protected function parseAttrDecl(&$idl_attribute) {
        trace('parseAttrDecl->');
        $this->getScanner()->beginTransaction();

        $idl_attribute = $this->idl_document->createNode('attribute');

        if ( Token::TOKEN_NONE == $this->parseReadonlyAttrSpec($idl_attribute) ) {
            $this->getScanner()->commitTransaction();
            return Token::TOKEN_NONE;
        } else if ( Token::TOKEN_NONE == $this->parseAttrSpec($idl_attribute) ) {
            $this->getScanner()->commitTransaction();
            return Token::TOKEN_NONE;
        }

        $idl_attribute = NULL;

        $this->getScanner()->rollbackTransaction();
        return Token::TOKEN_UNFOUND;
    }

    /*
     * <param_type_spec> === parseParamTypeSpec(\Zend\Idl\IDLType $idl_type)
     */
    /* simple_declarator === <ID> */
    /*
     * <param_attribute> ::= 'in'
     *                     | 'out'
     *                     | 'inout'
     */
    /*
     * <param_decl> ::= param_attribute param_type_spec simple_declarator
     */
    protected function parseParamDecl(&$idl_argument) {
        trace('parseParamDecl->');
        $this->getScanner()->beginTransaction();

        $token = $this->getToken();
        if ( 'in' == $token
          || 'out' == $token
          || 'inout' == $token ) {
            $this->nextToken();
        }

        $idl_type = $this->idl_document->createNode('type');//new \Zend\Idl\IDLType();
        if ( Token::TOKEN_NONE != $this->parseParamTypeSpec($idl_type) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_argument->setType($idl_type);

        $identifier = NULL;
        if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_argument->setName( $identifier);

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }


    /*
     * <parameter_decls> ::= '(' param_decl (COMA param_decl)* ')'
     *                     | '(' ')'
     */
    protected function parseParameterDecls(&$idl_arguments) {
        trace('parseParameterDecls->');
        $this->getScanner()->beginTransaction();

        if ( '(' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $this->nextToken();

        $idl_argument = $this->idl_document->createNode('parameter');//new \Zend\Idl\IDLArgument();
        if ( Token::TOKEN_NONE == $this->parseParamDecl($idl_argument) ) {
            $idl_arguments[] = $idl_argument;
            while (TRUE) {
                if ( ',' == $this->getToken() ) {
                    $this->nextToken();
                    $idl_argument = $this->idl_document->createNode('parameter');//new \Zend\Idl\IDLArgument();
                    if ( Token::TOKEN_NONE != $this->parseParamDecl($idl_argument) ) {
                        $this->getScanner()->rollbackTransaction();
                        return Token::TOKEN_UNFOUND;
                    }
                    $idl_arguments[] = $idl_argument;
                } else {
                    break;
                }
            }
        }

        if ( ')' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $this->nextToken();

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /*
     * <raises_expr> ::= 'raises' '(' <scoped_name> (COMA <scoped_name>)* ')'
     *
     * @param array $raises
     * @return self
     */
    protected function parseRaisesExpr(&$idl_raises) {
        trace('parseRaisesExpr->');
        $this->getScanner()->beginTransaction();

        $raises = $this->idl_document->createNode('element');

        if ( 'raises' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $this->nextToken();

        if ( '(' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $this->nextToken();

        $raise = NULL;
        $scoped_name = NULL;
        if ( Token::TOKEN_NONE == $this->parseScopedName($scoped_name) ) {
            $raise = $this->idl_document->createNode('type');
            $raise->setName($scoped_name);
            $raises->appendNode($raise);
            while (TRUE) {
                if ( ',' == $this->getToken() ) {
                    $this->nextToken();
                    $scoped_name = NULL;
                    if ( Token::TOKEN_NONE != $this->parseScopedName($scoped_name) ) {
                        $this->getScanner()->rollbackTransaction();
                        return Token::TOKEN_UNFOUND;
                    }
                    $raise = $this->idl_document->createNode('type');
                    $raise->setName($scoped_name);
                    $raises->appendNode($raise);
                } else {
                    break;
                }
            }
        } else {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }

        if ( ')' != $this->getToken() ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $this->nextToken();
        $idl_raises = $raises;

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /*
     * <context_expr> ::= 'context' '(' STRING_LITERAL (COMA STRING_LITERAL)* ')'
     */
    protected function parseContextExpr(/*&$export*/) {
        trace('parseContextExpr->');
        return Token::TOKEN_UNFOUND;
    }

    /*
     * <op_type_spec> ::= <param_type_spec> | 'void'
     *
     * @param \Zend\Idl\Type $idl_type
     */
    protected function parseOpTypeSpec(&$idl_type) {
        trace('parseOpTypeSpec->');
        $this->getScanner()->beginTransaction();

        if ( Token::TOKEN_NONE == $this->parseParamTypeSpec($idl_type) ) {
        } else if ( 'void' == $this->getToken() ) {
            $idl_type->setName('void');
            $this->nextToken();
        } else {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /*
     * <op_attribute> ::= 'oneway'
     */
    /*
     * <op_decl> ::= <op_attribute>? <op_type_spec> <ID>
     *               <parameter_decls> <raises_expr>? <context_expr>?
     *
     */
    protected function parseOpDecl(&$idl_operation) {
        trace('parseOpDecl->');
        $this->getScanner()->beginTransaction();

        if ( 'oneway' == $this->getToken() ) {
            $this->nextToken();
        }

        if ($idl_operation==NULL) {
            $idl_operation = $this->idl_document->createNode('operation');
        }

        $idl_type = $this->idl_document->createNode('type');//new \Zend\Idl\IDLType();
        if ( Token::TOKEN_NONE != $this->parseOpTypeSpec($idl_type) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $idl_operation->setType($idl_type);

        $identifier = NULL;
        if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_IDENTIFIER;
        }
        $idl_operation->setName($identifier);

        $idl_arguments = array();
        if ( Token::TOKEN_NONE != $this->parseParameterDecls($idl_arguments) ) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_NONE;
        }
        $idl_operation->arguments = $idl_arguments;

        $raises = NULL;
        if ( Token::TOKEN_NONE == $this->parseRaisesExpr($raises) ) {
            $idl_operation->setRaises($raises);
        }
        if ( Token::TOKEN_NONE == $this->parseContextExpr() ) {
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <export> ::= <type_decl> ";"
     *            | <const_decl> ";"
     *            | <except_decl> ";"
     *            | <attr_decl> ";"
     *            | <op_decl> ";"
     *            | <type_id_decl> ";"
     *            | <type_prefix_decl> ";"
     */
    protected function parseExport(&$idl_interface) {
        trace('parseExport->');
        $this->getScanner()->beginTransaction();

        if ($idl_interface==NULL) {
            $idl_interface =  $this->idl_document->createNode('interface');
        }

        $idl_constant =  NULL;//new \Zend\Idl\IDLConstant();
        $idl_attribute = NULL;//new \Zend\Idl\IDLAttribute();
        $idl_operation = NULL;//new \Zend\Idl\IDLOperation();

        if ( Token::TOKEN_NONE==$this->parseTypeDecl() ) {
        } else if ( Token::TOKEN_NONE==$this->parseConstDecl($idl_constant) ) {
            //var_dump($idl_constant);
            $idl_interface->constants[] = $idl_constant;
            $idl_interface->appendNode($idl_constant);
        //} else if ( Token::TOKEN_NONE==$this->parseExceptDecl($idl_constant) ) {
        } else if ( Token::TOKEN_NONE==$this->parseAttrDecl($idl_attribute) ) {
            //var_dump($idl_attribute);
            $idl_interface->attributes[] = $idl_attribute;
            $idl_interface->appendNode($idl_attribute);
        } else if ( Token::TOKEN_NONE==$this->parseOpDecl($idl_operation) ) {
            //var_dump($idl_operation);
            $idl_interface->operations[] = $idl_operation;
            $idl_interface->appendNode($idl_operation);
        //} else if ( Token::TOKEN_NONE==$this->parseTypeIdDecl($idl_) ) {
        //} else if ( Token::TOKEN_NONE==$this->parseTypePrefixDecl($idl_) ) {
        } else {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }

        if ( ';' != $this->getToken()) {
            $this->getScanner()->rollbackTransaction();
            return Token::TOKEN_UNFOUND;
        }
        $this->nextToken();

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <interface_body> ::= <export>*
     */
    protected function parseInterfaceBody(&$idl_interface) {
        trace('parseInterfaceBody->');
        $this->getScanner()->beginTransaction();

        while ( Token::TOKEN_NONE == $this->parseExport($idl_interface) ) {
        }

        $this->getScanner()->commitTransaction();
        return Token::TOKEN_NONE;
    }

    /**
     * <module> ::= "module" <identifier> "{" <definition>+ "}"
     */
    protected function parseModule(&$idl_module) {
        trace('parseModule->');
            /*$this->modules = array();
            $this->modules['dom'] = array();
            $this->modules['dom']['foo'] = array();
            $this->currentModuleName = array('dom', 'foo');*/

        if ( FALSE == $this->hasToken() ) {
            return Token::TOKEN_UNFOUND;
        }

        $this->getScanner()->beginTransaction();
        $module = $this->idl_document->createNode('module');

        $token = $this->getToken();
        if ('module'==$token) {
            $this->nextToken();
            $identifier = '';
            if ( Token::TOKEN_NONE != $this->parseIdentifier($identifier) ) {
                $this->getScanner()->rollbackTransaction();
                return Token::TOKEN_IDENTIFIER;
            }
            //$this->nextToken();
            $module->setName($identifier);
            $this->currentModuleName = $identifier;
            //echo $identifier . PHP_EOL;

            $token = $this->getToken();
            if ('{' != $token ) {
                $this->getScanner()->rollbackTransaction();
                return '{';
            }
            $this->nextToken();

            $idl_definition = NULL;
            if ( Token::TOKEN_NONE == $this->parseDefinition($idl_definition) ) {
                $module->appendNode($idl_definition);
            }

            $token = $this->getToken();
            if ('}' != $token ) {
                $this->getScanner()->rollbackTransaction();
                return '}';
            }
            $this->nextToken();

            $token = $this->getToken();
            if (';' != $token ) {
                $this->getScanner()->rollbackTransaction();
                return ';';
            }
            $this->nextToken();

            $idl_module = $module;

            $this->getScanner()->commitTransaction();
            return Token::TOKEN_NONE;
        }

        $this->getScanner()->rollbackTransaction();
        return 'module';
    }

}
