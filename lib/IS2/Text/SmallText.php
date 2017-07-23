<?php

	namespace IS2\Text;
	use \Utils\DataSeeker;

	/**
	 * Representation of small within the IS2 ROM
	 *
	 * Small text in-ROM is basically just raw bytes,
	 * with a few special control codes
	 */
	class SmallText implements Text {

		/** @var Translator smalltext translator object */
		protected $_translator	= null;
		/** @var array Translated text, as a two-dimensional array ([line][chars]) */
		protected $_smallText		= null;
		/** @var array Original raw small text, as a one-dimensional array */
		protected $_rawSmallText	= null;
		/** @var int Offset into the ROM this text came from */
		protected $_offset		= null;

		/**
		 * Creates a SmallText object from the given data and offset
		 * @param string     $data        (ROM) data
		 * @param Translator $translator  Translation table
		 * @param int        $offset      Offset to smalltext
		 */
		public function __construct(&$data, Translator $translator, $offset) {
			$this->_offset			= $offset;
			$this->_translator		= $translator;

			$this->_rawSmallText	= $this->_getText($data, $offset);
			$this->_smallText		= $translator->translateArray($this->_rawSmallText);
		}


		/**
		 * Returns the smalltext as a string (with \n newlines)
		 * @return string String of translated bigtext
		 */
		 public function getAsString() {
 			return implode("", $this->_smallText);
 		}

 		/**
 		 * Returns the smalltext as an array of lines > characters
 		 * @return array Array of translated characters
 		 */
		public function getAsArray() {
			return $this->_smallText;
		}

		/**
		 * Gets the offset the text came from
		 * @return int  Offset the text came from
		 */
		public function getOffset() {
			return $this->_offset;
		}


		/**
		 * Get the text from the ROM
		 * @param  string     $rawData  Data
		 * @param  int        $offset   Offset into data to begin
		 * @return array                Array of bigText data
		 */
		protected function _getText(&$rawData, $offset) {
			// Create a DataSeeker for our ROM and seek to the offset given
			$data	= new DataSeeker($rawData);
			$data->seek($offset);

			// Prep output var and terminator
			$out		= array();
			$terminator	= $this->_translator->getTerminator();
			if ($terminator === null) throw new \Exception("Uhh. No terminator for small text???");

			// Get each character
			while (!$data->isEOF() && ($char = $data->getI()) !== $terminator) {
				$out[]	= $char;
			}
			return $out;
		}
	}
