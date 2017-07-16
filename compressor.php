<?php

	require "includes.php";

	/*

	// This code will take a decompressed blob of data,
	// and then...
	//             ... inflate it by about 12.5%. oops
	// Right now it is just "nop compression" in that
	// it doesn't compress, it just copies.
	// But! It works!

	$orig	= file_get_contents("/tmp/ita2_15B700.bin");

	$cp		= new Compressor($orig);
	$out	= $cp->compress();


	print wordwrap(bin2hex($out), 16 * 4, "\n", true);
	print "\n";

	$test	= new \ItadakiStreet2\Decompressor($out, 0);
	$tdata	= $test->decompress();

	var_dump(hash("sha256", $orig), hash("sha256", $tdata));

	print "\n";

	file_put_contents("/tmp/out.bin", $out);
	file_put_contents("/tmp/decomp.bin", $tdata);
	file_put_contents("/tmp/orig.bin", $orig);

	*/

	class Compressor {


		protected	$_input		= "";		// Input (plain)
		protected	$_inputPos	= 0;		// Current input pointer
		protected	$_len		= 0;		// Input length
		protected	$_output	= "";		// Output data (compressed)
		protected	$_compress	= array();	// Compressed data (pre-write)
		protected	$_bytePos	= array();	// Byte positions (for lookback test)
		protected	$_flag		= 0;		// Current compression flag
		protected	$_flagCount	= 0;		// Current flag counter

		public function __construct($data) {
			$this->_input	= $data;
			$this->_len		= strlen($data);
		}


		public function compress() {
			$this->_doCompression();
			$this->_finishData();

			return $this->_output;
		}


		protected function _finishData() {
			// First order of business: write the length of data
			$this->_writeByte($this->_len, true);

			// Write two otherwise unused bytes
			$this->_writeByte(0x6b58, true);

			// Figure out the # of flags we'll need
			$loops	= ceil(count($this->_compress) / 8) * 8;

			// Get the next 8 data chunks, write the flag, write the data
			// In the event we run out of data chunks, just fill the rest of bitflags with 0
			for ($i = 0; $i < $loops; $i++) {
				$flag		= 0;
				$data		= "";
				$baseChunk	= $i * 8;

				for ($c = 0; $c < 8; $c++) {
					// Get this chunk of data
					$testChunk	= $baseChunk + $c;
					$tData		= (isset($this->_compress[$testChunk])) ? $this->_compress[$testChunk] : "";
					$data		.= $tData;
					// Set a bit if compressed data (2 bytes)
					$flag		= ($flag << 1) + ((strlen($tData) === 2) ? 1 : 0);
				}

				$this->_output	.= chr($flag) . $data;
			}

		}

		public function _doCompression() {

			// Start the compression fun time
			while ($this->_inputPos < $this->_len) {

				if ($c = $this->_shouldCompress()) {
					// @TODO Compress
					// Advance read pointer as required

				} else {
					// Don't compress
					$this->_writePlainByte(ord($this->_input{$this->_inputPos}));
					$this->_inputPos++;
				}
			}
		}


		protected function _shouldCompress() {
			// @TODO: Do this, somehow
			return false;
		}


		// Add a single byte to the compressed data
		protected function _writePlainByte($byte) {
			$this->_compress[]	= chr($byte);
		}

		// Write some compressed data
		protected function _writeLookbackBytes($offset, $length) {

			// Offset obviously can't be greater than where we are,
			// and the length has to be between 3 and 18 (3 + 16) inclusive
			if ($offset > $this->_inputPos || ($length < 3 || $length > 18)) {
				throw new \Exception("Somehow length or offset are totally wrong.");
			}

			// Offset value is /behind/ current write pointer...
			$nOffset			= (($this->_inputPos - $offset) & 0xFFF) << 4;
			// ...and length is 3 + (4-bit value).
			$nLength			= (($length - 3) & 0x00F);

			// Combine into one 16-bit value
			$byte				= $nOffset + $nLength;
			$temp				= chr($byte & 0xFF) . chr(($byte & 0xFF00) >> 8);
			// Write to our output data
			$this->_compress[]	= $temp;

		}


		protected function _writeByte($value, $w16bit = false) {
			if ($w16bit) {
				// Write a 16-bit value
				$this->_output		.= chr($value & 0xFF) . chr(($value & 0xFF00) >> 8);

			} else {
				// Write just one 8-bit value
				$this->_output		.= chr($value);
			}
		}


	}
