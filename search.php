<?php

	require "includes.php";

	print pageHeader("naive textbox searcher");

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);
	$rom	= file_get_contents("ita2.sfc");

	print "<pre>";


	// Search for something a little less (more) naively
	// This is so complicated and dumb
	// Basically look for (A2|A9) ## ## (85|86) 05 ######### 22 3A 92
	// (LDA/LDX <pointer>, STA/STX $05, ..., JSR DrawTextbox)
	$matches	= array();
	$res		= preg_match_all("#(?:\xa2|\xa9)(..)(?:\x85|\x86)\x05.{0,10}\x22\x3a\x92#", $rom, $matches, PREG_OFFSET_CAPTURE);

	foreach ($matches[0] as $i => $match) {
		$dpos	= Utils::toIntLE($matches[1][$i][0]);
		$dposc	= 0x68000 + $dpos;

		$tb		= $itadaki->getTextbox($dposc);
		$tbta	= explode("\n", (string)$tb);

		printf("%06x : %-30s   <a href='test.php?o=0x%05x'>%s</a>\n", $match[1], bin2hex($match[0]), $dposc, $tbta[0]);
	}

	print "</pre>";
	print pageFooter();
