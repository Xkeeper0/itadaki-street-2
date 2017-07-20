<?php

	// Require the important stuff

	require "data/translator.php";

	require "data/utils.php";

	require "data/itadakistreet2/itadakistreet2.php";
	require "data/itadakistreet2/textbox.php";
	require "data/itadakistreet2/decompressor.php";
	require "data/itadakistreet2/compressor.php";
	require "data/itadakistreet2/street.php";
	require "data/itadakistreet2/street/square.php";

	require "data/snes/palette.php";

	require "html/html.php";

	// Define if we are in CLI mode or not (for some scripts)
	define("IS_CLI", (PHP_SAPI == "cli" ? true : false));
