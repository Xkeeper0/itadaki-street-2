<?php

	namespace ItadakiStreet2;

	class Textbox {

		// Translator class to handle text
		protected	$_translator	= null;
		// Offset into ROM for this textbox
		protected	$_offset		= null;
		// Data from the textbox definition
		protected	$_headerData	= null;

		// Text
		protected	$_text			= null;

		// Big text
		protected	$_bigText		= null;

		// Textbox's title
		protected	$_title			= null;

		// Screen position
		protected	$_position		= null;
		// Cursor position / data
		protected	$_cursor		= null;

		protected	$_concatData	= array();
		protected	$_rawParams		= array();
		protected	$_unknown		= array();

		/**
		 *
		 */
		public function __construct(\ItadakiStreet2 $translator, $offset, $textOffsetROM = null) {
			$this->_translator		= $translator;
			$this->_offset			= $offset;
			$this->_textOffsetROM	= $textOffsetROM;
			$this->_parseTextbox();
		}

		/**
		 * Decode the header of a textbox into something usable
		 */
		protected function _parseTextbox() {
			// 256 bytes chosen as arbitrary limit, should never be reached
			$data		= new \DataSeeker($this->_translator->romS($this->_offset, 0x100));

			try {
				while (!$data->isEOF()) {
					$start	= $data->position();
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
							// Two byte pointer to, sigh, more pointers.
							// Basically, concatenate the strings from those pointers.
							// (It's a zero-terminated list)
							$pointersPointer	= $data->getI(2);
							$tmpCurrentPointer	= $pointersPointer + 0x68000;
							$concatTemp			= array();
							$tmpText			= array();

							// Continuously fetch stuff
							while ($tmpPointerValue = $this->_translator->romI($tmpCurrentPointer, 2)) {
								if ($tmpPointerValue >= 0x8000) {
									$tmpPointerValueROM	= $tmpPointerValue + 0x68000;
									$text				= $this->_translator->getStringAtOffsetArray($tmpPointerValueROM);
								} else {
									$text				= array(sprintf("<span title='\$%04X' class='specialChar'>�</span>", $tmpPointerValue));
									$tmpPointerValueROM	= $tmpPointerValue;
								}
								$tmpText			= array_merge($tmpText, $text);
								$concatTemp[]		= array(
									'offset'	=> $tmpPointerValue,
									'offsetROM'	=> $tmpPointerValueROM,
									'text'		=> $text,
									);
								$tmpCurrentPointer	+= 2;
							}

							$this->_text	= array(
								'text'				=> $tmpText,
								'concat'			=> array(
									'offset'			=> $pointersPointer,
									'offsetROM'			=> $tmpCurrentPointer,
									'strings'			=> $concatTemp,
									),
								);
							break;

						case 0x06:
							// Cursor details. # options, starting X and Y position
							$this->_cursor	 = array(
								'options'	=> $data->getI(),
								'x'			=> $data->getI(),
								'y'			=> $data->getI(),
								);
							break;

						case 0x08:
							// Title of text box; placed across the top border
							$offset			= $data->getI(2);
							$offsetROM		= 0x68000 + $offset;
							$this->_title	= array(
								'offset'	=> $offset,
								'offsetROM'	=> $offsetROM,
								'text'		=> $this->_translator->getStringAtOffsetArray($offsetROM),
							);
							break;

						case 0x0a:
							// ?
							$data->getI();
							$data->getI();
							$data->getI();
							break;

						case 0x0c:
							// Big textbox text offset
							$offset		= $data->getI(2);
							$offsetROM	= 0x68000 + $offset;
							$this->_bigTextOffset	= $offset;
							break;

						case 0x0e:
							// ?
							$data->getI();
							$data->getI();
							$data->getI();
							$data->getI();
							$data->getI();
							$data->getI();
							break;

						case 0x10:
							// Text pointer for the actual text
							$offset		= $data->getI(2);
							$offsetROM	= 0x68000 + $offset;
							$this->_text	= array(
								'offset'	=> $offset,
								'offsetROM'	=> $offsetROM,
								'text'		=> $this->_translator->getStringAtOffsetArray($offsetROM),
								);
							break;

						case 0x82:	// Kludge for weird textboxes. Hey Devin!
						case 0x00:
							// End
							break 2;

						default:
							throw new \Exception(sprintf("Unhandled header argument \$%02x", $dtype));
							break;
					}

					$tmp	= $data->position();
					$data->seek($start);
					$this->_rawParams[]	= $data->getS($tmp - $start);
					$data->seek($tmp);

				}

				$headerLen	= $data->position();
				$data->seek();
				$this->_headerData	= $data->getS($headerLen);
			} catch (\Exception $e) {
				print $e->getMessage() ."\n";
			}

		}


		/**
		 * Simple text rendering of a textbox's definition / contents
		 */
		public function __toString() {

			$paramsText		= "";
			foreach ($this->_rawParams as $param) {
				$paramsText	.= ($paramsText ? ", " : "") . \Utils::printableHex($param);
			}

			$out	= sprintf("Textbox, offset \$%06X, header [%s]\n", $this->_offset, $paramsText);

			// This is kind of an ugly mess, but there isn't too much of a better
			// way to do it I think :/

			if ($this->_position) {
				$out	.= sprintf("  Position: (%d, %d), size %d x %d\n", $this->_position['x'], $this->_position['y'], $this->_position['w'], $this->_position['h']);
			}
			if ($this->_cursor) {
				$out	.= sprintf("  Cursor: %d options, position (%d, %d)\n", $this->_cursor['options'], $this->_cursor['x'], $this->_cursor['y']);
			}
			if (isset($this->_text['offset'])) {
				$out	.= sprintf("  Text: Pointer \$%04x (ROM: \$%06x)\n", $this->_text['offset'], $this->_text['offsetROM']);
			}
			if ($this->_title) {
				$out	.= sprintf("  Title: Pointer \$%04x (ROM: \$%06x)\n", $this->_title['offset'], $this->_title['offsetROM']);
			}
			if (isset($this->_text['concat'])) {
				$out	.= sprintf("  Concatenation: Pointer \$%04X (ROM: \$%06x)\n", $this->_text['concat']['offset'], $this->_text['concat']['offsetROM']);
				foreach ($this->_text['concat']['strings'] as $str) {
					$out	.= sprintf("    \$%04x (\$%06x) \"%s\"\n", $str['offset'], $str['offsetROM'], str_replace("\n", '\n', implode("", $str['text'])));
				}
			}

			if ($this->_bigTextOffset) {
				$out	.= sprintf("  UNHANDLED BIGTEXT: %04X\n", $this->_bigTextOffset);
			}

			if ($this->_unknown) {
				foreach ($this->_unknown as $uk) {
					$uvo	= "";
					foreach ($uk[1] as $uv) {
						$uvo	.= sprintf(" %X", $uv);
					}
					$out	.= sprintf("  Unknown \$%02x: %s\n", $uk[0], $uvo);
				}
			}

			if ($this->_title['text']) {
				$out	.= "\n----------------------------\n". implode("", $this->_title['text']) ."\n";
			}
			if ($this->_text) {
				$out	.= "\n----------------------------\n". implode("", $this->_text['text']) ."\n----------------------------\n";
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


			if ($this->_text) {
				// Place the string
				$xp		= $bleft + 1;
				$yp		= $btop + 1;
				foreach ($this->_text['text'] as $char) {

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
			}


			if ($this->_title) {
				// Place the string
				$xp		= $bleft + 1;
				$yp		= $btop;
				foreach ($this->_title['text'] as $char) {

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
