<?php

	require "includes.php";

	print pageHeader("palette town");

	$itadaki	= getIS2();
	$rom		= $itadaki->rom();


	showPalettes($rom, 0x60000, 40);
	print "<br>-----------------------<br><br>";
	showPalettes($rom, 0x8B000, 64);

	function showPalettes($rom, $ofs, $cnt) {

		for ($i = 0; $i < $cnt; $i++) {
			$paletteData	= substr($rom, $ofs + 0x20 * $i, 0x20);
			$palette		= new SNES\Palette($paletteData);
			printf("<tt>\$%06X</tt>\n", $ofs + 0x20 * $i);
			print $palette->htmlize();
			print "<br>";
		}

	}


	print pageFooter();
