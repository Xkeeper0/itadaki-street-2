
<?php

	require "includes.php";

	print pageHeader("textbox viewer");

	#header("Content-type: text/plain");

	$itadaki	= getIS2();

	#var_dump($itadaki->getStringAtOffset(0x72ba0 + 4));
	#var_dump($itadaki->getStringAtOffset(0x73554));

?>
<a href='search.php'>&lt;- back to search</a>
<br>
<br>
<table>
	<tr>
		<td style='width: 600px; vertical-align: top;'>
			<pre>
<?php

	$o	= 0x73545;
	$to	= null;

	if (isset($_GET['o'])) {
		$o	= hexdec($_GET['o']);
	}
	if (isset($_GET['to'])) {
		$to	= hexdec($_GET['to']);
	}

	try {

		$tb		= $itadaki->getTextbox($o, $to);
		print $tb;

	} catch (\Exception $e) {
		print "Err: ". $e->getMessage();
	}
?>
			</pre>
		</td>
		<td style=' vertical-align: top;'>
<?php

try {
	$tb->prettyPrint();

} catch (\Exception $e) {
	print "Err: ". $e->getMessage();
}


?>
		</td>
	</tr>
</table>
<?php

	print pageFooter();
