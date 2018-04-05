<?php

	require "includes.php";

	print pageHeader("bigtext viewer");

	#header("Content-type: text/plain");

	$itadaki	= getIS2();
	$rom		= $itadaki->rom();

	$btaddresses = array(
		0x070000,
		0x070039,
		0x070056,
		0x07006F,
		0x070097,
		0x0700C8,
		0x0700F8,
		0x070139,
		0x070152,
		0x07016A,
		0x070186,
		0x070192,
		0x0701BA,
		0x0701CA,
		0x0701D4,
		0x0701DE,
		// 0x0701ED, // This is the tournament rounds characters; skip it here
		0x0701F9,
		0x070210,
		0x070229,
		0x07024F,
		0x070273,
		0x07029A,
		0x0702BB,
		0x0702E1,
		0x070300,
		0x07031F,
		0x070362,
		0x0703A7,
		0x0703C9,
		0x0703F5,
		0x070423,
		0x070451,
		0x070475,
		0x070494,
		0x0704AC,
		0x0704CA,
		0x0704F2,
		0x070509,
		0x07053B,
		0x07055B,
		0x070576,
		0x07059B,
		0x0705B2,
		0x0705D3,
		0x0705F1,
		0x070629,
		0x07064D,
		0x07066C,
		0x07068B,
		0x0706B5,
		0x0706DF,
		0x070711,
		0x070724,
		0x070747,
		0x070766,
		0x070783,
		0x070798,
	);

	try {

		print "<table class='data-table'>\n<thead>\n<tr>\n<th>Address</th>\n<th colspan=2>Text</th>\n</tr>\n";

		print "<tbody>\n";
	
		// Read each value from the table at $tableaddr
		foreach ($btaddresses as $address) {

			printf("<tr>\n<td>0x%06X</td>\n", $address);
	
		// Read bigtext at that address
		// Display it on page
			try {
				$bigText	= $itadaki->getBigText($address);
				$tb		= $bigText->getRawAsArray();
				$txt		= str_replace("\n", "<br>", $bigText->getAsString());
				printf("<td>%s</td>\n", $txt);

				print "<td>";
				if ($tb) {
					foreach ($tb as $char) {
						if ($char === -1) {
							print "<br>";
						} else {
							print \IS2\Text\BigText::getImage($char);
						}
					}
				}
				print "</td>\n";
			}
			catch (\Exception $e) {
				print "<td colspan=2>Err: ".$e->getMessage()."</td>";
			}

		}
		print "</tbody>\n</table>\n";

	} catch (\Exception $e) {
		print "Err: ". $e->getMessage();
	}


	print pageFooter();
