<?php

	// Require the important stuff

	require "data/translator.php";
	require "data/itadakistreet2.php";
	require "data/utils.php";
	require "data/textbox.php";
	require "data/decompressor.php";
	require "data/compressor.php";
	require "html/html.php";

	// Define if we are in CLI mode or not (for some scripts)
	define("IS_CLI", (PHP_SAPI == "cli" ? true : false));
