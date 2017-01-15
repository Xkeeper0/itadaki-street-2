<?php

	class Translator {
		protected $_table		= null;
		protected $_rom			= null;

		protected $_terminator	= null;

		public function __construct($romFile, $tableFile, $bigTableFile) {
			$this->_readROM($romFile);
			$this->_readTable($tableFile);
		}


		public function getStringAtOffset($offset, $length = false) {

			$out	= "";
			$stop	= false;
			$pos	= 0;
			while (($length === false && !$stop) || ($length !== false && $length > 0)) {
				$bp		= $offset + $pos;
				$byte	= ord($this->_rom{$bp});

				if ($byte == $this->_terminator) {
					$stop	= true;
				} else {
					$out	.= (isset($this->_table[$byte])) ? $this->_table[$byte] : sprintf("[%02X]", $byte);
					if ($length !== false) $length--;
				}
				$pos++;
			}

			return $out;
		}


		public function getStringAtOffsetArray($offset, $length = false) {

			$out	= array();
			$stop	= false;
			$pos	= 0;
			while (($length === false && !$stop) || ($length !== false && $length > 0)) {
				$bp		= $offset + $pos;
				$byte	= ord($this->_rom{$bp});

				if ($byte == $this->_terminator) {
					$stop	= true;
				} else {
					$out[]	= (isset($this->_table[$byte])) ? $this->_table[$byte] : sprintf("[%02X]", $byte);
					if ($length !== false) $length--;
				}
				$pos++;
			}

			return $out;
		}


		public function getTextbox($offset, $textOffset = null) {

			$terminator		= strpos($this->_rom, "\0", $offset);
			$dataLen		= $terminator - $offset;
			#printf("Len = %x (%d)\n", $dataLen, $dataLen);
			$data			= substr($this->_rom, $offset, $dataLen + 1);
			#printf("data: %s\n", Utils::printableHex($data));

			$u1				= $this->_romI($offset +  0);
			$screenX		= $this->_romI($offset +  1);
			$screenY		= $this->_romI($offset +  2);
			$windowW		= $this->_romI($offset +  3);
			$windowH		= $this->_romI($offset +  4);
			$u6				= $this->_romI($offset +  5);
			$cursorOptions	= $this->_romI($offset +  6);
			$cursorX		= $this->_romI($offset +  7);
			$cursorY		= $this->_romI($offset +  8);
			$u10			= $this->_romI($offset +  9);
			$textPointer	= $this->_romI($offset + 10, 2);

			if ($textOffset) {
				$text		= $this->getStringAtOffsetArray($textOffset);
			} else {
				// Try to get the text offset from the text pointer
				// Chances of this working: slim
				$textOffset	= floor($offset / 0x8000) * 0x8000 + ($textPointer % 0x8000);
				#printf("I calculated the offset as %x\n", $textOffset);
				$text		= $this->getStringAtOffsetArray($textOffset);
			}
			return new Textbox(
							$offset,
							$data,
							$u1,
							$screenX,
							$screenY,
							$windowW,
							$windowH,
							$u6,
							$cursorOptions,
							$cursorX,
							$cursorY,
							$u10,
							$textPointer,
							$textOffset,
							$text
							);
		}



		protected function _romI($o, $l = 1) {
			if ($l == 1) {
				return ord($this->_rom{$o});
			} else {
				return Utils::toIntLE(substr($this->_rom, $o, $l));
			}
		}

		protected function _romS($o, $l = 1) {
			return substr($this->_rom, $o, $l);
		}


		protected function _readROM($file) {
			$this->_rom		= file_get_contents($file);
		}

		protected function _readTable($file) {
			$table	= file_get_contents($file);
			$tableA	= explode("\n", $table);
			$tableOut	= array();

			foreach ($tableA as $str) {
				if ($x = trim($str)) {
					$row	= explode("=", $x, 2);
					$row[0]	= hexdec($row[0]);

					if ($row[0] == 0x20) $row[1] = " ";
					if ($row[0] == 0xFE) $row[1] = "\n";

					if ($row[1] == "[END]") $this->_terminator = $row[0];
					$tableOut[$row[0]]	= $row[1];
				}
			}
			$this->_table	= $tableOut;
		}

	}
