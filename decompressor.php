<?php

	require "includes.php";

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);

	if (IS_CLI) {
		// If run as a command line too, do command line stuff

		if (!isset($argv[1])) {
			die("Missing: offset to start decompressing (like '0x??????')\n");
		}

		$ofs		= hexdec($argv[1]);
		$filename	= sprintf("decomp_%06X.bin", $ofs);

		if (isset($argv[2])) {
			$filename	= $argv[2];
		}

		$decomp	= $itadaki->getDecompressor($ofs);
		printf("Decompressing data from \$%06x ...\n", $ofs);
		printf("Compressed data length:   %6d bytes + 4 byte header\n", $decomp->getCompressedSize());
		printf("Decompressed data length: %6d bytes\n", $decomp->getDecompressedSize());

		$data	= $decomp->decompress();
		file_put_contents($filename, $data);
		print "Saved decompressed data as $filename\n";


	} else {

		// Viewed as webpage, do web stuff

		print pageHeader("decompression tool");

		$ofs		= false;
		if (isset($_GET['ofs'])) {
			$ofs	= hexdec($_GET['ofs']);
		}

?>
decompression tool. enter an offset to decompress the data there.
<br>
<br>
<form method="get">
	offset: <input type="text" name="ofs" value="<?php printf("0x%06x", ($ofs ? $ofs : 0x15b700)); ?>"> <input type="submit" value="do it">
</form><br>

<?php

		if ($ofs) {

			$decomp	= $itadaki->getDecompressor($ofs);

			print "<pre>";
			printf("Decompressing data from \$%06x ...\n", $ofs);
			printf("Compressed data length:   %6d bytes + 4 byte header\n", $decomp->getCompressedSize());
			printf("Decompressed data length: %6d bytes\n", $decomp->getDecompressedSize());

			ob_start();
			$data	= $decomp->decompress();
			$log	= ob_get_clean();

			$base64	= base64_encode($data);
			$ofst	= sprintf("%06X", $ofs);
			print "<a download='ita2_$ofst.bin' href='data:application/octet-stream;base64,$base64'>download this</a>";

			print "\n\n";
			print wordwrap(bin2hex($data), 16 * 4, "\n", true);
			print "\n\n";
			print "$log\n\n";
			print "</pre>";

		}

		print pageFooter();


	}
