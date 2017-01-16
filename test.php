<!doctype html>
<html>
<head>
	<title>itadaki street 2 textbox viewer</title>
	<meta charset="utf-8">
	<style type="text/css">
		body {
			font-family:	Verdana, Tahoma, sans-serif;
		}
		pre	{
			font-family:	Ubuntu Mono, Consolas, Monaco, Courier New, monospace;
		}
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
</head>
<body>
<?php

	require "includes.php";

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
	print "</pre>";
	print "</td><td style=' vertical-align: top;'>";


	$tb->prettyPrint();

	print "</td></tr></table>";

	// ?  X  Y  W  H  ?? cO cX cY
	// 02 01 0d 0c 0d 06 05 02 0f 10 52 b5 00 fe 20

?>
<br>
<br>
<br>
<a href="https://github.com/Xkeeper0/itadaki-street-2">view this project on github</a>
</body>
</html>
