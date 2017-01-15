<?php

	require "ita2.php";

	print "<pre>";

	$itadaki	= new Translator("ita2.sfc", "is2.tbl", null);
	$rom	= file_get_contents("ita2.sfc");

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
