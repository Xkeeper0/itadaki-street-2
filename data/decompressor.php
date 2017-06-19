<?php

	namespace ItadakiStreet2;

	class Decompressor {

		protected $_rom				= null;		// The ROM file, as a reference
		protected $_output			= "";		// The decompressed data

		// Data start and output aren't needed
		// because they are assumed to both be zero
		protected $_romOffset		= 0;		// Where in the ROM it starts
		protected $_readPointer		= 0;		// Where we are in the data
		protected $_writePointer	= 0;		// Where we are in the output
		protected $_writeLength		= 0;		// How much we should write
		protected $_rotatyThing		= 0;		// Unknown state spinner
		protected $_f2f3			= 0;		// 7E00F2-F3

		/**
		* Constructor, called from ItadakiStreet2->getDecompressor
		* ROM should be passed automatically by that class
		*/
		public function __construct(&$rom, $startOffset) {
			$this->_rom			= &$rom;
			$this->_romOffset	= $startOffset;
		}

		/**
		* Decomp_ProbablyStartHere
		*/
		public function decompress() {
			// All of the various data is already zero

			// Get the amount we're expected to read as a 16-bit value
			// The original game does two reads/two writes, but we can just do one 16-bit
			$this->_writeLength		= $this->_readNextByte(true);
			$this->_log(sprintf("Expected size: %04x bytes", $this->_writeLength));

			// Burn another pointless read
			$this->_readNextByte();

			// Burn one more byte??
			$this->_readNextByte();

			// Get the next rotary thingy
			//$this->_getNextRotatyThing();

			while ($this->_writePointer < $this->_writeLength) {
				//$this->_log(sprintf("Write pointer: %04x", $this->_writePointer));
				$this->_log(sprintf("WP %04X - RT %04x", $this->_writePointer, $this->_rotatyThing));

				// If RotaryThing least significant byte is 00, get a new one
				if (($this->_rotatyThing & 0xFF) == 0x00) {
					//$this->_log("Getting new rotaty thing");
					$this->_getNextRotatyThing();
					//$this->_log(sprintf("  New rotaty thing value: %04x", $this->_rotatyThing));

				} elseif ($this->_shiftRotatyThing()) {
					// BCS - do something if we shift out a set bit
					$this->_log("  Doing F2F3 thing");
					$this->_doF2F3thing();

				} else {
					// Carry wasn't set, just copy data?
					// @TODO might need to make a "write to pos" thing
					// in the event this ends up being able to write arbitrary shit
					$byte		= $this->_readNextByte();
					$this->_log(sprintf("  Copying single byte: %02x", $byte));
					$this->_output			.= chr($byte);
					$this->_writePointer++;
				}

				// wow is that really it? uhhh that f2f3 thing must
				// really be complicated then huh
			}

			return $this->_output;

		}


		/**
		* DoSomethingWithF2F3
		*/
		protected function _doF2F3thing() {

			// Two byte value put into F2/F3
			$this->_f2f3	= $this->_readNextByte(true);
			$temp			= $this->_f2f3 >> 4;
			$f4				= $temp;

			$this->_log(sprintf("    F2F3 value: %04x", $this->_f2f3));
			$this->_log(sprintf("    F4 value  : %02x (%d)", $f4, $f4));
			$this->_log(sprintf("    Write ptr : %04x (%d)", $this->_writePointer, $this->_writePointer));

			$temp			= $this->_writePointer;
			$temp--;		// Reduce by one
			// Subtract temp from F4 value
			$f4				= $temp - $f4;

			$this->_log(sprintf("    F4 updated value: %04x (%d)", $f4, $f4));


			$temp			= ($this->_f2f3 & 0x0F) + 3;

			$this->_log(sprintf("  Repeat count: %04x (%d)", $temp, $temp));

			for ($i = 0; $i < $temp; $i++) {
				$byte			= ord($this->_output{$f4});
				$this->_log(sprintf("      Copying byte %02x", $byte));
				$this->_output	.= chr($byte);
				$this->_writePointer++;
				$f4++;
			}
		}


		/**
		* GetNextRotatyThing
		* Loads one byte into A, swaps A/B, loads FF into A
		*/
		protected function _getNextRotatyThing() {
			$tmp	= $this->_readNextByte();
			$this->_rotatyThing	= ($tmp << 8) + 0xFF;
		}


		/**
		* ShiftRotatyThing
		* This is not an actual subroutine but just a useful helper
		* @return	if "carry" would be set
		*/
		protected function _shiftRotatyThing() {
			// Shift left once
			$tmp				= ($this->_rotatyThing << 1);
			// Shift right 16 times to get the carry value
			$ret				= ($tmp & 0x10000) >> 16;

			//$this->_log(sprintf("  Before %04x - New %04x - Cflag %x", $this->_rotatyThing, $tmp, $ret));
			// Update rotatything
			$this->_rotatyThing	= $tmp & 0xFFFF;
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
