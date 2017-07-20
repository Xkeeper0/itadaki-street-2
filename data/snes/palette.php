<?php

	namespace SNES;

	class Palette {

		protected	$_data		= "";
		protected	$_colors	= array();

		public function __construct($data) {
			if (strlen($data) !== 0x20) {
				throw new \Exception("data is wrong size: should be 32 bytes, is ". strlen($data));
			}

			for ($i = 0; $i < 16; $i++) {
				$this->_parseColor($i, substr($data, $i * 2, 2));
			}
		}

		protected function _parseColor($c, $colorData) {
			$colorRaw	= \Utils::toIntLE($colorData);
			$color	= array();
			$color['raw']	= $colorRaw;
			$color['r']		= ($colorRaw & 0x001F) << 3;
			$color['g']		= ($colorRaw & 0x03E0) >> 2;
			$color['b']		= ($colorRaw & 0x7C00) >> 7;
			$color['html']	= sprintf("#%02x%02x%02x", $color['r'], $color['g'], $color['b']);

			$this->_colors[$c]	= $color;
		}


		public function htmlize() {
			$out	= "\t<div class='palette'>\n";
			for ($i = 0; $i < 16; $i++) {
				$out	.= "\t\t<div style='background: ". $this->_colors[$i]['html']  .";'></div>\n";
			}
			$out	.= "\t</div>\n";
			return $out;
		}

	}
