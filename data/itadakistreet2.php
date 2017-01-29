<?php

	/**
	 * Itadaki Street 2-specific stuff
	 */
	class ItadakiStreet2 extends Translator {

		public function getTextbox($offset) {

			return new ItadakiStreet2\Textbox($this, $offset);

		}

	}
