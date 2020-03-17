<?php

define('ROOT_PATH', __DIR__);

spl_autoload_register(function($class) {
	$class = str_replace('\\', '/', $class);
	$path = __DIR__ . '/classes/' . $class . '.php';
	if(is_file($path)) {
		/** @noinspection PhpIncludeInspection */
		include_once $path;
	}
});

$path = $_SERVER['argv'][1];
if(!is_file($path)) exit('Input does not exists: ' . $path);

$swagger = yaml_parse(file_get_contents($path));

$generator = new SwaggerConverter();
$generator->run($swagger);
