<?php

/**
 * Class SWGHelper
 * @author TheAndrey
 */
class SWGHelper {

	const SPACER = '    ';
	const OBJECT_TAGS = ['parameters', 'responses', 'schema', 'properties', 'items'];

	public static function makeComment($comment, $prefix = '') {
		$code = $prefix . "/**\n";
		foreach(explode("\n", rtrim($comment)) as $line) {
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
			$data = array_merge(['path' => $path], $data);
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
		$prefix = str_repeat(self::SPACER, $level);

		uksort($data, __CLASS__ . '::compareItems'); // Объекты в конец списка атрибутов

		// rename reference key
		if(array_key_exists('$ref', $data)) {
			$data['ref'] = $data['$ref'];
			unset($data['$ref']);
		}

		$code = "{$prefix}@SWG\\{$name}(\n";
		foreach($data as $key => $value) {
			$code .= self::processTagItem($key, $value, $level);
		}

		$code .= "{$prefix})";
		if($level > 0) $code .= ',';
		$code .= PHP_EOL;
		return $code;
	}

	/**
	 * Обработчик атрибута тега
	 * @param string $key
	 * @param mixed $value
	 * @param int $level
	 * @return string
	 */
	private static function processTagItem($key, $value, $level) {
		$prefix = str_repeat(self::SPACER, $level + 1);

		// Parameter
		if($level == 0 && $key == 'parameters') {
			$result = '';
			foreach($value as $item) {
				$result .= self::makeTag('Parameter', $item, $level + 1);
			}
			return $result;
		}

		// Response
		if($level == 0 && $key == 'responses') {
			$result = '';
			foreach($value as $status => $item) {
				$item = array_merge(['response' => $status], $item);
				$result .= self::makeTag('Response', $item, $level + 1);
			}
			return $result;
		}

		// Schema
		if($key == 'schema') {
			return self::makeTag('Schema', $value, $level + 1);
		}

		// Property
		if($key == 'properties') {
			$result = '';
			foreach($value as $property => $item) {
				$item = array_merge(['property' => $property], $item);
				$result .= self::makeTag('Property', $item, $level + 1);
			}
			return $result;
		}

		if($key == 'items' && is_array($value)) {
			return self::makeTag('Items', $value, $level + 1);
		}

		return $prefix . $key . '=' . self::exportVar($value) . ',' . PHP_EOL;
	}

	/**
	 * @param mixed $input
	 * @return string
	 */
	private static function exportVar($input) {
		if(is_string($input)) return '"' . addslashes($input) . '"';
		if(is_bool($input)) return var_export($input, true);

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

	public static function compareItems($a, $b) {
		return self::getTagWeight($a) <=> self::getTagWeight($b);
	}

	private static function getTagWeight($key) {
		return in_array($key, self::OBJECT_TAGS) ? 1 : 0;
	}
}