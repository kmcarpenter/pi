<?php
        /**
	 *  PiPic: Create an image of pi by mapping colors to a picture.
         *
         *  Copyright (C) 2008-2009  Michael Carpenter (mcarpent@zenwerx.com)
         *
         *  This program is free software; you can redistribute it and/or modify
         *  it under the terms of the GNU General Public License as published by
         *  the Free Software Foundation; either version 2 of the License, or
         *  (at your option) any later version.
         *
         *  This program is distributed in the hope that it will be useful,
         *  but WITHOUT ANY WARRANTY; without even the implied warranty of
         *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         *  GNU General Public License for more details.
         *
         *  You should have received a copy of the GNU General Public License
         *  along with this program in the file COPYING; if not, write to the
         *  Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
         *  Boston, MA 02110-1301 USA
         */

	/******************************************************************************
	 * CHANGELOG                                                                  *
 	 ******************************************************************************
	 *
	 * 2009-08-13: Add ability to use arbitrary start positions
	 *
	 * 2008-04-04: Modify while loop to check for is_numeric rather than CR & LF
	 *             Also defined some constants rather than hard coded values
	 *			- Mike
	 * 2008-04-04: Release under GPL
	 *			- Mike
	 * 2008-04-03: Cleanup and Prettification
	 *			- Mike
	 * 2008-04-02: Initial hack together
	 *			- Mike
	 *
	 *
	 */

	// Define some defaults
	define ('MAX_SIZE', 	4194304	);
	define ('MAX_SELECT',      2048 );
	define ('DEFAULT_X',	    300	);
	define ('DEFAULT_Y', 	    300	);
	define ('ERR_TOO_BIG', 	     -1	);
	define ('ERR_NO_PI', 	     -2	);
	define ('ERR_BAD_PARAM',     -3 );
	define ('ERR_BAD_START',     -4 );
	define ('SUCCESS',	      0	);
	define ('MAX_PLACES',	      8 );
	define ('MAX_NUM',	      9 );
	define ('TOTALDIG',   100000000 );
	define ('PIFILE',"pihundredmil" );
	define ('EFILE', "ehundredmil"	);
	define ('LNFILE',"lnhundredmil" );
	define ('PHIFILE',"phihundredmil");

	// Build Pi
	function buildPi($dig_x = DEFAULT_X, $dig_y = DEFAULT_Y, $startdig = 0, $constant = PIFILE) {
		$prefix = "";
		switch ($constant)
		{
			case "e":
				$constant = EFILE;
				$prefix = "e";
				break;
			case "pi":
			default:
				$constant = PIFILE;
				$prefix = "pi";
				break;
		}
		// Don't go over our maximum size
		$numdigits = $dig_x * $dig_y;
		if ($numdigits > MAX_SIZE) {
			return ERR_TOO_BIG;
		}
		if ((TOTALDIG - $numdigits) < $startdig) {
			return ERR_BAD_START;
		}
		// Use sane values
		if (  $dig_x > MAX_SELECT || $dig_x < 0 || $dig_y > MAX_SELECT || $dig_y < 0 ) {
			return ERR_BAD_PARAM;
		}

		$fname = sprintf("cache/$prefix%04dx%04d_s%09d.png", $dig_x, $dig_y, $startdig);
		$bw_fname = sprintf("cache/bw_$prefix%04dx%04d_s%09d.png", $dig_x, $dig_y, $startdig);
		// Try to used a cached file
		if (!file_exists($fname)) {			
			// Create image and colors 
			$gd = imagecreatetruecolor($dig_x, $dig_y);
			$gdbw = imagecreatetruecolor($dig_x, $dig_y);

			$cols = array( 
                            	imagecolorallocate($gd, 0xFF, 0xFF, 0xFF),
                                imagecolorallocate($gd, 0x00, 0xFF, 0xFF),
                                imagecolorallocate($gd, 0x00, 0x00, 0xFF),
                                imagecolorallocate($gd, 0xFF, 0x00, 0xFF),
                                imagecolorallocate($gd, 0x00, 0xFF, 0x00),
                                imagecolorallocate($gd, 0xFF, 0xA5, 0x00),
                                imagecolorallocate($gd, 0xFF, 0x00, 0x00),
                                imagecolorallocate($gd, 0xFF, 0xFF, 0x00),
                                imagecolorallocate($gd, 0x80, 0x80, 0x80),
                         	imagecolorallocate($gd, 0x00, 0x00, 0x00)
			);

			// Make sure we can get the contents of the pi file
			$piF = fopen($constant, 'r');
			if (!$piF)
			{
				return ERR_NO_PI;
			}
			fseek($piF, $startdig);
			$pi = fread($piF, $numdigits);
			if (!$pi) {
				return ERR_NO_PI;
			}

			// Build and image pixel by pixel
			$c = 0;
			for ($y = 0; $y < $dig_y; $y++) {
				for ($x=0; $x < $dig_x; $x++) {
					while (!is_numeric($pi[$c])) {
						$c++;
					}
					imagesetpixel($gd, $x, $y, $cols[$pi[$c]]);
					$c++;
				}
			}
			imagepng($gd, $fname);
			convertToGrayscale($gd, $dig_x, $dig_y, $bw_fname);
		} 

		return SUCCESS;
	}

	function convertToGrayscale($colImage, $x, $y, $fname) {
		$bwImage = imageCreate($x,$y);
		for ($c = 0; $c < 256; $c++) {     
    			imagecolorallocate($bwImage, $c,$c,$c);
		}
		imagecopymerge($bwImage, $colImage, 0,0,0,0, $x, $y, 100);
		imagepng($bwImage, $fname);
	}

	// Defaults
	$x = DEFAULT_X;
	if (isset($_GET['x']) && is_numeric($_GET['x'])) {
		$x = (int) $_GET['x'];
	}
	$y = DEFAULT_Y;
	if (isset($_GET['y']) && is_numeric($_GET['y'])) {
		$y = (int) $_GET['y'];
	}
	$start = array();
	$startDig = "";
	for ($i=0;$i<MAX_PLACES;$i++)
	{
		if (isset($_GET["s$i"]) && is_numeric($_GET["s$i"]) && $_GET["s$i"] > 0 && $_GET["s$i"] <= MAX_NUM) {
			$start[$i] = $_GET["s$i"];
		} else {
			$start[$i] = 0;
		}
		$startDig .= $start[$i];	
	}
	$startDig = (int) $startDig; // Cast to an integer
	$const = "pi";
	if (isset($_GET["c"]) && is_string($_GET["c"]) && strlen($_GET["c"]) <= 3) {
		$const = $_GET["c"];
	}
	$c = "&pi;";
	if ($const == "e")
	{
		$c = "e";
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<title>Visualize <?php echo $c ?>!</title>
		<script type='text/javascript'>
		// <![CDATA[
			function calculatePi() {
				var x = document.getElementById('x');
				x = parseInt(x.options[x.selectedIndex].value);
				var y = document.getElementById('y');
				y = parseInt(y.options[y.selectedIndex].value);
				document.getElementById('digCount').innerHTML = x*y;
				calculateDig();
			}

			function calculateDig()
			{
				var count = parseInt(document.getElementById('digCount').innerHTML);
				var start = "";
				for (i=0;i<<?php echo MAX_PLACES ?>;i++)
				{
					var idx = "s" + i;
					var obj = document.getElementById(idx);
					start = start + obj.options[obj.selectedIndex].value;
				}
				obj = document.getElementById('digView');
				start = parseInt(start);
				if (start + count > <?php echo TOTALDIG ?>){
					obj.innerHTML = "Digits surpass length of pi on server : <?php echo TOTALDIG ?>";
					return;
				}
				obj.innerHTML = (start + 1) + " &ndash; " + (start + count);
			}

			function flipGrayscale() {
				var img = document.getElementById('img');
				var x = document.getElementById('x');
				x = x.options[x.selectedIndex].text;
                                var y = document.getElementById('y');
				y = y.options[y.selectedIndex].text;
				var c = document.getElementById('c');
				c = c.options[c.selectedIndex].value;
				start = "0";
				for (i=0;i<<?php echo MAX_PLACES ?>;i++)
				{
					idx = "s" + i;
					var obj = document.getElementById(idx);
					start = start + obj.options[obj.selectedIndex].value;
				}
				img.src='/piorig/cache/bw_' + c + x + 'x' + y + '_s' + start + '.png';
				img.onclick = function() { flipColor(); };
			}

			function flipColor() {
				var img = document.getElementById('img');
				var x = document.getElementById('x');
				x = x.options[x.selectedIndex].text;
                                var y = document.getElementById('y');
				y = y.options[y.selectedIndex].text;
				var c = document.getElementById('c');
				c = c.options[c.selectedIndex].value;
				var start = "0";
				for (i=0;i<<?php echo MAX_PLACES ?>;i++)
				{
					var idx = "s" + i;
					var obj = document.getElementById(idx);
					start = start + obj.options[obj.selectedIndex].value;
				}
				img.src='/piorig/cache/' + c + x + 'x' + y + '_s' + start + '.png';
				img.onclick = function() { flipGrayscale() };
			}
		// ]]>
		</script>
		<style type='text/css'>
			img { border: 1px solid black; }
			a.valid { font-size: 0.6em; }
		</style>
	</head>
	<body onload='calculatePi();calculateDig();'>
	<h1>Visualize <?php echo $c; ?>!</h1>
	<p>
		For a list of pre-generated pictures check out the <a href='/piorig/cache/'>cache</a>. Or have fun 
		looking at <a href='/piorig/'>4 million digits</a> of &pi;. If you would like to create your own page
		using a script like this one, you can <a href='/pipic.tar.gz'>download the source</a> (Licensed
		under the GNU GPL). 
	</p>
	<p>
<script type="text/javascript"><!--
google_ad_client = "pub-7136387287433354";
/* 728x90, created 4/22/10 */
google_ad_slot = "0540323117";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
	</p>
	<p>
		Each digit of <?php echo $c ?> is mapped to a color and then stored to 
		the picture shown below!
	</p>
	<p>
		<a class='valid' href="http://validator.w3.org/check?uri=referer">XMTL 1.0 Strict</a>
	</p>
	<div style='float: left'>
		<h1>Color:</h1>
		<ul>
			<li>0 <img src='0.png' alt='0' title='0' /></li>
			<li>1 <img src='1.png' alt='1' title='1' /></li>
			<li>2 <img src='2.png' alt='2' title='2' /></li>
			<li>3 <img src='3.png' alt='3' title='3' /></li>
			<li>4 <img src='4.png' alt='4' title='4' /></li>
			<li>5 <img src='5.png' alt='5' title='5' /></li>
			<li>6 <img src='6.png' alt='6' title='6' /></li>
			<li>7 <img src='7.png' alt='7' title='7' /></li>
			<li>8 <img src='8.png' alt='8' title='8' /></li>
			<li>9 <img src='9.png' alt='9' title='9' /></li>
		</ul>
	</div>
	<div style='float: left; margin-left: 100px'>
	<?php
		// Build picture
		$err = buildPi($x, $y, $startDig, $const);
		$file = sprintf("%s%04dx%04d_s%09d.png", $const, $x, $y, $startDig);
		if ($err == SUCCESS) {
			$c = "&pi;";
			if ($const == "e")
			{
				$c = "e";
			}
			echo "\t<h1>$c: $x x $y @ $startDig</h1>\n";
			echo "\t<img src='/piorig/cache/$file' id='img' alt='Pi/e/etc...' title='Pi/e/etc...' onclick='flipGrayscale()' />";
		} else {
			switch ($err) {
			case ERR_TOO_BIG:
				echo "\t<h2>Width x Height cannot exceed " . MAX_SIZE . "!</h2>\n";
				break;
			case ERR_NO_PI:
				echo "\t<h2>Can't open PI file!</h2>\n";
				break;
			case ERR_BAD_PARAM:
				echo "\t<h2>Invalid parameter: X & Y must satisfy: 1 <= (x & y) <= " . MAX_SELECT . "!</h2>\n";
				break;
			case ERR_BAD_START:
				echo "\t<h2>The starting place you selected is too big to display the number of digits you asked for.<br/>Start <= ( " . TOTALDIG . " - (X * Y) )!<br/> In this case: " . (TOTALDIG - ($x*$y)) . " </h2>\n";
				break;
			default:
				echo "\t<h2>Unknown Error</h2>\n";
			}
		}
		// End picture
	?>
	<p>Click image to swap between color and grayscale.</p>
	<form method='get' action='/piorig/pipic.php'>
		<table>
		<tr>
			<td>Constant:</td>
			<td>
				<select id='c' name='c'>
					<option value='pi' <?php if ($const == 'pi') { echo "selected='selected'"; } ?> >
					&pi;</option>
					<option value='e' <?php if ($const == 'e') { echo "selected='selected'"; } ?> >
					e</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Offset:</td>
			<td>
				<?php
					for ($i=0;$i<MAX_PLACES;$i++)
					{
						echo "\t\t<select id='s$i' name='s$i' onchange='calculateDig();'>";
						for ($j=0;$j<=MAX_NUM;$j++)
						{
							echo "\t\t\t<option value='$j'";
							if ($j==$start[$i]) { echo " selected='selected' "; }
							echo ">$j</option>\n";
						}
						echo "\t\t</select>";
					}
				?>
			</td>
		</tr>
		<tr>
			<td>Width (x):</td>
			<td>
				<select id='x' name='x' onchange='calculatePi()'>
				<?php
					// X Select box
					for ($i=1; $i<=MAX_SELECT; $i++) {
						echo "\t\t\t\t<option value='$i'";
						if ($i == $x) { echo " selected='selected' "; }
						echo ">" . sprintf("%04d", $i) . "</option>\n";
					}
					// End X Select
				?>
				</select>
			</td>
		</tr><tr>
			<td>
				Height (y): 
			</td>
			<td>
				<select id='y' name='y' onchange='calculatePi()'>
				<?php
					// Y Select box
					for ($i=1; $i<=MAX_SELECT; $i++) {
						echo "\t\t\t<option value='$i'";
						if ($i == $y) { echo " selected='selected' "; }
						echo ">" . sprintf("%04d", $i) . "</option>\n";
					}
					// End Y  Select
				?>
			
				</select>
			</td>
		</tr><tr>
			<td>
				Digit Count:
			</td>
			<td id='digCount'>
			</td>
		</tr><tr>
			<td>
				Digit View:
			</td>
			<td id='digView'>
			</td>
		</tr>
	</table>
	<p>
		<input type='submit' value='Load' />
	</p>
	</form>
	</div>
	<p>
<script type="text/javascript"><!--
google_ad_client = "pub-7136387287433354";
/* 728x90, created 4/22/10 */
google_ad_slot = "0540323117";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
	</p>
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {
	var pageTracker = _gat._getTracker("UA-15848155-1");
	pageTracker._trackPageview();
	} catch(err) {}</script>
	</body>
</html>

