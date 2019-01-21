<?php
// Stolen CPU Functions
// I don't remember where I got these from, so I am not putting them in
// the pi displaying script. They were freely available on the internet,
// but I do not know what license they were under!
// 	- Mike 2/24/2009
// NOTE: Only works on LINUX!!!
function getStat()
{
    $_statPath = '/proc/stat';

    $stat = file($_statPath);

    $parts = explode(" ", preg_replace("!cpu +!", "", $stat[0]));

    $return = array();
    $return['user'] = $parts[0];
    $return['nice'] = $parts[1];
    $return['system'] = $parts[2];
    $return['idle'] = $parts[3];
    return $return;
}

function getCpuUsage()
{
    $time1 = getStat();
    usleep(100000);
    $time2 = getStat();

    $delta = array();

    foreach ($time1 as $k=>$v)
    {
        $delta[$k] = $time2[$k] - $v;
    }

    $deltaTotal = array_sum($delta);

    $percentages = array();

    foreach ($delta as $k=>$v)
    {
        $percentages[$k] = round($v / $deltaTotal * 100, 2);
    }
    return $percentages;
}

?>
