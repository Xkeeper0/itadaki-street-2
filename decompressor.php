<?php

	require "includes.php";

	print pageHeader("decompression tool");

	#header("Content-type: text/plain");

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);
	$rom		= file_get_contents("ita2.sfc");


	$ofs		= false;
	if ($_GET['ofs']) {
		$ofs	= hexdec($_GET['ofs']);
	}

?>
decompression tool. enter an offset to decompress it, maybe.
<br>there's no way to tell if it worked successfully.
<br>
<form method="get">
	offset: <input type="text" name="ofs" value="<?php printf("0x%06x", ($ofs ? $ofs : 0x15b700)); ?>"> <input type="submit" value="do it">
</form><br>

<?php

	if ($ofs) {

		$test	= $itadaki->getDecompressor($ofs);

		print "<pre>";
		$x		= $test->decompress();

		$base64	= base64_encode($x);
		$ofst	= sprintf("%06X", $ofs);
		print "<a download='ita2_$ofst.bin' href='data:application/octet-stream;base64,$base64'>download this</a>";

		print "\n\n";
		print wordwrap(bin2hex($x), 16 * 4, "\n", true);
		print "</pre>";

	}

	print pageFooter();
