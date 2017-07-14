<?php

	namespace ItadakiStreet2;

	class Decompressor {

		protected $_rom					= null;		// The ROM file, as a reference
		protected $_output				= "";		// The decompressed data

		// Data start and output aren't needed
		// because they are assumed to both be zero
		protected $_romOffset			= 0;		// Where in the ROM it starts
		protected $_readPointer			= 0;		// Where we are in the data
		protected $_writePointer		= 0;		// Where we are in the output
		protected $_writeLength			= 0;		// How much we should write
		protected $_compressionFlag		= 0;		// Compression bitflag

		/**
		* Constructor, called from ItadakiStreet2->getDecompressor
		* ROM should be passed automatically by that class
		*/
		public function __construct(&$rom, $startOffset) {
			$this->_rom			= &$rom;
			$this->_romOffset	= $startOffset;
		}

		/**
		* decompress
		* Decompress some data based on arguments from the constructor.
		*/
		public function decompress() {

			// Get the amount we're expected to read as a 16-bit value.
			$this->_writeLength		= $this->_readNextByte(true);
			$this->_log(sprintf("Expected size: %04x bytes", $this->_writeLength));

			// Read two unused (!) bytes.
			// The code itself reads and immediately discards these best I can tell
			$this->_readNextByte();
			$this->_readNextByte();

			// Continue decompressing data until we fill the requested amount
			while ($this->_writePointer < $this->_writeLength) {

				if (($this->_compressionFlag & 0xFF) == 0x00) {
					// If the low byte of _compressionFlag is 00, get a new one
					$this->_getNextCompressionFlag();

				} elseif ($this->_shiftCompressionFlag()) {
					// If the high bit was set,
					// the next two bytes are a lookback compression command
					$this->_log("  Decompress bit set, doing lookback decompression");
					$this->_doLookbackDecompression();

				} else {
					// If the high bit wasn't set, just copy a byte from input to output
					$byte		= $this->_readNextByte();
					$this->_log(sprintf("  Copying single byte: %02x", $byte));
					$this->_output			.= chr($byte);
					$this->_writePointer++;
				}

			}

			return $this->_output;

		}


		/**
		* _doLookbackDecompression
		* Implements the lookback decompression format used by the game
		* Note that this isn't complete lookback, as you can reference "future"
		* data (in a limited way), and you're limited to about 3 to 19 bytes
		* (as "compressing" in this way is useless at 2 or under)
		*
		* The short version is that the compression is an instruction to
		* "rewind" the current output by X bytes, and start copying data there
		* to the end of the output, moving forwards each time
		*
		* Assume a lookback of 3 and a length of 5:
		*                          v current write pointer
		* Output:   01 02 03 04 05 ..
		*                 ^ rewind here
		*
		* Output:   01 02 03 04 05 03 04 05 03 04 ...
		*                 ^--------^
		*                    ^--------^
		*                       ^--------^
		*                          ^--------^
		*                             ^--------^
		* etc...
		*/
		protected function _doLookbackDecompression() {

			// The game loads two bytes into a 16-bit value...
			$decompSetting	= $this->_readNextByte(true);

			// Then uses the top 12 bits as the offset to look back into the written data, plus 1
			// and the bottom 4 bits as the length of data to read and write, plus 3
			$readOfs	= $decompSetting >> 4;
			$readPtr	= $this->_writePointer - ($readOfs + 1);
			$readLen	= ($decompSetting & 0x0F) + 3;

			$this->_log(sprintf("    Original value: %04x", $decompSetting));
			$this->_log(sprintf("    Lookback: %04x bytes, readPtr %04x", $readOfs, $readPtr));
			$this->_log(sprintf("    Length:   %02x (%2d)", $readLen, $readLen));

			for ($i = 0; $i < $readLen; $i++) {
				// Read a byte from the output string at position readPtr
				// and output it at the end of the decompressed output
				$byte			= ord($this->_output{$readPtr});
				$this->_log(sprintf("      Copying byte %02x", $byte));
				$this->_output	.= chr($byte);
				$this->_writePointer++;
				$readPtr++;
			}
		}


		/**
		* GetNextCompressionFlag
		* Transfers next byte to high byte of 16-bit value, then adds FF
		* The bitflag is used to determine if the next bytes are compression data
		* A high bit shifted out = compressed, otherwise plain copy
		*/
		protected function _getNextCompressionFlag() {
			$this->_compressionFlag	= ($this->_readNextByte() << 8) + 0xFF;
		}


		/**
		* ShiftCompressionFlag
		* This is not an actual subroutine but just a useful helper
		* @return	top bit shifted out of the 16-bit value
		*/
		protected function _shiftCompressionFlag() {
			// Shift left once
			$tmp				= ($this->_compressionFlag << 1);
			// Shift right 16 times to get the carry value
			$ret				= ($tmp & 0x10000) >> 16;

			// Update CompressionFlag
			$this->_compressionFlag	= $tmp & 0xFFFF;

			// Return shifted-out bit
			return $ret;
		}

		/**
		* Decomp_ReadNextByte
		* No need to worry about rolling the banks here
		* @r16bit	return 16 bit value instead of 8
		*/
		protected function _readNextByte($r16bit = false) {
			$bytes				= $r16bit ? 2 : 1;
			$ret				= \Utils::toIntLE(substr($this->_rom, $this->_romOffset + $this->_readPointer, $bytes));
			$this->_readPointer	+= $bytes;

			return $ret;
		}


		/**
		* Outputs lots of garbage if you uncomment it
		*/
		protected function _log($m) {
			//print $m ."\n";
		}

	}
