<?php

	require "includes.php";

	if (!IS_CLI) {
		die("Sorry, this is CLI only.\n");
	}

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);

	// This file, when run, will start decompressing from $ofs.
	// It will then start again from the next point after,
	// decompressing as it goes.
	// It will stop if it his something that looks like junk
	// or something that decompresses improperly.

	// All of these offsets contain stuff, so you'll have to
	// run each one one-by-one.
	// Make sure to create a "decomp/" folder too.
	// (This tool isn't really prime-time ready, you see.)

	//$ofs		= 0x1248DF;
	//$ofs		= 0x08b800;
	//$ofs		= 0x061440;
	//$ofs		= 0x0FFA00;
	$ofs		= 0x167970;

	while (true) {

		$filename	= sprintf("decomp_%06X.bin", $ofs);

		$decomp	= $itadaki->getDecompressor($ofs);
		printf("Decompressing data from \$%06x ...\n", $ofs);
		printf("  Compressed data length:   %6d bytes + 4 byte header\n", $decomp->getCompressedSize());
		printf("  Decompressed data length: %6d bytes\n", $decomp->getDecompressedSize());
		if ($decomp->getCompressedSize() == 0xFFFF || $decomp->getDecompressedSize() == 0xFFFF || $decomp->getCompressedSize() >= $decomp->getDecompressedSize()) {
			die("...These sizes don't make any sense. Probably not compressed. Aborting.\n");
		}

		$data	= $decomp->decompress();
		file_put_contents("decomp/". $filename, $data);
		print "  Saved decompressed data as $filename\n";

		$ofs	+= $decomp->getCompressedSize() + 4;

	}
