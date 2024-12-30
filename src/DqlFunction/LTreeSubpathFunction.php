<?php

namespace Pvsaintpe\LTreeBundle\DqlFunction;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class LTreeSubpathFunction extends FunctionNode
{
    public const FUNCTION_NAME = 'ltree_subpath';

    protected Node $first;

    protected Node $second;

    protected ?Node $third = null;

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return $this->third === null
            ? sprintf(
                'subpath(%s, %s)',
                $this->first->dispatch($sqlWalker),
                $this->second->dispatch($sqlWalker)
            )
            : sprintf(
                'subpath(%s, %s, %s)',
                $this->first->dispatch($sqlWalker),
                $this->second->dispatch($sqlWalker),
                $this->third->dispatch($sqlWalker)
            );
    }

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->first = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_COMMA);
        $this->second = $parser->ArithmeticPrimary();

        // parse third parameter if available
        if (TokenType::T_COMMA === $parser->getLexer()->lookahead->type) {
            $parser->match(TokenType::T_COMMA);
            $this->third = $parser->ScalarExpression();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
