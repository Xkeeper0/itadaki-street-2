
<?php

	require "includes.php";

	print pageHeader("vwf converter");

	#header("Content-type: text/plain");

	try {
		$image	= imagecreatefrompng("data/vwf.png");
		$widths = array(
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	 4,  6,  6, 12,  8, 11, 11,  4,  5,  5,  6,  6,  4,  6,  4,  7,
	 8,  4,  8,  8,  8,  8,  8,  8,  8,  8,  4,  4,  5,  6,  5,  8,
	12, 10,  8,  8,  8,  8,  8,  9,  8,  4,  7, 10,  8, 12,  9,  8,
	 8,  9,  9,  8,  8,  9, 10, 12, 12,  8,  8,  4,  7,  4,  6, 12,
	12,  7,  7,  6,  7,  6,  6,  7,  8,  4,  5,  8,  4, 12,  8,  6,
	 7,  7,  6,  6,  4,  7,  8, 12,  7,  8,  6,  7, 12,  7, 11, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
	12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
		);

		$bytes	= array();

		for ($imgy = 0; $imgy < 16; $imgy++) {
			for ($imgx = 0; $imgx < 16; $imgx++) {
				$tilenum = ($imgy * 16) + $imgx;
				$bytes[$tilenum] = array();
				$bits = 0;
				$curbyte = 0;
				for ($tiley = 0; $tiley < 12; $tiley++) {
					for ($tilex = 0; $tilex < ($widths[$tilenum] <= 4 ? 4 : ($widths[$tilenum] <= 8 ? 8 : 12)); $tilex++) {
						$thisbit = (imagecolorat($image, ($imgx * 12) + $tilex, ($imgy * 12) + $tiley) === 0xFFFFFF ? 1 : 0);
						$curbyte = ($curbyte << 1) | $thisbit;
						$bits++;
						if ($bits >= 8) {
							array_push($bytes[$tilenum], $curbyte);
							$curbyte = 0;
							$bits = 0;
						}
					}
				}
				while ($bits != 0) {
					$curbyte <<= 1;
					$bits++;
					if ($bits >= 8) {
						array_push($bytes[$tilenum], $curbyte);
						$curbyte = 0;
						$bits = 0;
					}
				}
			}
		}

		$binblob = "";
		foreach (array_unique($bytes, SORT_REGULAR) as $tilenum => $tiledata) {
			foreach ($tiledata as $tilebyte) {
				$binblob .= chr($tilebyte);
			}
		}
		$base64 = base64_encode($binblob);
		print "<a download='vwf.bin' href='data:application/octet-stream;base64,$base64'>download font data</a><br><br>";
		print("<pre>");
		foreach ($bytes as $tilenum => $tiledata) {
			printf("%02X : ", $tilenum);
			foreach($tiledata as $tilebyte) {
				printf("%02X ", $tilebyte);
			}
			print("\n");
		}
		print("</pre>");
		print("<br><br><br><pre>");
		foreach (array_unique($bytes, SORT_REGULAR) as $tilenum => $tiledata) {
			foreach ($tiledata as $tilebyte) {
				printf("%02X ", $tilebyte);
			}
		}
		print("</pre>");
		$bytecount = 0;
		print("<br><br><br><pre>");
		foreach (array_unique($bytes, SORT_REGULAR) as $tilenum => $tiledata) {
			printf("%04X\n", $bytecount);
			$bytecount += count($tiledata);
		}
		print("</pre>");
		print("<br><br><br><img src=\"data/vwf.png\" width=768 height=768 style=\"image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: optimize-contrast;\">");
		print("<br><br><br>\n<pre>");
		var_dump($bytes);
		print("</pre>");

	} catch (\Exception $e) {
		print "Err: ". $e->getMessage();
	}

	print pageFooter();
