<?php

	class Textbox {

		protected	$_translator	= null;
		protected	$_offset		= null;
		protected	$_headerData	= null;
		protected	$_textOffset	= null;

		/**
		 *
		 */
		public function __construct(Translator $translator, $offset, $header, $textOffset = null) {
			$this->_translator	= $translator;
			$this->_offset		= $offset;
			$this->_headerData	= $header;
			$this->_textOffset	= $textOffset;
		}

		protected function _parseTextbox() {

		}


		public function __toString() {
			$x = sprintf(
					"Textbox: %s\n".
					"u1: %02x - u6: %02x - u10: %02x\n".
					"Position: %02x, %02x (%02x x %02x)\n".
					"Cursor: %02x options, starts at %02x, %02x\n".
					"Offset: %06x - Text pointer %04x (= %06x)\n".
					"-----------------------\n%s\n-----------------------\n",
					Utils::printableHex($this->headerData),
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
