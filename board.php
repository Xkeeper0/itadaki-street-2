<?php

	require "includes.php";

	print pageHeader("board test page");

	$itadaki	= new ItadakiStreet2("ita2.sfc", "is2.tbl", null);


?>



<style type="text/css">

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

		.t-1		{	background: url('squares/1.png');	}
		.t-2		{	background: url('squares/2.png');	}
		.t-3		{	background: url('squares/3.png');	}
		.t-4		{	background: url('squares/4.png');	}
		.t-5		{	background: url('squares/5.png');	}
		.t-6		{	background: url('squares/6.png');	}
		.t-7		{	background: url('squares/7.png');	}
		.t-9		{	background: url('squares/9.png');	}
		.t-16		{	background-image: url('squares/16.png');	}

		.t-2.d-0	{	background: url('squares/suit-0.png');	}
		.t-2.d-1	{	background: url('squares/suit-1.png');	}
		.t-2.d-2	{	background: url('squares/suit-2.png');	}
		.t-2.d-3	{	background: url('squares/suit-3.png');	}

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
			$this->_unk01			= $ds->getI(1);		// 00
			$this->_unk02			= $ds->getI(1);		// 01
			$this->_squareCount		= $ds->getI(1);		// 02
			$this->_unk03			= $ds->getI(1);		// 03
			$this->_maxRoll			= $ds->getI(1);		// 04
			$this->_unk05			= $ds->getI(1);		// 05
			$this->_startingMoney	= $ds->getI(2);		// 06-07
			$this->_defaultTarget	= $ds->getI(2);		// 08-09
			$this->_baseSalary		= $ds->getI(2);		// 0A-0B
			$this->_promotionBonus	= $ds->getI(2);		// 0C-0D

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
		protected	$_district	= 0;			// 219 for "null"
		protected	$_type		= 0;			// 0 for shop, ...

		public		$types		= array(
			0		=> "shop",
			1		=> "bank",
			2		=> "venture-suit",
			5		=> "holiday",
			);

		protected	$_unknowns	= array();		// 07-1F

		protected	$_name		= array();		// Stored in two halves

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

			for ($i = 0x09; $i <= 0x1f; $i++) {
				$this->_unknowns[$i]	= $ds->getI();
			}

			$nameA				= $ds->getS(8);
			$nameB				= $ds->getS(8);

			$translator			= $this->_street->getTranslator();
			$this->_name		= array(
				$translator->translateString($nameA),
				$translator->translateString($nameB),
				);

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
	if (isset($_GET['s']) && $_GET['s'] >= 0 && $_GET['s'] <= 14) {
		$streetNumber	= intval($_GET['s']);
	}

	print "view street: ";
	for ($i = 0; $i <= 14; $i++) {
		print " &middot; <a href='?s=$i'>$i</a>";
	}

	if ($streetNumber == 12 or $streetNumber == 5) {
		print "<br>this map uses floors and will probably look like shit";
	}

	print "<br>";

	//$test		= $itadaki->getDecompressor(0x15b700);
	$test		= $itadaki->getDecompressor($streetOffsets[$streetNumber]);
	$streetData	= $test->decompress();

	$street		= new Street($itadaki, $streetData);
	$street->OH_GOD_DONT_FLOOD_THE_PAGE();

?>


<div class="street">
<?php

	foreach ($street->squares as $id => $square) {
		$px		= $square->position['x'] / 2 * 40;
		$py		= $square->position['y'] / 2 * 40;

		$prices	= ($square->type == 0 ? "<div class='prices'>{$square->value}<br>{$square->price}</div>" : "");

		print <<<E
	<div class="square d-{$square->district} t-{$square->type}" style="left: {$px}px; top: {$py}px;">
		<div class="id">{$id}</div>
		{$prices}
		<!--{$square->type}, {$square->district}-->
		<div class="data">
E;
		print implode("", $square->name) ."<br>";
		$t1		= "";
		$t2		= "";
		foreach ($square->unknowns as $ui => $uv) {
			$t1		.= sprintf("<th>%02x</th>", $ui);
			$t2		.= sprintf("<td>%02x<br>%d</td>", $uv, $uv);
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
</div><pre>
<?php

	print_r($street);

	print "</pre>";
	print pageFooter();
