<?php

	/**
	 * Itadaki Street 2-specific stuff
	 */
	class ItadakiStreet2 extends Translator {

		public function getTextbox($offset) {

			return new ItadakiStreet2\Textbox($this, $offset);

		}


		/**
		 * Get a string of bigtext values
		 */
		public function getBigText($ofs) {
			$out		= array();
			while ($this->romI($ofs) !== 0) {
				$len	= $this->romI($ofs);
				$temp	= array();
				for ($i = 0; $i < $len; $i++) {
					$c	= $this->romI($ofs + 1 + ($i * 2), 2);
					if ($c % 0x12) throw new \Exception("Weird character in bigtext: $c");
					$temp[]	= $c / 0x12;
				}
				$out[]	= $temp;
				$ofs	+= 1 + $len * 2;
			}
			return $out;
		}

	}
