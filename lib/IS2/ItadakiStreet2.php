<?php

	namespace IS2;

	/**
	 * Itadaki Street 2-specific stuff
	 */
	class ItadakiStreet2 {

		/** @var string The ROM file */
		protected $_rom			= null;
		/** @var array Table of values to convert smalltext to Unicode characters */
		protected $_smallTextTable	= array();
		/** @var array Table of values to convert bigtext to Unicode characters */
		protected $_bigTextTable	= array();


		/**
		 * Loads a ROM file and (optionally) tables for use
		 * @param string $romFilename         Path to Itadaki Street 2 ROM
		 * @param string $smallTableFilename  Path to small table file
		 * @param string $bigTableFilename    Path to big table file
		 */
		public function __construct($romFilename, $smallTableFilename = null, $bigTableFilename = null) {
			if (!file_exists($romFilename)) {
				throw new \Exception("$romFilename file missing/not found");
			}
			$this->_rom		= file_get_contents($romFilename);
			if ($bigTableFilename)   $this->setBigTable($bigTableFilename);
			if ($smallTableFilename) $this->setSmallTable($smallTableFilename);
		}


		/**
		 * Sets the big table file and prepares it for use
		 * @param string $filename Filename to big table .tbl file
		 */
		public function setBigTable($filename) {
			$this->_bigTextTable	= new Text\Translator($filename);
		}

		/**
		* Sets the small table file and prepares it for use
		* @param string $filename Filename to small table .tbl file
		*/
		public function setSmallTable($filename) {
			$this->_smallTextTable	= new Text\Translator($filename);
		}

		/**
		 * Get the big text table object
		 * @return Text\Translator  Translator for big text
		 */
		public function getBigTable() {
			return $this->_bigTextTable;
		}

		/**
		 * Get the small text table object
		 * @return Text\Translator  Translator for small text
		 */
		public function getSmallTable() {
			return $this->_smallTextTable;
		}

		/**
		* Gets a textbox object from some offset in the ROM
		*
		* @param int offset of textbox
		* @return Textbox textbox generated from the given offset
		*/
		public function getTextbox($offset) {
			return new Textbox($this, $offset);
		}

		/**
		 * Gets an object representing bigtext from some ROM offset
		 * @param  int $ofs      Offset in ROM
		 * @return Text\BigText  BigText object
		 */
		public function getBigText($ofs) {
			// @TODO Error if no bigtext table
			return new Text\BigText($this->_rom, $this->_bigTextTable, $ofs);
		}

		/**
		 * Gets an object representing smalltext from some ROM offset
		 * @param  int $ofs        Offset in ROM
		 * @return Text\SmallText  SmallText object
		 */
		public function getSmallText($ofs) {
			// @TODO Error if no smalltext table
			return new Text\SmallText($this->_rom, $this->_smallTextTable, $ofs);
		}

		/**
		 * Get a decompressor with this ROM already in it
		 * @param  int $offset   offset of compressed data
		 * @return Decompressor  a decompressor object
		 */
		public function getDecompressor($offset) {
			return new Compression\Decompressor($this->_rom, $offset);
		}

		/**
		 * Get a reference to the ROM file
		 * @return string Loaded ROM file
		 */
		public function &rom() {
			return $this->_rom;
		}



	}
