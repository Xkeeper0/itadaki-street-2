
<?php

	require "includes.php";

	print pageHeader("vwf bigtext tester");

	#header("Content-type: text/plain");

	print <<<'HTML'
	<script src="smalltext-trans.js" type="text/Javascript"></script>
	<noscript><strong>Javascript is required for this page.<br /><br /></strong></noscript>

	<br /><h3>smalltext translator</h3><br />

	<textarea id="textboxinput" rows="8" cols="40" style="background: #000020; color: #ffffff;"></textarea>

	<textarea id="textboxoutput" rows="8" cols="40" style="background: #002000; color: #ffffff;" readonly></textarea>

	<br />

	<input type="checkbox" id="hexinput" name="hexinput" /><label for="hexinput">Hex digit input</label>

HTML;

	print pageFooter();
