function getTextInput()
{
	return document.getElementById("textboxinput");
}

function getTextOutput()
{
	return document.getElementById("textboxoutput");
}

function getHexInputSelect()
{
	return document.getElementById("hexinput");
}

function clearOutput()
{
	getTextOutput().value = '';
}

function translateText()
{
	var translationTable = new Array(
		'\x00'  , '\u250C', '\u2500', '\u2510', '\u2502'   , '\u2502', '\u2514', '\u2500', '\u2518', '\x09'  , '\u2554', '\u255A', '\u2557', '\u255D', '\u2BC8', '\u2BC6',
		'\u2660', '\u2665', '\u2666', '\u2663', '\u{1F83A}', ' '     , ' '     , ' '     , '\u233A', '\x19'  , '\u2551', '\u2550', '\u2551', '\u2550', '\u2BC7', '\u2BC5',
		' '     , '!'     , '\u300C', '\u300D', '$'        , '%'     , '&'     , '\''    , '('     , ')'     , '\u00D7', '+'     , ','     , '-'     , '.'     , '/'     ,
		'0'     , '1'     , '2'     , '3'     , '4'        , '5'     , '6'     , '7'     , '8'     , '9'     , ':'     , ';'     , '['     , '='     , ']'     , '?'     ,
		'\u00A9', 'A'     , 'B'     , 'C'     , 'D'        , 'E'     , 'F'     , 'G'     , 'H'     , 'I'     , 'J'     , 'K'     , 'L'     , 'M'     , 'N'     , 'O'     ,
		'P'     , 'Q'     , 'R'     , 'S'     , 'T'        , 'U'     , 'V'     , 'W'     , 'X'     , 'Y'     , 'Z'     , '\u2491', '\u250C', '\u2500', '\u2510', '_'     ,
		'\x60'  , '\x61'  , '\x62'  , '\x63'  , '\x64'     , '\x65'  , '\x66'  , '\x67'  , '\x68'  , '\x69'  , '\x6A'  , '\x6B'  , '\x6C'  , '\x6D'  , '\x6E'  , '\x6F'  ,
		'\x70'  , '\x71'  , '\x72'  , '\x73'  , '\x74'     , '\x75'  , '\x76'  , '\x77'  , '\x78'  , '\x79'  , '\x7A'  , '\x7B'  , '\x7C'  , '\x7D'  , '\x7E'  , '\x7F'  ,
		'\u3042', '\u3044', '\u3046', '\u3048', '\u304A'   , '\u304B', '\u304D', '\u304F', '\u3051', '\u3053', '\u3055', '\u3057', '\u3059', '\u305B', '\u305D', '\u305F',
		'\u3061', '\u3064', '\u3066', '\u3068', '\u306A'   , '\u306B', '\u306C', '\u306D', '\u306E', '\u306F', '\u3072', '\u3075', '\u3078', '\u307B', '\u307E', '\u307F',
		'\u3080', '\u3081', '\u3082', '\u3084', '\u3086'   , '\u3088', '\u3089', '\u308A', '\u308B', '\u308C', '\u308D', '\u308F', '\u3092', '\u3093', '\u3083', '\u3085',
		'\u3087', '\u3063', '\u3001', '\u3002', '\u3099'   , '\u309A', '\u30FC', '\u309B', '\u309C', '\u3041', '\u3043', '\u3045', '\u3047', '\u3049', '\u304B', '\u306A',
		'\u30A2', '\u30A4', '\u30A6', '\u30A8', '\u30AA'   , '\u30AB', '\u30AD', '\u30AF', '\u30B1', '\u30B3', '\u30B5', '\u30B7', '\u30B9', '\u30BB', '\u30BD', '\u30BF',
		'\u30C1', '\u30C4', '\u30C6', '\u30C8', '\u30CA'   , '\u30CB', '\u30CC', '\u30CD', '\u30CE', '\u30CF', '\u30D2', '\u30D5', '\u30D8', '\u30DB', '\u30DE', '\u30DF',
		'\u30E0', '\u30E1', '\u30E2', '\u30E4', '\u30E6'   , '\u30E8', '\u30E9', '\u30EA', '\u30EB', '\u30EC', '\u30ED', '\u30EF', '\u30F2', '\u30F3', '\u30E3', '\u30E5',
		'\u30E7', '\u30C3', '\u30A1', '\u30A3', '\u30A5'   , '\u30A7', '\u30A9', '\u30AB', '\u30CA', '\xF9'  , '\xFA'  , '\xFB'  , '\xFC'  , '\xFD'  , '\xFE'  , '\xFF'  ,
	);

	var text = getTextInput().value;
	var translatedText = '';
	var rawValues = new Array();

	if (getHexInputSelect().checked)
	{
		var textExplode = text.split(' ');
		for (var i = 0; i < textExplode.length; i++)
		{
			var charCode = parseInt(textExplode[i], 16);
			rawValues.push(charCode);

			if ((charCode >= 0x00) && (charCode <= 0xFF))
			{
				translatedText += translationTable[charCode];
			}
			else
			{
				// skip
			}
		}
	}
	else
	{
		for (var i = 0; i < text.length; i++)
		{
			rawValues.push(text.charCodeAt(i));

			if (text.charCodeAt(i) > 0xFF)
			{
				// skip;
			}
			else
			{
				translatedText += translationTable[text.charCodeAt(i)];
			}
		}
	}

	getTextOutput().value = translatedText;
//	return translatedText;
	return rawValues;
}

window.onload = function()
{
	getTextInput().addEventListener("input", function() {clearOutput(); translateText();}, false);
	getHexInputSelect().addEventListener("change", function() {clearOutput(); translateText();}, false);
}
