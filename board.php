<?php

	require "includes.php";

	print pageHeader("board test page");

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);

	function db($v) {
		if ($v == 0xdb) {
			return "<del>n/a</del>";
		} else {
			return $v;
		}
	}


?>



<style type="text/css">

	del	{
		opacity:	0.5;
		font-style:	italic;
		}

	.street {
		position:	relative;
		width:		100%;
		height:		1280px;
		background:	#333;
		}

	.square	{
		width:		74px;
		height:		74px;
		position:	absolute;
		border:		2px solid black;
		box-sizing:	border-box;
		background:	#555;
		box-shadow:	0 0 5px 1px black;
		padding:	0.2em 0.3em;
		background-position:	center;
		background-repeat:		no-repeat;
		}

	.data {
		display:	none;
		position:	absolute;
		left:		70px;
		top:		30px;
		padding:	0.2em 0.5em;
		background:	rgba(20, 20, 50, .8);
		}

	.square:hover {
		z-index:	9999;
		}
	.square:hover .data {
		display:	block;
		}

	table {
		border-collapse:	collapse;
		}
	td, th {
		border:	1px solid #ccc;
		font-family:	Consolas, Courier New, monospace;
		padding:		0.1em 0.3em;
		}

	.id	{
		font-family:	Consolas, Courier New, monospace;
		font-size:		80%;
		font-style:		italic;
		text-shadow:	1px 1px black, -1px -1px black, -1px 1px black, 1px -1px black;
		/*
		padding:		0.1em 0.3em;
		background:		rgba(0, 0, 0, .5);
		*/
		}

	.prices	{
		position:		absolute;
		bottom:			0;
		left:			0;
		right:			0;
		width:			100%;
		text-align:		center;
		font-size:		80%;
		background:		rgba(0, 0, 0, .3);
		}

		/* The district colors wrap around after 8 */
		.d-0, .d-8	{	background-color:	#ad2952;	}
		.d-1, .d-9	{	background-color:	#b55229;	}
		.d-2, .d-10	{	background-color:	#948421;	}
		.d-3, .d-11	{	background-color:	#4a9439;	}
		.d-4, .d-12	{	background-color:	#29847b;	}
		.d-5, .d-13	{	background-color:	#296bad;	}
		.d-6, .d-14	{	background-color:	#6b42ad;	}
		.d-7, .d-15	{	background-color:	#8c397b;	}

		/* "null" districts */
		.d-219		{	background-color:	#000000;	}

		.t-1		{	background-image: url('squares/1.png');	}
		.t-2		{	background-image: url('squares/2.png');	}
		.t-3		{	background-image: url('squares/3.png');	}
		.t-4		{	background-image: url('squares/4.png');	}
		.t-5		{	background-image: url('squares/5.png');	}
		.t-6		{	background-image: url('squares/6.png');	}
		.t-7		{	background-image: url('squares/7.png');	}
		.t-9		{	background-image: url('squares/9.png');	}
		.t-16		{	background-image: url('squares/16.png');	}

		.t-2.d-0	{	background-image: url('squares/suit-0.png');	}
		.t-2.d-1	{	background-image: url('squares/suit-1.png');	}
		.t-2.d-2	{	background-image: url('squares/suit-2.png');	}
		.t-2.d-3	{	background-image: url('squares/suit-3.png');	}

		.f-0, .f-1, .f-2 { display: none; }
		.f-0-toggle:checked ~ .square.f-0 { display: block; }
		.f-1-toggle:checked ~ .square.f-1 { display: block; }
		.f-2-toggle:checked ~ .square.f-2 { display: block; }

		.showspam ~ pre			{	display:	none;		}
		.showspam:checked ~ pre {	display:	block;		}

		li			{	white-space:	nowrap;	}

		.dataTable > tbody > tr > td {
			background-size:		contain !important;
			background-repeat:		no-repeat;
			background-position:	center;
			}

		.r	{	text-align: right;	}
		.c	{	text-align: center;	}
		.dataTable td, .dataTable th {
			padding: 0.2em 0.5em;
		}
</style>

<?php



	class Street {

		// Street header: 14 bytes (0x0D)
		// Square size: 48 bytes (0x30)
		// Square name: last 16 (0x10) of square

		protected	$_rom				= null;
		protected	$_translator		= null;
		protected	$_data				= null;

		protected	$_squares			= array();	// Container for square obj.

		protected	$_unk01				= null;		// 00
		protected	$_unk02				= null;		// 01
		protected	$_squareCount		= null;		// 02
		protected	$_unk03				= null;		// 03
		protected	$_maxRoll			= null;		// 04
		protected	$_unk05				= null;		// 05
		protected	$_startingMoney		= null;		// 06-07
		protected	$_defaultTarget		= null;		// 08-09
		protected	$_baseSalary		= null;		// 0A-0B
		protected	$_promotionBonus	= null;		// 0C-0D



		public function __construct(&$translator, $data) {
			$this->_translator	= &$translator;
			$this->_rom			= &$translator->rom();
			$this->_data		= $data;

			$this->_parse();
			unset($this->_data);
		}


		protected function _parse() {

			$ds						= new \DataSeeker($this->_data);
			$this->_unk01			= $ds->getI(1);		// 00; unused? always 02
			$this->_unk02			= $ds->getI(1);		// 01; unused? always 00
			$this->_squareCount		= $ds->getI(1);		// 02; count of squares on this street
			$this->_unk03			= $ds->getI(1);		// 03; unused? always 00
			$this->_maxRoll			= $ds->getI(1);		// 04; max dice roll (5~8)
			$this->_unk05			= $ds->getI(1);		// 05; unused? always 00
			$this->_startingMoney	= $ds->getI(2);		// 06-07; player's starting money
			$this->_defaultTarget	= $ds->getI(2);		// 08-09; default target (can be changed)
			$this->_baseSalary		= $ds->getI(2);		// 0A-0B; base salary on promotion
			$this->_promotionBonus	= $ds->getI(2);		// 0C-0D; bonus given on promotion, multiplied by level

			for ($i = 0; $i < $this->_squareCount; $i++) {
				$sqdata				= $ds->getS(0x30);
				$this->_squares[$i]	= new Square($this, $i, $sqdata);
			}
		}


		/**
		* this function unsets refs to the big translator object
		* because nobody likes several megabytes of random junk in
		* their output
		*/
		public function OH_GOD_DONT_FLOOD_THE_PAGE() {
			unset($this->_translator);
			unset($this->_rom);
			$this->_translator		= null;
			$this->_rom				= null;
		}


		public function getTranslator() {
			return $this->_translator;
		}

		public function __get($name) {
			$tname	= "_". $name;
			if (property_exists($this, $tname)) {
				return $this->$tname;
			} else {
				throw new \Exception("Invalid property name $name, idiot");
			}
		}
	}

	class Square {
		protected	$_street	= null;			// Reference back to the original object
		protected	$_id		= null;			// Order this is in the data
		protected	$_data		= null;			// Binary blob of fun data

		protected	$_position	= array();		// 00 (X), 01 (Y)
		protected	$_floor		= 0;			// 02 -- floor that this is on?
		protected	$_value		= 0;			// 03-04
		protected	$_price		= 0;			// 05-06
		protected	$_district	= 0;			// 07; 0xdb for "none", used as suit in suit spaces
		protected	$_type		= 0;			// 0 for shop, ...

		protected	$_linkCount	= 0;			// 0e; # of links
		protected	$_links		= array();		// 0f-12; square ids

		protected	$_pathCount	= 0;			// 13; # of path exclusions
		protected	$_paths		= array();		// 14-1f; 3-byte list of "origin, forbid, forbid" to block movement

		protected	$_unknowns	= array();		// 07-0b

		protected	$_name		= array();		// Stored in two halves for auction/trading window
		protected	$_fullName	= "";			// Combined chunks of name

		public		$types		= array(
			0		=> "Shop",
			1		=> "Bank",
			2		=> "Venture/Suit",
			3		=> "Roll On",
			4		=> "Boon",
			5		=> "Holiday",
			6		=> "Warp",
			7		=> "Stockbroker",
			8		=> "(Unused/Blank)",
			9		=> "Casino",
			0x10	=> "Vacant Lot",
			);


		public function __construct($street, $id, $data) {
			$this->_street		= $street;
			$this->_id			= $id;
			$this->_data		= $data;
			$this->_parse();
			unset($this->_data);
		}

		protected function _parse() {
			$ds					= new \DataSeeker($this->_data);

			$this->_position	= array(
					'x'				=> $ds->getI(),
					'y'				=> $ds->getI(),
				);

			$this->_floor		= $ds->getI();

			$this->_value		= $ds->getI(2);
			$this->_price		= $ds->getI(2);
			$this->_type		= $ds->getI(1);
			$this->_district	= $ds->getI(1);

			for ($i = 0x09; $i <= 0x0d; $i++) {
				$this->_unknowns[$i]	= $ds->getI();
			}

			$this->_linkCount	= $ds->getI();
			for ($i = 0x0f; $i <= 0x12; $i++) {
				$this->_links[]	= $ds->getI();
			}

			$this->_pathCount	= $ds->getI();
			for ($i = 0x14; $i <= 0x1f; $i += 3) {
				$origin			= $ds->getI();
				$this->_paths[$origin]	= array($ds->getI(), $ds->getI());

				if ($origin == 0xdb && $this->_paths[$origin][0] == 0xdb && $this->_paths[$origin][1] == 0xdb) {
					unset($this->_paths[$origin]);
				}
			}


			$nameA				= $ds->getS(8);
			$nameB				= $ds->getS(8);

			$translator			= $this->_street->getTranslator();
			$this->_name		= array(
				$translator->translateString($nameA),
				$translator->translateString($nameB),
				);
			$this->_fullName	= trim($this->_name[0]) . trim($this->_name[1]);

		}

		public function __get($name) {
			$tname	= "_". $name;
			if (property_exists($this, $tname)) {
				return $this->$tname;
			} else {
				throw new \Exception("Invalid property name $name, idiot");
			}
		}

	}

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


	$streetNumber	= 13;
	if (isset($_GET['s']) && $_GET['s'] >= 1 && $_GET['s'] <= 15) {
		$streetNumber	= intval($_GET['s']) - 1;
	}

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
<table>
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
<table class="dataTable">
	<thead>
		<tr>
			<th>id</th>
			<th>type</th>
			<th>name</th>
			<th>value</th>
			<th>price</th>
			<th>district</th>
			<th>links</th>
		</tr>
	</thead>
	<tbody>
E;

			foreach ($s->squares as $sid => $square) {
				print <<<E
		<tr>
			<td class="c">$sid</td>
			<td class="d-{$square->district} t-{$square->type}" style="position: static; height: 2em;"></td>
			<td>{$square->fullName}</td>
			<td class="r">{$square->value}</td>
			<td class="r">{$square->price}</td>
			<td class="c d-{$square->district}">{$square->district}</td>
			<td class="r">{$square->linkCount}</td>
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


	//$test		= $itadaki->getDecompressor(0x15b700);
	$test		= $itadaki->getDecompressor($streetOffsets[$streetNumber]);
	$streetData	= $test->decompress();

	$street		= new Street($itadaki, $streetData);
	$street->OH_GOD_DONT_FLOOD_THE_PAGE();

?>
<div class="street">
<?php

	$floors	= 1;
	if ($streetNumber == 5) $floors = 2;
	if ($streetNumber == 12) $floors = 3;
	print '<input type="radio" name="floors" checked="checked" id="f-0-toggle" class="f-0-toggle"'. ($floors == 1 ? ' disabled="disable"' : "") .'><label for="f-0-toggle"> 1F</label>';
	for ($i = 1; $i < $floors; $i++) {
		print " <input type=\"radio\" name=\"floors\" class=\"f-{$i}-toggle\" id=\"f-{$i}-toggle\"><label for=\"f-{$i}-toggle\"> ". ($i + 1) ."F</label>";
	}

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
			{$square->types[$square->type]}: {$name}
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
			<table>
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
