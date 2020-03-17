<?php

class SWGHelper {

	const SPACER = '    ';

	public static function makeComment($comment, $prefix = '') {
		$code = $prefix . "/**\n";
		foreach(explode("\n", $comment) as $line) {
			$code .= $prefix . ' * ' . rtrim($line) . PHP_EOL;
		}
		$code .= $prefix . " */\n";
		return $code;
	}

	/**
	 * Генерация документации к методу
	 * @param string $path
	 * @param array $methods
	 * @return string
	 */
	public static function methodDoc($path, $methods) {
		$doc = '';
		foreach($methods as $method => $data) {
			$doc .= self::makeTag(ucfirst($method), $data);
		}
		return $doc;
	}

	/**
	 * @param string $name Имя тега после @SWG
	 * @param array $data
	 * @param int $level
	 * @return string
	 */
	private static function makeTag($name, array $data, $level = 0) {
		$prefix_out = str_repeat(self::SPACER, $level);
		$prefix_in = str_repeat(self::SPACER, $level + 1);

		$code = "{$prefix_out}@SWG\\{$name}(\n";

		foreach($data as $key => $value) {

			if($level == 0 && $key == 'parameters') {

				foreach($value as $item) {
					$code .= self::makeTag('Parameter', $item, $level + 1);
				}

			} else {

				$code .= $prefix_in . $key . '=' . self::exportVar($value) . ',' . PHP_EOL;

			}
		}

		$code .= "{$prefix_out})\n";
		return $code;
	}

	/**
	 * @param mixed $input
	 * @return string
	 */
	private static function exportVar($input) {
		if(is_string($input)) {
			return '"' . addslashes($input) . '"';
		}

		if(is_array($input)) {
			$parts = [];
			$is_assoc = self::isAssoc($input);
			foreach($input as $key => $item) {
				$prefix = $is_assoc ? '' : '"' . addslashes($key) . '": ';
				$parts[] = $prefix . self::exportVar($item);
			}
			return '{' . implode(', ', $parts) . '}';
		}

		return $input;
	}

	/**
	 * Определяет ассоциативный массив
	 * @param array $arr
	 * @return bool
	 */
	public static function isAssoc(array $arr) {
		return array_keys($arr) === range(0, count($arr) - 1);
	}
}