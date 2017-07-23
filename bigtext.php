<?php

	if (isset($_GET['i'])) {
		if (substr($_GET['i'], 0, 2) == "0x") $_GET['i'] = hexdec($_GET['i']);
		$f		= sprintf("data/big-font/%03x.png", $_GET['i']);
		if (file_exists($f)) {
			header("Content-type: image/png");
			readfile($f);
			die();
		}
	}

	require "includes.php";
	$itadaki	= getIS2();
	$rom		= $itadaki->rom();

	if (isset($_GET['o'])) {
		$o	= hexdec($_GET['o']);
	} elseif (isset($_GET['i'])) {
		if (substr($_GET['i'], 0, 2) == "0x") $_GET['i'] = hexdec($_GET['i']);
		$o	= $_GET['i'] * 0x12;
	} else {
		die("You aren't supposed to access this directly...");
	}

	$idx	= $o / 0x12;
	$o		= $o + 0x5CD80;	// Constant offset into ROM

	$image	= imagecreatetruecolor(12, 12);

	$colors	= array(
				imagecolorallocate($image, 0x06, 0x06, 0x1c),
				imagecolorallocate($image, 0xE0, 0xE0, 0xE0),
			);

	$data	= substr($rom, $o, 0x12);

	for ($i = 0; $i < (12 * 12); $i++) {
		$x	= $i % 12;
		$y	= floor($i / 12);
		imagesetpixel($image, $x, $y, $colors[getBit($data, $i)]);
	}

	header("Content-type: image/png");
	imagepng($image);
	if (!file_exists("data/big-font/")) mkdir("data/big-font/");
	imagepng($image, sprintf("data/big-font/%03x.png", $idx));



	function getBit($s, $bit) {
		$pos	= (int)floor($bit / 8);
		$bit	= 7 - ($bit % 8);
		$chr	= ord($s{$pos});

		return ($chr >> $bit) & 0x01;
	}
