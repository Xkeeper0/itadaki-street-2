<?php

	require "includes.php";

	print pageHeader("recompression tool");

?>
compression tool. upload some data and get some compressed stuff back.
<br>
<br>reinserting it into the ROM is an exercise for you, dear reader.
<br>
<br>
<form method="post" enctype="multipart/form-data">
	file: <input type="file" name="file" value=""> <input type="submit" value="crush">
</form><br>

<?php

	if (isset($_FILES['file']) && !$_FILES['file']['error']) {

		print "<pre>";
		$size	= filesize($_FILES['file']['tmp_name']);
		$comp	= new \IS2\Compression\Compressor(file_get_contents($_FILES['file']['tmp_name']));
		ob_start();
		$data	= $comp->compress();
		$log	= ob_get_clean();
		$cSize	= strlen($data);

		printf("original size: %d bytes. compressed to %d bytes (%.1f%%)\n", $size, $cSize, $cSize / $size * 100);

		$base64	= base64_encode($data);
		print "\n\n<a download='compressed.bin' href='data:application/octet-stream;base64,$base64'>download it</a>";

		print "\n\n";
		print wordwrap(bin2hex($data), 16 * 4, "\n", true);
		print "\n\ncompressor output log\n---------------------\n\n$log";
		print "</pre>";

	}

	print pageFooter();
