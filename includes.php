<?php

	error_reporting(E_ALL | E_NOTICE);

	// Require the important stuff
	function is2_autoloader($class) {
		$file	= __DIR__ ."/lib/". str_replace('\\', '/',$class) .".php";
		//printf("Autoloading: %s from %s<br>\n", $class, $file);
		if (file_exists($file)) {
			require_once($file);
		}
	}
	spl_autoload_register("is2_autoloader");

	// Require the basic layout engine
	// @TODO: Make a real one for viewing Stuff
	require "html/html.php";

	// Define if we are in CLI mode or not (for some scripts)
	define("IS_CLI", (PHP_SAPI == "cli" ? true : false));

	// Go ahead and just load everything here.
	// Lazy code for lazy people.
	function getIS2() {
		$itadaki	= new \IS2\ItadakiStreet2("data/is2.sfc", "data/tables/is2-smalltext.tbl", "data/tables/is2-bigtext.tbl");
		return $itadaki;
	}
