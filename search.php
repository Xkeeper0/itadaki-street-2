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
	$res		= preg_match_all("#(?:\xa2|\xa9)(..)(?:\x85|\x86)\x05.{0,10}\x22\x3a\x92#s", $rom, $matches, PREG_OFFSET_CAPTURE);
	$res		= preg_match_all("#(?:\xa2|\xa9)(..)(?:\x85|\x86)\x05.{0,10}\x22(.[\x80-\xFF])\x01#s", $rom, $matches, PREG_OFFSET_CAPTURE);

	$textboxes	= array();

	foreach ($matches[0] as $i => $match) {
		$dpos	= Utils::toIntLE($matches[1][$i][0]);
		$jsr	= Utils::toIntLE($matches[2][$i][0]);
		$dposc	= 0x68000 + $dpos;

		$tb		= $itadaki->getTextbox($dposc);
		$tbta	= explode("\n", (string)$tb);

		$name	= sprintf("textbox_%06x", $dposc);
		if (!isset($textboxes[$name])) {
			$textboxes[$name]	= array(
				'offset'		=> $dpos,
				'offsetROM'		=> $dposc,
				'description'	=> $tbta[0],
			);
		}
		$textboxes[$name]['references'][]	= array(
			'referenceROM'	=> $match[1],
			'subroutine'	=> $jsr,
			'code'			=> bin2hex($match[0]),
			);
		printf("%06x : %-40s   (%04X, -&gt; %04X)   <a href='test.php?o=0x%05x'>%s</a>\n", $match[1], bin2hex($match[0]), $dpos, $jsr, $dposc, $tbta[0]);
	}

	print "\n\n";

	foreach ($textboxes as $name => $textbox) {
		printf("<strong>%s - \$%06x (\$%04x): %s</strong>\n", $name, $textbox['offsetROM'], $textbox['offset'], $textbox['description']);
		foreach ($textbox['references'] as $ref) {
			printf("  Referenced at \$%06x, subroutine ptr \$%04x, code %s\n", $ref['referenceROM'], $ref['subroutine'], $ref['code']);
		}
	}

	//var_dump($textboxes);
	print "</pre>";
	print pageFooter();
