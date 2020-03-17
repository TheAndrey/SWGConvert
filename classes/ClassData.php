<?php

class ClassData {

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $comment;
	/**
	 * @var MethodData[]
	 */
	public $methods = [];

	/**
	 * Get PHP code
	 * @return string
	 */
	public function getCode() {
		$code = '';
		if($this->comment) $code .= SWGHelper::makeComment($this->comment);

		$code .= "class {$this->name} {\n\n";
		foreach($this->methods as $method) {
			$code .= $method->getCode() . PHP_EOL;
		}
		$code .= '}';
		return $code;
	}
}