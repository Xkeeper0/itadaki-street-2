<?php

	namespace IS2\Text;

	class Translator {

		/** @var string[] Array of byte value => character(s) */
		protected $_table		= array();


		/**
		 * Create a table object for translating byte strings
		 * @param  string $filename  Filename
		 */
		public function __construct($filename) {
			$this->_table		= $this->_readTable($filename);
		}


		/**
		 * Translate an array of character (values) using the table
		 * @param  string[]|int[] $characters An array of characters or ints
		 * @return string[]                   An array of translated characters
		 */
		public function translateArray($characters) {

			$out	= array();

			foreach ($characters as &$character) {
				if (is_array($character)) {
					// If an array of characters, recurse
					$character	= $this->translateArray($character);
				} else {
					// If the array is of characters, convert them to byte values first
					$value		= is_int($character) ? $character : ord($character);
					$character	= $this->_getCharacter($value);
				}
			}

			return $characters;
		}

		/**
		 * Translate a string of characters using the table
		 *
		 * This does NOT support values over FF (obviously),
		 * just raw bytes.
		 *
		 * Multi-byte translations will also be returned
		 * in the string with no way to tell them apart.
		 * You should probably just use translateArray()...
		 *
		 * @param  string $string String to translate
		 * @return string         Translated string
		 */
		public function translateString($string) {
			// Split the string into characters and (surprise)
			// use translateArray because duplication is bad.
			$characters	= str_split($string, 1);
			$characters	= $this->translateArray($characters);
			return implode("", $characters);
		}


		/**
		 * Gets the terminator for strings using this table
		 * @return int|null Terminator character value (if set)
		 */
		public function getTerminator() {
			return isset($this->_table['terminator']) ? $this->_table['terminator'] : null;
		}


		/**
		 * Get the character corresponding to a given index value
		 *
		 * If an index doesn't exist, [HH] will be returned
		 * (where HH is the hexadecimal value of a character)
		 * @param  int $index  Index value for character
		 * @return string      Character for that index
		 */
		protected function _getCharacter($index) {
			if ($index === -1) return "\n";
			return (isset($this->_table[$index])) ? $this->_table[$index] : sprintf("[%02X]", $index);
		}

		/**
		 * Read a table (.tbl) file into an array
		 * @param  string $filename  Filename
		 * @return string[]          Array of byte value => character(s)
		 */
		protected function _readTable($filename) {
			$table		= file_get_contents($filename);
			$tableA		= explode("\n", $table);
			$tableOut	= array();

			foreach ($tableA as $str) {
				// Every line in a table file is
				// "HH=C", where H is a hex value and C is its in-game representation
				if ($x = trim($str)) {
					$row	= explode("=", $x, 2);
					$row[0]	= hexdec($row[0]);

					// Handle some special table values that don't work well in table format
					if ($row[1] == "[space]") $row[1] = " ";
					if ($row[1] == "[\\n]") $row[1] = "\n";
					if ($row[1] == "[END]") $tableOut['terminator'] = $row[0];

					$tableOut[$row[0]]	= $row[1];
				}
			}
			return $tableOut;
		}

	}
