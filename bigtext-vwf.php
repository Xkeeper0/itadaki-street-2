
<?php

	require "includes.php";

	print pageHeader("vwf bigtext tester");

	#header("Content-type: text/plain");

	print <<<'HTML'
	<script src="vwf-display.js" type="text/Javascript"></script>
	<noscript><strong>Javascript is required for this page.<br /><br /></strong></noscript>

	<br /><h3>vwf bigtext tester</h3><br />

	<img id="vwffont" src="data/vwf.png" style="display: none;" />

	<textarea id="textboxinput" rows="8" cols="40" style="background: #000020; color: #ffffff;"></textarea>

	<canvas id="textboxcanvas" width="128" height="48" style="border:1px solid #d3d3d3; padding: 4px; width: 384px; height: 144px; image-rendering: optimizeSpeed; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; image-rendering: optimize-contrast; -ms-interpolation-mode: nearest-neighbor;">Your browser does not support the HTML5 canvas tag.</canvas>


HTML;

	print pageFooter();
