<?php

class MethodData {

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $comment;

	/**
	 * Get PHP code
	 * @return string
	 */
	public function getCode() {
		$code = '';
		if($this->comment) $code .= SWGHelper::makeComment($this->comment, "\t");

		$code .= "\tpublic function {$this->name}() { }\n";
		return $code;
	}
}