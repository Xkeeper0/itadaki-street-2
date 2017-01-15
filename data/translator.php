<?php

	class Translator {

		// Table file.
		protected $_table		= null;

		// ROM file.
		protected $_rom			= null;

		// Terminator value
		protected $_terminator	= null;

		// @TODO: Figure out how to handle multiple tables
		// Itadaki Street 2 has two tables (one smalltext, one bigtext)
		// Obviously one table won't really cut it

		public function __construct($romFile, $tableFile, $bigTableFile) {
			$this->_readROM($romFile);
			$this->_readTable($tableFile);
		}

		/**
		 * Get a string from the ROM, converted to a string
		 * @TODO Probably just do implode(getStringAtOffsetArray)
		 */
		public function getStringAtOffset($offset, $length = false) {

			$out	= "";
			$stop	= false;
			$pos	= 0;
			while (($length === false && !$stop) || ($length !== false && $length > 0)) {
				$bp		= $offset + $pos;
				$byte	= ord($this->_rom{$bp});

				if ($byte == $this->_terminator) {
					$stop	= true;
				} else {
					$out	.= (isset($this->_table[$byte])) ? $this->_table[$byte] : sprintf("[%02X]", $byte);
					if ($length !== false) $length--;
				}
				$pos++;
			}

			return $out;
		}

		/**
		 * Get an array of characters from a place in ROM
		 * Useful because of multi-byte table entries
		 */
		public function getStringAtOffsetArray($offset, $length = false) {

			$out	= array();
			$stop	= false;
			$pos	= 0;
			while (($length === false && !$stop) || ($length !== false && $length > 0)) {
				$bp		= $offset + $pos;
				$byte	= ord($this->_rom{$bp});

				if ($byte == $this->_terminator) {
					$stop	= true;
				} else {
					$out[]	= (isset($this->_table[$byte])) ? $this->_table[$byte] : sprintf("[%02X]", $byte);
					if ($length !== false) $length--;
				}
				$pos++;
			}

			return $out;
		}




		protected function _romI($o, $l = 1) {
			if ($l == 1) {
				return ord($this->_rom{$o});
			} else {
				return Utils::toIntLE(substr($this->_rom, $o, $l));
			}
		}

		protected function _romS($o, $l = 1) {
			return substr($this->_rom, $o, $l);
		}


		protected function _readROM($file) {
			$this->_rom		= file_get_contents($file);
		}

		protected function _readTable($file) {
			$table	= file_get_contents($file);
			$tableA	= explode("\n", $table);
			$tableOut	= array();

			foreach ($tableA as $str) {
				if ($x = trim($str)) {
					$row	= explode("=", $x, 2);
					$row[0]	= hexdec($row[0]);

					if ($row[0] == 0x20) $row[1] = " ";
					if ($row[0] == 0xFE) $row[1] = "\n";

					if ($row[1] == "[END]") $this->_terminator = $row[0];
					$tableOut[$row[0]]	= $row[1];
				}
			}
			$this->_table	= $tableOut;
		}

	}