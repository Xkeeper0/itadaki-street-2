<?php

	require "includes.php";

	use IS2\Street\Street;

	print pageHeader("board test page");

	// This page is kind of a huge mess, sorry.
	// I will hopefully fix it later (heh)

	$itadaki	= getIS2();

	// This will gray out any "0xdb" value
	// (which seems to be an "unused" value for IS2)
	function db($v) {
		if ($v == 0xdb) {
			return "<del>n/a</del>";
		} else {
			return $v;
		}
	}


	// @TODO Find these in the ROM
	// @TODO Also find the street config (starting values, etc.)
	$streetOffsets	= array(
						0x15B700,
						0x15B9B0,
						0x15BD16,
						0x15C1BD,
						0x15C4C9,
						0x15C9CA,
						0x15CEC0,
						0x15D32E,
						0x15D883,
						0x15DEDC,
						0x15E3BA,
						0x15E8CB,
						0x15EDFC,
						0x15F35E,
						0x15F976,
					);


	// Choose a street to view
	$streetNumber	= 0;
	if (isset($_GET['s']) && $_GET['s'] >= 1 && $_GET['s'] <= 15) {
		$streetNumber	= intval($_GET['s']) - 1;
	}

	// Print a list of streets to pick from
	print "view street: ";
	for ($i = 1; $i <= 15; $i++) {
		print " &middot; <a href='?s=$i'>$i</a>";
	}
	print " &middot; <a href='?s=all'>all data</a>";

	/*******************************************/
	/*******************************************/
	/*******************************************/
	// set to true to dump all the table data in one go
	if (isset($_GET['s']) && $_GET['s'] == "all") {

		print <<<E
<table class='street-data'>
	<tr>
		<th>id</th>
		<th>unk01</th>
		<th>unk02</th>
		<th>squareCount</th>
		<th>unk03</th>
		<th>maxRoll</th>
		<th>unk05</th>
		<th>startingMoney</th>
		<th>defaultTarget</th>
		<th>baseSalary</th>
		<th>promotionBonus</th>
	</tr>
E;

		$street			= array();

		foreach ($streetOffsets as $id => $offset) {
			$test		= $itadaki->getDecompressor($offset);
			$streetData	= $test->decompress();

			$street[$id]		= new Street($itadaki, $streetData);

			$tid		= $id + 1;

			print <<<E
	<tr>
		<th>street $tid</td>
		<td>{$street[$id]->unk01}</td>
		<td>{$street[$id]->unk02}</td>
		<td><a href="#street-{$tid}">{$street[$id]->squareCount}</a></td>
		<td>{$street[$id]->unk03}</td>
		<td>{$street[$id]->maxRoll}</td>
		<td>{$street[$id]->unk05}</td>
		<td>{$street[$id]->startingMoney}</td>
		<td>{$street[$id]->defaultTarget}</td>
		<td>{$street[$id]->baseSalary}</td>
		<td>{$street[$id]->promotionBonus}</td>
	</tr>
E;

		}
		print "</table>";


		foreach ($street as $id => $s) {
			$tid	= $id + 1;
			print <<<E
<h1><a name="street-{$tid}">street $tid</a></h1>
<table class="street-data data-table">
	<thead>
		<tr>
			<th>id</th>
			<th>type</th>
			<th>name</th>
			<th>value</th>
			<th>price</th>
			<th>district</th>
			<th>links</th>
			<th>unknowns</th>
		</tr>
	</thead>
	<tbody>
E;

			foreach ($s->squares as $sid => $square) {
				$uvo	= "";
				foreach ($square->unknowns as $b => $uv) {
					$uvo	.= db($uv) ." ";
				}

				$district	= db($square->district);
				print <<<E
		<tr>
			<td class="c">$sid</td>
			<td class="d-{$square->district} t-{$square->type}" style="position: static; height: 2em;"></td>
			<td class="nobr">{$square->fullName}</td>
			<td class="r">{$square->value}</td>
			<td class="r">{$square->price}</td>
			<td class="c d-{$square->district}">{$district}</td>
			<td class="r">{$square->linkCount}</td>
			<td class="r">{$uvo}</td>
		</tr>
E;
			}

		print <<<E
	</tbody>
</table>
<br>
<br>
E;
		}

		print pageFooter();
		exit();
	}
	/*******************************************/
	/*******************************************/
	/*******************************************/


	$test		= $itadaki->getDecompressor($streetOffsets[$streetNumber]);
	$streetData	= $test->decompress();

	$street		= new Street($itadaki, $streetData);
	$street->OH_GOD_DONT_FLOOD_THE_PAGE();

?>
<div class="street">
<?php

	// Some streets have more than one visible floor
	// Showing them all at once is a mess, so hide all but one at a time
	$floors	= 1;
	if ($streetNumber == 5) $floors = 2;
	if ($streetNumber == 12) $floors = 3;
	print '<input type="radio" name="floors" checked="checked" id="f-0-toggle" class="f-0-toggle"'. ($floors == 1 ? ' disabled="disable"' : "") .'><label for="f-0-toggle"> 1F</label>';
	for ($i = 1; $i < $floors; $i++) {
		print " <input type=\"radio\" name=\"floors\" class=\"f-{$i}-toggle\" id=\"f-{$i}-toggle\"><label for=\"f-{$i}-toggle\"> ". ($i + 1) ."F</label>";
	}

	// Display the list of squares
	foreach ($street->squares as $id => $square) {
		$px		= $square->position['x'] / 2 * 40;
		$py		= $square->position['y'] / 2 * 40;

		$prices	= ($square->type == 0 ? "<div class='prices'>{$square->value}<br>{$square->price}</div>" : "");
		$name	= implode("", $square->name);

		print <<<E
	<div class="square d-{$square->district} t-{$square->type} f-{$square->floor}" style="left: {$px}px; top: {$py}px;">
		<div class="id">{$id}</div>
		{$prices}
		<div class="data">
			{$square->types[$square->type]}: <span class="nobr">{$name}</span>
			<br>district #{$square->district}
			<br>floor {$square->floor}
			<br>links: {$square->linkCount}
			<br><ol>
E;


		foreach ($square->links as $id) {
			print "<li>". db($id);
			if (isset($square->paths[$id])) {
				print " (&#x2718; ". db($square->paths[$id][0]) .", ". db($square->paths[$id][1]) .")";
			}
			print "</li>";
		}
		print "</ol>";

		$t1		= "";
		$t2		= "";
		foreach ($square->unknowns as $ui => $uv) {
			$t1		.= sprintf("<th>%02x</th>", $ui);
			$t2		.= "<td>". db($uv) ."</td>";
		}

		print <<<E
			<table class='street-data'>
				<thead>
					$t1
				</thead>
				<tbody>
					$t2
				</tbody>
			</table>
		</div>
	</div>

E;
	}

?>
</div>
<br><input type="checkbox" class="showspam" id="showspam"><label for="showspam"> Show object dump</label>
<pre>
<?php

	print_r($street);

	print "</pre>";
	print pageFooter();
