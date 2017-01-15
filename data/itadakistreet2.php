<?php

	/**
	 * Itadaki Street 2-specific stuff
	 */
	class ItadakiStreet2 extends Translator {

		public function getTextbox($offset, $textOffset = null) {

			$terminator		= strpos($this->_rom, "\0", $offset);
			$dataLen		= $terminator - $offset;
			$data			= substr($this->_rom, $offset, $dataLen + 1);

			return new ItadakiStreet2\Textbox($this, $offset, $data, $textOffset);
		}

	}
