<?php

	require "ita2.php";

	#header("Content-type: text/plain");

	$itadaki	= new Translator("ita2.sfc", "is2.tbl", null);

	#var_dump($itadaki->getStringAtOffset(0x72ba0 + 4));
	#var_dump($itadaki->getStringAtOffset(0x73554));

	print "<table><tr><td style='width: 600px;'>";

	$o	= 0x73545;

	if (isset($_GET['o'])) {
		$o	= hexdec($_GET['o']);
	}
	$tb		= $itadaki->getTextbox($o);


	print "<pre>";
	print $tb;
	print "\n";
	var_dump($tb);
	printf("\n\n%x", $tb->textOffset);
	print "</pre>";
	print "</td><td>";


	$tb->prettyPrint();

	print "</td></tr></table>";

	// ?  X  Y  W  H  ?? cO cX cY
	// 02 01 0d 0c 0d 06 05 02 0f 10 52 b5 00 fe 20
