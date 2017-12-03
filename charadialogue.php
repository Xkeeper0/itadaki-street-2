<?php

	require "includes.php";

	print pageHeader("dialogue viewer");

	#header("Content-type: text/plain");

	$itadaki	= getIS2();
	$rom		= $itadaki->rom();

	$chara = 0xA; // Konomi?

	$charanames = array(
		"Maharaja",
		"Shinji",
		"Kouhei",
		"Natsuhiko",
		"Hiroyuki",
		"Seiji",
		"Takemaru",
		"Heisuke",
		"Ayaka",
		"Mayu",
		"Konomi",
		"Kaoruko",
		"Kazumi",
		"Shelley",
		"Kyouko",
		"Yuuko",
	);

	try {

		if (isset($_GET['chara'])) {
			$chara = $_GET['chara'];
		}

		if (($chara < 0) || ($chara > 0xF)) {
			throw new \Exception('Character number out of range');
		}

		$tableptr	= 0x5A000 + ($chara * 4);
		$tableaddr	= 0x50000 + \Utils\Convert::toIntLE(substr($rom, $tableptr, 2));

//		printf("<!--\n\$tableptr = %06X\n\$tableaddr = %06X\n-->", $tableptr, $tableaddr);

		printf("<table class='data-table'>\n<thead>\n<tr>\n<th colspan=2>Entry</th>\n<th colspan=3>%s</th>\n</tr>\n", $charanames[$chara]);
		print "<tr>\n<th>ID</th>\n<th>Address</th>\n<th colspan=2>Text</th>\n<th>Mood value</th>\n</tr>\n</thead>\n";

		print "<tbody>\n";
	
		// Read each value from the table at $tableaddr
		for ($entry = 0; $entry < ($chara == 0 ? 15 : 152); $entry++) {
			$entryval	= \Utils\Convert::toIntLE(substr($rom, $tableaddr + ($entry * 3), 3));
			$bank		= ($entryval & 0x0F0000) >> 16;
			$btaddr		= ($entryval & 0x00FFFF) + (0x8000 * ($bank - 1));
			$animval	= ($entryval & 0xF00000) >> 20;
//			printf("<!--\n\$entryval = %06X\n\$bank = %02X\n\$btaddr = %06X\n\$animval = %02X\n-->", $entryval, $bank, $btaddr, $animval);
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

			printf("<td>%d</td>\n</tr>\n", $animval);
		}
		print "</tbody>\n</table>\n";

	} catch (\Exception $e) {
		print "Err: ". $e->getMessage();
	}


	print pageFooter();
