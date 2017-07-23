<?php

	namespace IS2\Text;

	/**
	 * Representation of some text within the ROM
	 */
	interface Text {

		/**
		 * Create a new object to represent text in the ROM
		 * @param string     $rom        ROM data
		 * @param Translator $translator Translator for given text data
		 * @param int        $offset     Offset of the text
		 */
		public function __construct(&$rom, Translator $translator, $offset);

		/**
		 * Gets the translated text as a string
		 * @return string	String of translated characters
		 */
		public function getAsString();

		/**
		 * Gets the translated string as an array
	 	 * @return array    Array of arrays (one per line of text)
		 */
		public function getAsArray();

		/**
		 * Gets the offset of the given text in the ROM
		 * @return int       The offset the text came from
		 */
		public function getOffset();

	}
