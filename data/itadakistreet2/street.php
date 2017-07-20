<?php


	namespace ItadakiStreet2;


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
				$this->_squares[$i]	= new Street\Square($this, $i, $sqdata);
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
