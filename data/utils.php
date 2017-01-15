<?php

	class Utils {
		public static function toIntLE($s) {
			$out	= 0;
			$sl		= strlen($s);
			for ($i = 0; $i < $sl; $i++) {
				$out	+= ord($s{$i}) << (8 * $i);
			}
			return $out;
		}

		public static function printableHex($s) {
			$len	= strlen($s);
			$out	= "";
			for ($i = 0; $i < $len; $i++) {
				$out .= ($i ? " " : "") . sprintf("%02x", ord($s{$i}));
			}
			return $out;
		}
	}


	class DataSeeker {
		protected	$_pointer	= 0;
		protected	$_length	= 0;
		protected	$_data		= "";

		public function __construct($data) {
			$this->_data	= $data;
			$this->_length	= strlen($data);
		}

		/**
		 * Get an integer from the data
		 */
		public function getI($len = 1) {
			return Utils::toIntLE($this->_fetch($len));
		}

		/**
		 * Get a string from the data
		 */
		public function getS($len = 1) {
			return $this->_fetch($len);
		}

		/**
		 * Move the pointer somewhere
		 */
		public function seek($ptr = 0) {
			if ($ptr >= $this->_length) {
				throw new Exception("Tried to put pointer past EOF");
			}
			$this->_pointer	= $ptr;
		}

		/**
		 * Internal function to fetch data from the internal string
		 */
		protected function _fetch($len = 1) {
			if ($this->_pointer + $len > $this->_length) {
				throw new Exception("Not enough data to fetch; wanted ". $len .", only have ". ($this->_length - $this->_pointer));
			}

			$ret			= substr($this->_data, $this->_pointer, $len);
			$this->_pointer	+= $len;
			return $ret;
		}

	}
