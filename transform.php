<?php

	$p1 = fopen('e-001.txt', 'r');
	$p2 = fopen('ehundredmil', 'w');
	$i = 0;
	while (!feof($p1))
	{
		$i++;
		$buf = fgets($p1);
		$buf = str_replace(" ", "", $buf);
		$buf = substr($buf, 0, 100);
		fwrite($p2, $buf);
		echo "Wrote line: $i\n";
	}

	fclose($p1);
	fclose($p2);
?>
