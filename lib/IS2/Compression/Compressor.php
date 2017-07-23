<?php

	namespace IS2\Compression;

	// @TODO: Better comments

	class Compressor {


		protected	$_input			= "";		// Input (plain)
		protected	$_readPointer	= 0;		// Current input pointer
		protected	$_len			= 0;		// Input length
		protected	$_output		= "";		// Output data (compressed)
		protected	$_compress		= array();	// Compressed data (pre-write)
		protected	$_bytePos		= array();	// Byte positions (for lookback test)


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

			// Write two placeholder bytes.
			// These will be the size of compressed data later,
			// but we obviously don't know that quite yet.
			$this->_writeByte(0x0000, true);

			// Figure out the # of flags we'll need
			$loops	= ceil(count($this->_compress) / 8);

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

			// Get the size of compressed data (minus the 4 header bytes)
			$size	= strlen($this->_output) - 4;
			// Write that size back into the data.
			$this->_output{2}	= chr(($size & 0x00FF));
			$this->_output{3}	= chr(($size & 0xFF00) >> 8);

		}

		public function _doCompression() {

			// Start the compression fun time
			while ($this->_readPointer < $this->_len) {
				printf("Byte %04X [\$%02X]\n", $this->_readPointer, ord($this->_input{$this->_readPointer}));
				if ($c = $this->_shouldCompress()) {
					printf("  Compress check says yes: offset %04X, length %02X\n", $c['offset'], $c['length']);
					$this->_writeLookbackBytes($c['offset'], $c['length']);
					$this->_advanceReadPointer($c['length']);

				} else {
					// Don't compress
					printf("  Not compressing\n");
					$this->_writePlainByte(ord($this->_input{$this->_readPointer}));
					$this->_advanceReadPointer(1);
				}
			}
		}


		// Determine if we should compress.
		// Look back at various things and see if we can get anywhere
		protected function _shouldCompress() {

			if ($this->_readPointer == 0) {
				// Can't compress the first byte!
				printf("  Can't compress first byte!\n");
				return false;
			}

			// Get the current byte
			$cByte	= ord($this->_input{$this->_readPointer});

			if (!isset($this->_bytePos[$cByte])) {
				// Can't compress a byte we haven't seen before!
				printf("  First time seeing \$%02X, can't compress!\n", $cByte);
				return false;
			}

			// Look through all of the instances of this byte
			// and see if we can get anything good out of it.
			// Should go in closest-first to last
			// @TODO Don't look at anything more than 4095 (0xFFF) characters back
			$bestLength	= 0;
			$bestOffset	= 0;
			foreach ($this->_bytePos[$cByte] as $offset) {
				$length	= $this->_testLookbackWrite($offset);
				if ($length > $bestLength) {
					printf(" New potential offset %04X, length %02X\n", $offset, $length);
					$bestLength	= $length;
					$bestOffset	= $offset;
					if ($length == 18) {
						// Hit gold, can't do any better than this
						break;
					}
				}
			}

			if ($bestLength >= 3) {
				// If we can get 3 or more, it's worth shrinking to 2 bytes
				return array("offset" => $bestOffset, "length" => $bestLength);
			}

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
			if ($offset > $this->_readPointer || ($length < 3 || $length > 18)) {
				throw new \Exception("Somehow length or offset are totally wrong.");
			}

			// Offset value is /behind/ current write pointer...
			$nOffset			= (($this->_readPointer - ($offset + 1)) & 0xFFF) << 4;
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


		// Determine if (and for how long) we can
		// use some lookback data
		protected function _testLookbackWrite($offset) {
			// $offset is the address into _input
			// $loopPoint is how many bytes we get out of it before "repeating"
			// (i.e., overrunning into what we've written)
			// Basically 'if we go past _readPointer, start over from $offset again'
			$loopPoint	= $this->_readPointer - $offset;
			$length		= 0;

			for ($i = 0; $i < 18; $i++) {

				// If we've gone past the end, immediately stop
				if (($this->_readPointer + $i) === $this->_len) {
					break;
				}

				// Compare the real data with the possible read data
				$real	= $this->_input{$this->_readPointer + $i};
				$cmp	= $this->_input{($offset + ($i % $loopPoint))};

				if ($real === $cmp) {
					$length++;
				} else {
					// Data doesn't match, can't go forward any more
					break;
				}
			}

			return $length;
		}


		// Advance the internal read pointer by some amount,
		// adding the read bytes to our tracker
		protected function _advanceReadPointer($num) {
			for ($i = 0; $i < $num; $i++) {
				$b		= ord($this->_input{$this->_readPointer});
				if (!isset($this->_bytePos[$b])) {
					$this->_bytePos[$b]	= array();
				}
				array_unshift($this->_bytePos[$b], $this->_readPointer);
				$this->_readPointer++;
			}
		}


	}
