<?php

	namespace ItadakiStreet2\Street;

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

		protected	$_unknowns	= array();		// 07-0b; these are always 0xdb except suit squares, where 07 is always 00

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
				throw new \Exception("Invalid property name $name");
			}
		}

	}
