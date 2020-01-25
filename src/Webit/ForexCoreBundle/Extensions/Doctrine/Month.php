<?php
namespace Webit\ForexCoreBundle\Extensions\Doctrine;
 
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
 
class Month extends FunctionNode {
 
    public $columns = array();
    public $needle;
    public $mode;
 
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
 
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
 
        do {
            $this->columns[] = $parser->StateFieldPathExpression();                        
        }
        while ($parser->getLexer()->isNextToken(Lexer::T_COMMA));
 
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);            
        $parser->match(Lexer::T_AS);            
        
        //$this->needle = $parser->InParameter();
 
        /*while ($parser->getLexer()->isNextToken(Lexer::T_STRING)) {
            $this->mode = $parser->Literal();
        }*/
 
    }
 
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $haystack = null;
 
        $first = true;
        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ') ';
            $haystack .= $column->dispatch($sqlWalker);
        }
 
        $query = "MONTH(" . $haystack .")";
 
 
        return $query;
    }
} 
