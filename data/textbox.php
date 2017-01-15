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

		/**
		 *
		 */
		public function __construct(\ItadakiStreet2 $translator, $offset, $header, $textOffset = null) {
			$this->_translator	= $translator;
			$this->_offset		= $offset;
			$this->_headerData	= $header;
			$this->_textOffset	= $textOffset;
		}

		/**
		 * Decode the header of a textbox into something usable
		 */
		protected function _parseTextbox() {

		}


		/**
		 * Simple text rendering of a textbox's definition / contents
		 */
		public function __toString() {
			$x = sprintf(
					"Textbox"
					);
			return $x;
		}

		/**
		 * Output the textbox as a pretty-looking grid,
		 * resembling the game's screen
		 */
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
			$xp		= $bleft + 1;
			$yp		= $btop + 1;

			foreach ($this->text as $char) {

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
