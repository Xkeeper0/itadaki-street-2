
<?php

	require "includes.php";

	print pageHeader("bigtextbox viewer");

	#header("Content-type: text/plain");

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);

	#var_dump($itadaki->getStringAtOffset(0x72ba0 + 4));
	#var_dump($itadaki->getStringAtOffset(0x73554));

	$o	= 0x73545;

	if (isset($_GET['o'])) {
		$o	= hexdec($_GET['o']);
	}

	printf("offset: \$%06x\n\n<br>", $o);

	try {

		$tb		= $itadaki->getBigText($o);

		if ($tb) {
			foreach ($tb as $line) {
				foreach ($line as $char) {
					printf('<img src="bigtext.php?i=0x%03x" title="$%03x">', $char, $char);
				}
				print "\n<br>";
			}
		}


	} catch (\Exception $e) {
		print "Err: ". $e->getMessage();
	}


	print pageFooter();
