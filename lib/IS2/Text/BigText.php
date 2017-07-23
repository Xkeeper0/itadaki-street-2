<?php

	namespace IS2\Text;
	use \Utils\DataSeeker;

	/**
	 * Representation of big text within the IS2 ROM
	 *
	 * Big text in-ROM is stored as a struct like this:
	 * LL BBBB BBBB BBBB ...
	 * LL BBBB BBBB BBBB ...
	 * 00
	 *
	 * LL = one-byte length, BBBB = two-byte LE of character position
	 * in the 1bpp bigtext font data
	 */
	class BigText implements Text {

		/** @var Translator bigtext translator object */
		protected $_translator	= null;
		/** @var array Translated text, as an array of characters */
		protected $_bigText		= null;
		/** @var array Original raw big text, as an array of indexes */
		protected $_rawBigText	= null;
		/** @var int Offset into the ROM this text came from */
		protected $_offset		= null;

		/**
		 * Creates a BigText object from the given data and offset
		 * @param string     $rom         (ROM) data
		 * @param Translator $translator  Translation table
		 * @param int        $offset      Offset into bigtext
		 */
		public function __construct(&$data, Translator $translator, $offset = 0) {
			$this->_offset		= $offset;
			$this->_translator	= $translator;

			$this->_rawBigText	= $this->_getText($data, $offset);
			$this->_bigText		= $translator->translateArray($this->_rawBigText);
		}


		/**
		 * Returns the bigtext as a string (with \n newlines)
		 * @return string String of translated bigtext
		 */
		 public function getAsString() {
 			return implode("", $this->_bigText);
 		}

		/**
 		 * Returns the bigtext as an array of lines > characters
 		 * @return array Array of arrays of translated characters
 		 */
		public function getAsArray() {
			return $this->_bigText;
		}

		/**
 		 * Returns the raw bigtext indexes
 		 * @return array Raw bigtext as an array of indexes
 		 */
		public function getRawAsArray() {
			return $this->_rawBigText;
		}

		/**
		 * Gets the offset the text came from
		 * @return int  Offset the text came from
		 */
		public function getOffset() {
			return $this->_offset;
		}

		/**
		 * Gets an HTML image for this particular character
		 * @param  int $index  Character index
		 * @return string      HTML <img> tag
		 */
		public static function getImage($index) {
			return sprintf('<img src="bigtext.php?i=0x%03x" title="$%03x">', $index, $index);
		}

		/**
		 * Get the text from the data
		 * @param  string     $rawData Raw data
		 * @param  int        $offset  Offset into data to begin
		 * @return array               Array of bigText data
		 */
		protected function _getText(&$rawData, $offset) {
			// Create a DataSeeker for our ROM and seek to the offset given
			$data	= new DataSeeker($rawData);
			$data->seek($offset);
			$out		= array();

			// First byte of each sequence: number of characters
			while (($length = $data->getI()) !== 0) {
				// Add a newline if we have text already.
				// This is a special "gross hack" for translating new lines
				if (!empty($out)) $out[]	= -1;
				// Get characters and store the indexes
				for ($i = 0; $i < $length; $i++) {
					$c	= $data->getI(2);
					if ($c % 0x12) throw new \Exception("Weird character in bigtext: $c");
					$out[]	= $c / 0x12;
				}
			}
			return $out;
		}
	}
