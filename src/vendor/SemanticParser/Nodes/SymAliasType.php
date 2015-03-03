<?php

namespace vendor\SemanticParser\Nodes;

use vendor\TokenParser\Scanner;
use vendor\Exception\SemanticException;
use vendor\Utility\Console;

class SymAliasType extends SymType
{
	public $aliased = null;

	public function __construct($identifier, $aliased)
	{
		// Console::write("SymAliasType->construct\n");
		$this->identifier = $identifier;
		$this->aliased = $aliased;
	}

	public function printInfo($offset)
	{
		Console::write("{$offset}SymAliasType:\n");
		$offset .= '    ';
		Console::write("{$offset}{$this->identifier}\n");
		$this->aliased->printInfo($offset);
	}
}