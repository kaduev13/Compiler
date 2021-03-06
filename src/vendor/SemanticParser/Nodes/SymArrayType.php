<?php

namespace vendor\SemanticParser\Nodes;

use vendor\TokenParser\Scanner;
use vendor\SyntaxParser\Nodes\Node;
use vendor\Exception\SemanticException;
use vendor\Utility\Console;

class SymArrayType extends SymType
{
	public $dimensions = [];
	public $type = null;

	public function isDynamic()
	{
		return count($this->dimensions) < 1;
	}

	public function isAnonim()
	{
		return false;
	}

	public function __construct($scanner, $_symTable, $identifier)
	{
		$this->identifier = $identifier;
		$scanner->next();
		$token = $scanner->get();
		if ($token->isOperator('[')) {
			$scanner->next();
			while (!$scanner->get()->isOperator(']')) {
				//
				if ($scanner->get()->isIdentifier()) {
					$symbol = $_symTable->findRecursive($scanner->get()->getValue());
					if (is_a($symbol, 'vendor\SemanticParser\Nodes\SymSubrangeType')) {
						$this->dimensions[] = $symbol;
						$scanner->next();
					} else {
						$this->dimensions[] = new SymSubrangeAnonimType($scanner, $_symTable);
					}
				} else {
					$this->dimensions[] = new SymSubrangeAnonimType($scanner, $_symTable);
				}
				if ($scanner->get()->isOperator(',')) {
					$scanner->next();
					continue;
				} else if (!$scanner->get()->isOperator(']')) {
					SemanticException::expected($scanner, ['<OPERATOR \',\'>', '<OPERATOR \']\'>']);
				}
			}
			if (count($this->dimensions) < 1) {
				SemanticException::raw($scanner, 'Found array with no dimensions!');
			}
			$token = $scanner->nget();
		}
		if (!$token->isKeyword('of')) {
			SemanticException::expected($scanner, ['<KEYWORD \'of\'>']);
		}
		$scanner->next();
		$this->type = SymType::parse($scanner, $_symTable, null);
		// $scanner->next();
	}

	public function checkIndex($expression, $idx, $_symTable)
	{
		if (count($this->dimensions) <= $idx) {
			SemanticException::raw('Array has already completely dereferenced');
		}
		return SymSimpleType::equal($expression->symType, $_symTable->findRecursive('integer'));
	}

	public function printInfo($offset)
	{
		Console::write("{$offset}SymArrayType:\n");
		$offset .= '    ';
		Console::write("{$offset}{$this->identifier}\n");
		for ($i = 0; $i < count($this->dimensions); $i++) {
			$this->dimensions[$i]->printInfo($offset);
		}
		$this->type->printInfo($offset);
	}

	public function isConvertableTo($type)
	{
		if (is_a($type, 'vendor\SemanticParser\Nodes\SymAliasType')) {
			$type = $type->getBase();
		}
		if ($this->identifier == $type->identifier) {
			return true;
		}
		if (is_a($type, 'vendor\SemanticParser\Nodes\SymArrayType')) {
			$arrayType = $type->type;
			$thisType = $this->type;
			$class = get_class($arrayType);
			if ($class::equal($arrayType, $thisType)) {
				if ($type->isDynamic()) {
					return true;
				}
			}
		}
		return false;
	}
}