<?php

	namespace ItadakiStreet2;

	class Textbox {

		// Translator class to handle text
		protected	$_translator	= null;
		// Offset into ROM for this textbox
		protected	$_offset		= null;
		// Data from the textbox definition
		protected	$_headerData	= null;

		// Offset to the textbox's... text
		protected	$_textOffset	= null;
		protected	$_textOffsetROM	= null;
		// And said text
		protected	$_text			= null;
		// Screen position
		protected	$_position		= null;
		// Cursor position / data
		protected	$_cursor		= null;

		protected	$_unknown		= array();

		/**
		 *
		 */
		public function __construct(\ItadakiStreet2 $translator, $offset, $header, $textOffsetROM = null) {
			$this->_translator		= $translator;
			$this->_offset			= $offset;
			$this->_headerData		= $header;
			$this->_textOffsetROM	= $textOffsetROM;
			$this->_parseTextbox();
		}

		/**
		 * Decode the header of a textbox into something usable
		 */
		protected function _parseTextbox() {
			$data		= new \DataSeeker($this->_headerData);

			try {
				while (!$data->isEOF()) {
					$dtype	= $data->getI();

					switch ($dtype) {

						case 0x02:
							// Screen position; X, Y, Width, Height
							$this->_position	 = array(
								'x'	=> $data->getI(),
								'y'	=> $data->getI(),
								'w'	=> $data->getI(),
								'h'	=> $data->getI(),
								);
							break;

						case 0x04:
							// Unknown, two bytes
							//$this->_unknown[]	= array(0x04, array($data->getI(2)));
							// Text pointer for the actual text
							$offset		= $data->getI(2);
							$offsetROM	= 0x78000 + $offset;
							$this->_textOffset	= $offset;
							if (!$this->_textOffsetROM) $this->_textOffsetROM = $offsetROM;
							break;							break;


						case 0x06:
							// Cursor details. # options, starting X and Y position
							$this->_cursor	 = array(
								'options'	=> $data->getI(),
								'x'			=> $data->getI(),
								'y'			=> $data->getI(),
								);
							break;

						case 0x08:
							// Unknown, two bytes
							$this->_unknown[]	= array(0x08, array($data->getI(2)));
							break;

						case 0x10:
							// Text pointer for the actual text
							$offset		= $data->getI(2);
							$offsetROM	= 0x68000 + $offset;
							$this->_textOffset	= $offset;
							if (!$this->_textOffsetROM) $this->_textOffsetROM = $offsetROM;
							break;

						case 0x00:
							// End
							break 2;

						default:
							throw new \Exception(sprintf("Unhandled header argument \$%02x", $dtype));
							break;
					}
				}
			} catch (\Exception $e) {
				print $e->getMessage() ."\n";
			}

			if ($this->_textOffsetROM) {
				$this->_text	= $this->_translator->getStringAtOffsetArray($this->_textOffsetROM);
			}

		}


		/**
		 * Simple text rendering of a textbox's definition / contents
		 */
		public function __toString() {

			$out	= sprintf("Textbox, offset \$%06X, header [%s]\n", $this->_offset, \Utils::printableHex($this->_headerData));
			if ($this->_position) {
				$out	.= sprintf("  Position: %d, %d, size %d x %d\n", $this->_position['x'], $this->_position['y'], $this->_position['w'], $this->_position['h']);
			}
			if ($this->_cursor) {
				$out	.= sprintf("  Cursor: %d options, position %d x %d\n", $this->_cursor['options'], $this->_cursor['x'], $this->_cursor['y']);
			}
			if ($this->_textOffset) {
				$out	.= sprintf("  Text: Pointer \$%04x (ROM ~ \$%06x)\n", $this->_textOffset, $this->_textOffsetROM);
			}
			if ($this->_unknown) {
				foreach ($this->_unknown as $uk) {
					$out	.= sprintf("  Unknown \$%02x: %s\n", $uk[0], implode(", ", $uk[1]));
				}
			}

			return $out;

		}

		/**
		 * Output the textbox as a pretty-looking grid,
		 * resembling the game's screen
		 */
		public function prettyPrint() {
			$grid					= array_fill(0, 28, array_fill(0, 32, null));

			// Quick vars
			$btop					= $this->_position['y'];
			$bbottom				= $btop + $this->_position['h'] - 1;
			$bleft					= $this->_position['x'];
			$bright					= $bleft + $this->_position['w'] - 1;

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
			$xp		= $bleft + 1;
			$yp		= $btop + 1;

			foreach ($this->_text as $char) {

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

			if ($this->_cursor) {
				for ($i = 0; $i < $this->_cursor['options']; $i++) {
					$xp	= $this->_cursor['x'];
					$yp	= $this->_cursor['y'] + 2 * $i;

					$grid[$yp][$xp]	= ($grid[$yp][$xp] ? "<s>". $grid[$yp][$xp] ."</s> " : "") . ($i == 0 ? "►" : "▻");
				}
			}

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
