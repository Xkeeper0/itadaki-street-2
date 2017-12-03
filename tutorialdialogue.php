<?php

	require "includes.php";

	print pageHeader("tutorial dialogue viewer");

	#header("Content-type: text/plain");

	$itadaki	= getIS2();
	$rom		= $itadaki->rom();

	try {
		$tableaddr	= 0x1CE28;

//		printf("<!--\n\$tableptr = %06X\n\$tableaddr = %06X\n-->", $tableptr, $tableaddr);

		print "<table class='data-table'>\n<thead>\n<tr>\n<th>ID</th>\n<th>Address</th>\n<th colspan=2>Text</th>\n</tr>\n</thead>\n";

		print "<tbody>\n";

		// Read each value from the table at $tableaddr
		for ($entry = 0; $entry < 75; $entry++) {
			$entryval	= \Utils\Convert::toIntLE(substr($rom, $tableaddr + ($entry * 8), 2));
			$btaddr		= $entryval + 0x68000;
			printf("<tr>\n<td>%d</td>\n<td>0x%06X</td>\n", $entry, $btaddr);

		// Read bigtext at that address
		// Display it on page
			try {
				$bigText	= $itadaki->getBigText($btaddr);
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
