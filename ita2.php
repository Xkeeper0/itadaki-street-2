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



	class Textbox {

		// Offset of textbox data
		public	$textboxOffset	= null;

		// Unknown. 02 draws normally, 00, 01, 03 make it invisible, 04 locks up?
		public	$u1				= null;
		// Where the left border of the window sits. 0 = against the left edge
		public	$screenX		= null;
		// Where the top border of the window sits. 0 = against the top edge
		public	$screenY		= null;
		// Width of the window, including borders. 00 and 01 are very bad
		public	$windowW		= null;
		// Height of the window, including borders.
		public	$windowH		= null;
		// ??? Causes ... problems if changed
		public	$u6				= null;
		// Cursor option count
		public	$cursorOptions	= null;
		// Cursor starting X
		public	$cursorX		= null;
		// Cursor starting Y
		public	$cursorY		= null;
		// ? 0x10, shows names list if 09?
		public	$u10			= null;

		public	$textPointer	= null;
		public	$textOffset		= null;
		public	$text			= null;

		/**
		 *
		 */
		public function __construct($tbofs, $u1, $x, $y, $w, $h, $u6, $co, $cx, $cy, $u10, $ptr, $ofs, $text) {
			$this->textboxOffset	= $tbofs;
			$this->u1				= $u1;
			$this->screenX			= $x;
			$this->screenY			= $y;
			$this->windowW			= $w;
			$this->windowH			= $h;
			$this->u6				= $u6;
			$this->cursorOptions	= $co;
			$this->cursorX			= $cx;
			$this->cursorY			= $cy;
			$this->u10				= $u10;
			$this->textPointer		= $ptr;
			$this->textOffset		= $ofs;
			$this->text				= $text;
		}


		public function __toString() {
			$x = sprintf(
					"Textbox: u1: %02x - u6: %02x - u10: %02x\n".
					"Position: %02x, %02x (%02x x %02x)\n".
					"Cursor: %02x options, starts at %02x, %02x\n".
					"Offset: %06x - Text pointer %04x (= %06x)\n".
					"-----------------------\n%s\n-----------------------\n",
					$this->u1, $this->u6, $this->u10,
					$this->screenX, $this->screenY, $this->windowW, $this->windowH,
					$this->cursorOptions, $this->cursorX, $this->cursorY,
					$this->textboxOffset, $this->textPointer, $this->textOffset,
					implode("", $this->text)
					);
			return $x;
		}

		public function prettyPrint() {
			$grid					= array_fill(0, 28, array_fill(0, 32, null));

			// Quick vars
			$btop					= $this->screenY;
			$bbottom				= $btop + $this->windowH - 1;
			$bleft					= $this->screenX;
			$bright					= $bleft + $this->windowW - 1;

			// Draw border
			for ($yp = $btop; $yp <= $bbottom; $yp++) {
				for ($xp = $bleft; $xp <= $bright; $xp++) {
					$c		= "";
					if ($yp == $btop) {
						if		($xp == $bleft) 	$c	= "┌";
						elseif	($xp == $bright)	$c	= "┐";
						else						$c	= "─";
					} elseif ($yp == $bbottom) {
						if		($xp == $bleft) 	$c	= "└";
						elseif	($xp == $bright)	$c	= "┘";
						else						$c	= "─";
					} elseif ($xp == $bleft) {
						$c	= "│";
					} elseif ($xp == $bright) {
						$c	= "│";
					}
					$grid[$yp][$xp]	= $c;
				}
			}


			// Place the string
			//$len	= mb_strlen($this->text);
			$xp		= $bleft + 1;
			$yp		= $btop + 1;
			//for ($i = 0; $i < $len; $i++) {
			foreach ($this->text as $char) {
				//$char	= mb_substr($this->text, $i, 1);

				if ($char == "゙" || $char == "゚") {
					// Handle combining chars.
					$grid[$yp - 1][$xp - 1]	= $char;

				} elseif ($char == "\n") {
					// Newline
					$yp++;
					$xp	= $bleft + 1;

				} else {
					$grid[$yp][$xp]	= $char;
					$xp++;
				}
			}

			if ($this->cursorOptions) {
				for ($i = 0; $i < $this->cursorOptions; $i++) {
					$xp	= $this->cursorX;
					$yp	= $this->cursorY + 2 * $i;

					$grid[$yp][$xp]	= ($grid[$yp][$xp] ? "<s>". $grid[$yp][$xp] ."</s> " : "") . ($i == 0 ? "►" : "▻");
				}
			}

			// mb_strlen, mb_substr

			// Render the table
			print "<table class='menugrid'>\n";
			for ($y = 0; $y < 28; $y++) {
				print "\t<tr>\n";
				for ($x = 0; $x < 32; $x++) {
					$c	= ($grid[$y][$x] !== null ? " class='c'" : "");
					print "\t\t<td$c>". $grid[$y][$x] ."</td>\n";
				}
				print "\t</tr>\n";
			}
			print "</table>\n";
		}


	}
