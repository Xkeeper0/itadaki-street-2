<?php

	namespace IS2;
	use \Utils\DataSeeker;
	use \IS2\Text\BigText;

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
		// ?
		protected	$_numberInput	= null;

		protected	$_rawParams		= array();

		/**
		 *
		 */
		public function __construct(ItadakiStreet2 $translator, $offset, $textOffsetROM = null) {
			$this->_translator		= $translator;
			$this->_offset			= $offset;
			$this->_textOffsetROM	= $textOffsetROM;
			$this->_parseTextbox();
		}

		/**
		 * Decode the header of a textbox into something usable
		 */
		protected function _parseTextbox() {
			$rom		= $this->_translator->rom();
			$data		= new DataSeeker($rom);
			$data2		= new DataSeeker($rom);
			$data->seek($this->_offset);

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

							// Temporarily move our read pointer around
							$data2->seek($tmpCurrentPointer);

							// Continuously fetch stuff
							while ($tmpPointerValue = $data2->getI(2)) {
								if ($tmpPointerValue >= 0x8000) {
									$tmpPointerValueROM	= $tmpPointerValue + 0x68000;
									$text				= $this->_translator->getSmallText($tmpPointerValueROM)->getAsArray();
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
							$text			= $this->_translator->getSmallText($offsetROM)->getAsArray();
							if ($offset < 0x8000) {
								// This is not actually text, uh oh
								$text		= array(sprintf("<span title='\$%04X' class='specialChar'>�</span>", $offset));
								$offsetROM 	= false;
							}
							$this->_title	= array(
								'offset'	=> $offset,
								'offsetROM'	=> $offsetROM,
								'text'		=> $text,
								);
							break;

						case 0x0a:
							// Seems to be a horizontal number input,
							// like when investing or w/e
							$posX			= $data->getI();
							$posY			= $data->getI();
							$unk			= $data->getI();
							$this->_numberInput	= array(
								'x'			=> $posX,
								'y'			=> $posY,
								'length'	=> $unk,
								);
							break;

						case 0x0c:
							// Big textbox text offset
							$offset			= $data->getI(2);
							$offsetROM		= 0x68000 + $offset;
							$this->_bigText	= array(
								'offset'	=> $offset,
								'offsetROM'	=> $offsetROM,
								'text'		=> $this->_translator->getBigText($offsetROM),
								);
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
							$text		= $this->_translator->getSmallText($offsetROM)->getAsArray();
							if ($offset < 0x8000) {
								// This is not actually text, uh oh
								$text	= array(sprintf("<span title='\$%04X' class='specialChar'>�</span>", $offset));
								$offsetROM	= false;
							}
							$this->_text	= array(
								'offset'	=> $offset,
								'offsetROM'	=> $offsetROM,
								'text'		=> $text,
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
				$paramsText	.= ($paramsText ? ", " : "") . \Utils\Convert::printableHex($param);
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

			if ($this->_title['text']) {
				$out	.= "\n----------------------------\n". implode("", $this->_title['text']) ."\n";
			}
			if ($this->_text) {
				$out	.= "\n----------------------------\n". implode("", $this->_text['text']) ."\n----------------------------\n";
			}


			if ($this->_bigText) {
				$out	.= sprintf("  Big text: \$%04X (ROM: \$%06X)\n", $this->_bigText['offset'], $this->_bigText['offsetROM']);
				$bigText		= $this->_bigText['text']->getAsString();
				$bigTextArray	= $this->_bigText['text']->getRawAsArray();
				$out			.= $bigText ."\n";
				foreach ($bigTextArray as  $char) {
					$out	.= ($char === -1 ? "\n" : BigText::getImage($char));
				}
				$out	.= "\n";
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
			$btop					= $this->_position ? $this->_position['y'] : 0x0A;
			$bbottom				= $btop + ($this->_position ? $this->_position['h'] : 0x07) - 1;
			$bleft					= $this->_position ? $this->_position['x'] : 0x09;
			$bright					= $bleft + ($this->_position ? $this->_position['w'] : 0x0E) - 1;

			// Draw border
			if ($this->_position) {
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

			if ($this->_numberInput) {
				$grid[$this->_numberInput['y']][$this->_numberInput['x']]	= sprintf("%02X", $this->_numberInput['length']);
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
