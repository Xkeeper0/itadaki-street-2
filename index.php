<?php

	require "includes.php";

	print pageHeader("home");
?>

<h1>itadaki street 2 (いただきストリート２)</h1>
<p>hello and welcome! this is a project for reverse-engineering <em>itadaki street 2</em>.
</p>

<p>itadaki street 2 is an early game in a japanese-only series. it was released on the super nintendo.</p>

<p>western players may know this series from the release of <em>fortune street</em>, on the wii.
	<br><em>fortune street</em> is generally considered the worst game in the series.
</p>

<p>thankfully, this game isn't that, it's better! so we want to make it accessible for english players.
</p>

<p>there is an english <em>guide</em> available. the website is <a href="http://www.vizzed.com/boards/thread.php?id=80350">terrible</a>, though.
</p>


<h2>project</h2>
<p>the goal of this project is to reverse-engineer enough about this game to be able to make an english translation patch.
</p>

<p>right now, we are trying to find all the relevant data.
</p>

<p>much of the work is being done in a <a href="https://docs.google.com/spreadsheets/d/1m-tibEic1JpQ-BRFiOMlAnN089g6XKs0ad5JFc231Ew/edit?usp=sharing">google document</a>, with some reports being done in a <a href="http://jul.rustedlogic.net/thread.php?id=18198">forum thread</a>.
</p>


<h2>utilities</h2>
<p>here are a list of tools in this project so far:

 	<ul>
		<li><a href="search.php">textbox searcher</a> - searches for textbox calls and can display them close to how they would be in-game</li>
		<li><a href="board.php">street viewer</a> - displays the layout of each "street" as it is defined in the data</li>
		<li><a href="decompressor.php">decompressor</a> - given an offset, will attempt to decompress data from the rom</li>
		<li><a href="compressor.php">compressor</a> - given binary data, will attempt to compress it for re-insertion</li>
		<li><a href="palette.php">palette viewer</a> - visual represenation of palettes found in the data</li>
		<li><a href="bigtextchart.php">bigtext character viewer</a> - displays all of the "big text" graphics used for speech bubbles</li>
	</ul>
</p>

<p>there are also some other tools that are not web-accessible:
	<ul>
		<li>mass-decompress - will automatically extract continued compressed data to files</li>
	</ul>
</p>


<h2>contributing</h2>
<p>if you think you can help out with this, follow the link to the github repo over on the right.
	<br>you can also post in the <a href="http://jul.rustedlogic.net/thread.php?id=18198">forum thread</a> or message me on <a href="https://twitter.com/xkeepah">twitter</a> or whatever.
</p>

<?php
	print pageFooter();
