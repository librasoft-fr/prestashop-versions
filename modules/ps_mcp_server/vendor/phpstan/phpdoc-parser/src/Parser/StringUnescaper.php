<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Parser;

use PHPStan\ShouldNotHappenException;
use function chr;
use function hexdec;
use function octdec;
use function preg_replace_callback;
use function str_replace;
use function substr;

class StringUnescaper
{

	private const REPLACEMENTS = [
		'\\' => '\\',
		'n' => "\n",
		'r' => "\r",
		't' => "\t",
		'f' => "\f",
		'v' => "\v",
		'e' => "\x1B",
	];

	public static function unescapeString(string $string): string
	{
		$quote = $string[0];

		if ($quote === '\'') {
			return str_replace(
				['\\\\', '\\\''],
				['\\', '\''],
				substr($string, 1, -1),
			);
		}

		return self::parseEscapeSequences(substr($string, 1, -1), '"');
	}

	
	private static function parseEscapeSequences(string $str, string $quote): string
	{
		$str = str_replace('\\' . $quote, $quote, $str);

		return preg_replace_callback(
			'~\\\\([\\\\nrtfve]|[xX][0-9a-fA-F]{1,2}|[0-7]{1,3}|u\{([0-9a-fA-F]+)\})~',
			static function ($matches) {
				$str = $matches[1];

				if (isset(self::REPLACEMENTS[$str])) {
					return self::REPLACEMENTS[$str];
				}
				if ($str[0] === 'x' || $str[0] === 'X') {
					return chr((int) hexdec(substr($str, 1)));
				}
				if ($str[0] === 'u') {
					if (!isset($matches[2])) {
						throw new ShouldNotHappenException();
					}
					return self::codePointToUtf8((int) hexdec($matches[2]));
				}

				return chr((int) octdec($str));
			},
			$str,
		);
	}

	
	private static function codePointToUtf8(int $num): string
	{
		if ($num <= 0x7F) {
			return chr($num);
		}
		if ($num <= 0x7FF) {
			return chr(($num >> 6) + 0xC0)
				. chr(($num & 0x3F) + 0x80);
		}
		if ($num <= 0xFFFF) {
			return chr(($num >> 12) + 0xE0)
				. chr((($num >> 6) & 0x3F) + 0x80)
				. chr(($num & 0x3F) + 0x80);
		}
		if ($num <= 0x1FFFFF) {
			return chr(($num >> 18) + 0xF0)
				. chr((($num >> 12) & 0x3F) + 0x80)
				. chr((($num >> 6) & 0x3F) + 0x80)
				. chr(($num & 0x3F) + 0x80);
		}

		
		return "\xef\xbf\xbd";
	}

}
