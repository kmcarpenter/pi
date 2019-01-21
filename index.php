<?php
	 /**
         *  Pi: Display 4 million digits of Pi
         *
         *  Copyright (C) 2008  Michael Carpenter (mcarpent@zenwerx.com)
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


	// Include cpu usage function (license unsure)
	require("cpu.inc.php");

	// Define the two different pi files
	// We display a special message for pi day!
	define("PI_NORM", 	"pi.gz"		);
	define("PI_DAY", 	"piday.gz"	);
	define("TRUNCFILE", 	"pitrunc.txt"	);
	define("PIDAY",		"314"		);
	define("IDLEMIN", 	30		);
	define("ONE_K",		1024		);

	// Check for cpu usage. If it's too high display a 'friendly'
	// message rather than killing the box.
	$u = getCpuUsage();
	if ($u['idle'] < IDLEMIN)
	{
		echo "The server is too busy...<br/>We most likely got linked by a larger site, and the load is strenuous.<br/>Try again in a few minutes.";
		exit();
	}

	// Look for gzip encoding. If it doesn't exist we spit out a few
	// digits.
	if (false === strpos($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip"))
	{
		// echo out the truncated file
		echo(readfile(TRUNCFILE));
		exit();
	}

	// Ok, this is the actual pi script. Pretty simple!
	// Spit out a gzip header.
	header("Content-Encoding: gzip");
	
	ob_end_flush();

	// Decide what file we're displaying
	$pi = (date("nj") == PIDAY) ? PI_DAY : PI_NORM;

	// Open the file
	$f = fopen($pi, "r");

	// Dump it to the browser 1k at a time
	while (!feof($f))
	{
		$o = fread($f, ONE_K);
		echo $o;
		flush();
		ob_flush();
	}

//	exit();
?>
