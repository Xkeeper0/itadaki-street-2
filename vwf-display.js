function getTextInput()
{
	return document.getElementById("textboxinput");
}

function getCanvas()
{
	return document.getElementById("textboxcanvas");
}

function clearCanvas()
{
	getCanvas().getContext("2d").fillStyle = "#000000";
	getCanvas().getContext("2d").fillRect(0,0,1024,1024);
}

function drawLetter(chr, x, y)
{
	var vwffont = document.getElementById("vwffont");
	var charcode = chr.charCodeAt(0);

	if ((charcode < 32) || (charcode > 127)) return false;

	getCanvas().getContext("2d").drawImage(
		vwffont, 
		Math.floor(charcode % 16) * 12, 
		Math.floor(charcode / 16) * 12, 
		12, 
		12, 
		x, 
		y, 
		12, 
		12
	);

	return charcode;
}

function drawText()
{
	var charwidths = new Array(
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
         4,  6,  6, 12,  8, 11, 11,  4,  5,  5,  6,  6,  4,  6,  4,  7,
         8,  4,  8,  8,  8,  8,  8,  8,  8,  8,  4,  4,  5,  6,  5,  8,
        12, 10,  8,  8,  8,  8,  8,  9,  8,  4,  7, 10,  8, 12,  9,  8,
         8,  9,  9,  8,  8,  9, 10, 12, 12,  8,  8,  4,  7,  4,  6, 12,
        12,  7,  7,  6,  7,  6,  6,  7,  8,  4,  5,  8,  4, 12,  8,  6,
         7,  7,  6,  6,  4,  7,  8, 12,  7,  8,  6,  7, 12,  7, 11, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
                );

	var text = getTextInput().value;
	var x = 0;
	var y = 0;
	var i;

	for (i = 0; i < text.length; i++)
	{
		if (text.charAt(i) == "\n")
		{
			x = 0;
			y += 16;
			continue;
		}
		else if (text.charCodeAt(i) < 128)
		{
			drawLetter(text.charAt(i), x, y);
			x += charwidths[text.charCodeAt(i)];
		}
	}
}

window.onload = function()
{
	clearCanvas();
	drawText();

	getTextInput().addEventListener("input", function() {clearCanvas(); drawText();}, false);
}
