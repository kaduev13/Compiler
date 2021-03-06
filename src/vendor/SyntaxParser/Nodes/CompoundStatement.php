<?php

namespace vendor\SyntaxParser\Nodes;

use vendor\SyntaxParser\Nodes\Node;
use vendor\TokenParser\Scanner;
use vendor\Exception\SyntaxException;

class CompoundStatement extends Node
{
    private $statements = null;

    public function __construct($scanner, $_symTable)
    {
        $this->statements = [];
        parent::requireKeyword($scanner, 'begin');
        $scanner->next();
        while (!$scanner->get()->isKeyword('end')) {
            $this->statements[] = new Statement($scanner, $_symTable);
            parent::semicolonPass($scanner);
        }
        $scanner->next();
    }

    static public function smartParse($scanner, $_symTable)
    {
        if ($scanner->get()->isKeyword('begin')) {
            return new CompoundStatement($scanner, $_symTable);
        } else {
            return new Statement($scanner, $_symTable);
        }
    }

    public function toIdArray(&$id)
    {
        $node = [
            "id"       => $id,
            "name"     => "CompoundStatement",
            "children" =>
                array_map(function(&$statement) use (&$id) {
                    $id++;
                    return $statement->toIdArray($id);
                }, $this->statements)
        ];
        return $node;
    }
}
