
<?php

	require "includes.php";

	print pageHeader("bigtextbox viewer");

	#header("Content-type: text/plain");

	$itadaki	= getIS2();
	$rom		= $itadaki->rom;

	$o	= 0x7029a;

	if (isset($_GET['o'])) {
		$o	= hexdec($_GET['o']);
	}

	printf("offset: \$%06x\n\n<br><br>", $o);

	try {

		$bigText	= $itadaki->getBigText($o);
		$tb		= $bigText->getRawAsArray();
		$txt		= str_replace("\n", "<br>", $bigText->getAsString());

		printf("%s\n\n<br><br>", $txt);

		if ($tb) {
			foreach ($tb as $char) {
				if ($char === -1) {
					print "\n<br>";
				} else {
					print \IS2\Text\BigText::getImage($char);
				}
			}
		}


	} catch (\Exception $e) {
		print "Err: ". $e->getMessage();
	}


	print pageFooter();
