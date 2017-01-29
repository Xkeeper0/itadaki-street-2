<!doctype html>
<html>
<head>
	<table>nice font, dude</table>
	<style>
		body {
			background: #113;
			font-family:	Ubuntu Mono, Consolas, Courier New;
			font-size:		14pt;
			color:			#e0e0e0;
		}
		img	{

			image-rendering: -moz-crisp-edges; 
			image-rendering: -o-crisp-edges;
			image-rendering: -webkit-optimize-contrast;
			image-rendering: crisp-edges;
			image-rendering: pixelated;
			-ms-interpolation-mode:nearest-neighbor;
			width:				36px;
			height:				36px;
			padding:			3px;
		}
	</style>
</head>
<body>
<table>
	<thead>
		<tr>
			<th>Idx</th>
			<td colspan='16'></td>
		</tr>
	</thead>
	<tbody>

<?php

	for ($i = 0; $i < 0x2D0; $i++) {
		if (!($i % 0x10)) {
			printf("\t\t<tr><th>%03X</th>\n", $i);
		}

		printf("\t\t\t<td><img src='bigtext.php?i=%d' title='%03X'></td>\n", $i, $i);

		if (($i % 0x10) == 0x0F) {
			printf("\t\t</tr>\n", $i);
		}

	}

?>
	</tbody>
</table>
</body>
</html>
