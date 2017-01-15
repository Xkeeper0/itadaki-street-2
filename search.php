<?php

	require "includes.php";
	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);
	$rom	= file_get_contents("ita2.sfc");

	print "<pre>";


	// Search for something a little less (more) naively
	// This is so complicated and dumb
	// Basically look for (A2|A9) ## ## (85|86) 05 ######### 22 3A 92
	// (LDA/LDX <pointer>, STA/STX $05, ..., JSR DrawTextbox)
	$matches	= array();
	$res		= preg_match_all("#(?:\xa2|\xa9)(..)(?:\x85|\x86)\x05.{0,10}\x22\x3a\x92#", $rom, $matches);

	print "                                                      u1 X  Y  W  H  u6 CO CX CY 10 TxtPt ???????????????\n";

	foreach ($matches[0] as $i => $match) {
		$dpos	= Utils::toIntLE($matches[1][$i]);
		$dposc	= 0x68000 + $dpos;

		$tb		= $itadaki->getTextbox($dposc);
		$tbta	= explode("\n", (string)$tb);

		printf("%3d : <a href='test.php?o=0x%05x'>%s</a>  %-30s   %s\n", $i, $dposc, bin2hex($matches[1][$i]), bin2hex($match), $tbta[0]);
	}

	print "\n\n\n";



	// "JSR $923A"
	$search	= "\x22\x3a\x92";

	$start	= 0;
	while ($pos = strpos($rom, $search, $start)) {

		$start	= $pos + 1;	// Move start to after this
		$data	= substr($rom, $pos - 8, 11);
		$dpos	= Utils::toIntLE(substr($data, 1, 2));
		$dposc	= 0x68000 + $dpos;

		$dprint	= sprintf("%s   %s   %s   %s",
				Utils::printableHex(substr($data, 0, 3)),
				Utils::printableHex(substr($data, 3, 2)),
				Utils::printableHex(substr($data, 5, 3)),
				Utils::printableHex(substr($data, 8, 3))
				);

		$ok		= "             ";
		if (ord($rom{$dposc}) == 0x02) {
			$ok	= "   (seems ok)";
		}

		$tb		= $itadaki->getTextbox($dposc);
		$tbta	= explode("\n", (string)$tb);


		#printf("%8x  %s -> <a href='test.php?o=0x%5x'>%04x</a>\n", $pos, Utils::printableHex($data), $dposc, $dpos);
		printf("%8x   %s -> <a href='test.php?o=0x%5x'>%04x</a>%s   %s\n", $pos, $dprint, $dposc, $dpos, $ok, $tbta[0]);

	}

	print "</pre>";
