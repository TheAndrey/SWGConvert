<?php

/**
 * Преобразует файл Swagger в PHP аннотации
 * @author TheAndrey
 */
class SwaggerConverter {

	/**
	 * @var string
	 */
	private $outputDir;
	/**
	 * @var ClassData[]
	 */
	private $controllers = [];
	/**
	 * @var ClassData[]
	 */
	private $models = [];

	public function __construct() {
		$this->outputDir = ROOT_PATH . '/generated';
	}

	/**
	 * @param array $swagger Структура SWG
	 */
	public function run(array $swagger) {
		if(!file_exists($this->outputDir)) mkdir($this->outputDir);

		foreach($swagger['paths'] as $path => $data) {
			$this->processPath($path, $data);
		}

		foreach($swagger['definitions'] as $name => $data) {
			$this->processModel($name, $data);
		}

		$this->generateClasses('controller', $this->controllers);
		$this->generateClasses('model', $this->models);
	}

	/**
	 * @param string $path
	 * @param array $data
	 */
	private function processPath($path, array $data) {
		$path_arr = explode('/', $path);
		$path_arr = array_filter($path_arr, function($part) {
			return preg_match('/^[a-z0-9]+$/i', $part);
		});
		$path_arr = array_values($path_arr);
		if(empty($path_arr)) throw new \DomainException('Can not parse path: ' . $path);

		// Controller
		$controller_name = $path_arr[0];
		$controller = $this->controllers[strtolower($controller_name)];
		if(empty($controller)) {
			$controller = new ClassData();
			$controller->name = ucfirst($controller_name) . 'Controller';
			$this->controllers[strtolower($controller_name)] = $controller;
		}

		// Method
		$method = new MethodData();
		$method->name = ($path_arr[1] ?? 'index') . 'Action';
		$method->comment = SWGHelper::methodDoc($path, $data);

		$controller->methods[] = $method;
	}

	/**
	 * @param string $name
	 * @param array $data
	 */
	private function processModel($name, $data) {
		$model = new ClassData();
		$model->name = $name;
		$model->comment = SWGHelper::modelDefinition($name, $data);
		$this->models[] = $model;
	}

	private function generateClasses($dir, $list) {
		$dir = $this->outputDir . '/' . $dir;
		if(!file_exists($dir)) mkdir($dir);

		foreach($list as $class) {
			$code = $this->generateClass($class);
			file_put_contents($dir . '/' . $class->name . '.php', $code);
		}
	}

	/**
	 * @param ClassData $class
	 * @return string
	 */
	private function generateClass($class) {
		$code = '<?php' . PHP_EOL . PHP_EOL;
		$code .= $class->getCode() . PHP_EOL;
		return $code;
	}

}