<style type="text/css">
	.menugrid {
		background: #000;
		border-spacing: 1px;
		color: white;
	}
	.menugrid td {
		background: #222;
		width: 1.5em;
		height: 1.5em;
		text-align: center;
	}
	.menugrid td.c {
		background: #224;
	}
</style>

<?php

	require "ita2.php";

	#header("Content-type: text/plain");

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);

	#var_dump($itadaki->getStringAtOffset(0x72ba0 + 4));
	#var_dump($itadaki->getStringAtOffset(0x73554));

	print "<table><tr><td style='width: 600px; vertical-align: top;'>";

	$o	= 0x73545;
	$to	= null;

	if (isset($_GET['o'])) {
		$o	= hexdec($_GET['o']);
	}
	if (isset($_GET['to'])) {
		$to	= hexdec($_GET['to']);
	}


	print "<pre>";
	$tb		= $itadaki->getTextbox($o, $to);
	print "\n";
	print $tb;
	print "\n";
	#var_dump($tb);
	printf("\n\n%x", $tb->textOffset);
	print "</pre>";
	print "</td><td style=' vertical-align: top;'>";


	$tb->prettyPrint();

	print "</td></tr></table>";

	// ?  X  Y  W  H  ?? cO cX cY
	// 02 01 0d 0c 0d 06 05 02 0f 10 52 b5 00 fe 20
