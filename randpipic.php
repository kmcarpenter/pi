<p>
Here's a random item from my <a href='/piorig/pipic.php'>pi picture</a> script:
</p>

<?php
	$dir = "/home/ahsile/www/zenwerx.com/piorig/cache/";
	$files = scandir($dir);

	// remove . and ..
	array_shift($files);
	array_shift($files);

	$count = count($files);

	$entry = array_rand($files);

	$f =  $files[$entry];
	
	$size = getimagesize($dir . $f);

	$width = $size[0];
	$height = $size[1];
	if ($size[0] > 300)
	{
		$ratio = $height/(float)$width;
		$width = 300;
		$height = (int)(300 * $ratio);
	}

	echo "<a href='/piorig/cache/$f' target='_blank'><img src='/piorig/cache/$f' width='$width' height='$height' /></a>";
?>
