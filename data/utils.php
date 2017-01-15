<?php

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
