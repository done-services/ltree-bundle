<?php

namespace Pvsaintpe\LTreeBundle\DqlFunction;

use Doctrine\ORM\Query\AST\{ASTException, Node, Subselect};
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class LTreeOperatorFunction extends FunctionNode
{
    public const FUNCTION_NAME = 'ltree_operator';

    protected Subselect|Node|string $operator;

    protected Node $first;

    protected Node $second;

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('(%s %s %s)',
            $this->first->dispatch($sqlWalker),
            $this->operator->value,
            $this->second->dispatch($sqlWalker)
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
        $this->operator = $parser->StringExpression();

        $parser->match(TokenType::T_COMMA);
        $this->second = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
