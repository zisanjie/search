<?php

/*
 * This file is part of the RollerworksSearch Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Doctrine\Orm\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Rollerworks\Component\Search\Doctrine\Dbal\QueryPlatformInterface;

/**
 * "RW_SEARCH_VALUE_CONVERSION(FieldMame, Column, Value, Strategy)".
 *
 * SearchValueConversion ::=
 *     "RW_SEARCH_VALUE_CONVERSION" "(" StringPrimary, StateFieldPathExpression,
 *      Literal "," Literal)"
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class SqlValueConversion extends FunctionNode
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * PathExpression or SqlFieldConversion.
     *
     * @var \Doctrine\ORM\Query\AST\Node
     */
    private $column;

    /**
     * @var int
     */
    private $valueIndex;

    /**
     * @var int
     */
    private $strategy;

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        /** @var \Closure $hintsValue */
        if (!$hintsValue = $sqlWalker->getQuery()->getHint('rw_where_builder')) {
            throw new \LogicException('Missing "rw_where_builder" hint for SearchValueConversion.');
        }

        /** @var QueryPlatformInterface $platform */
        /** @var mixed[] $parameters */
        list($platform, $parameters) = $hintsValue();

        return $platform->convertSqlValue(
            $parameters[$this->valueIndex],
            $this->fieldName,
            $this->column->dispatch($sqlWalker),
            $this->strategy
        );
    }

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->fieldName = $parser->Literal()->value;
        $parser->match(Lexer::T_COMMA);
        $this->column = $parser->ScalarExpression();
        $parser->match(Lexer::T_COMMA);
        $this->valueIndex = (int) $parser->Literal()->value;
        $parser->match(Lexer::T_COMMA);
        $this->strategy = (int) $parser->Literal()->value;

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
