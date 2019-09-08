<?php

$peakvalues = array();
$avgvalues = array();
$lastday = '';
$firstday = '';
$datecount = 0;
$s->execute();
while (($r = $s->fetch()) !== false) {
	if ($firstday == '') {
		$firstday = $r->p;
	}
	$peakvalues[$r->tday] = $r->p;
	$avgvalues[$r->tday] = $r->sm / 288; // (check every 5m)
	$datecount++;
	$lastday = $r->tday;
}
/*
if ($lastday != '') {
	$_t = time();
	// this may not work well when db/php use different language to format
	// dates...
	$safeguard = 30;
	$misseddates = array();
	do {
		$_t -= 3600 * 24;
		$__t = date($dformat, $_t);
		if ($__t == $lastday) {
			break;
		}
		$misseddates[] = $__t;
	} while($safeguard-- > 0);

	$datecount += count($misseddates);
	while (count($misseddates) > 0) {
		$__t = array_pop($misseddates);
		$peakvalues[$__t] = 0;
		$avgvalues[$__t] = 0;
	}
}
*/

$datecount = 0;
$_start = $firstdatapoint;
$_today = time();
$_peakvalues = array();
$_avgvalues = array();
lastxx_l:
$datecount++;
$_t = date($dformat, $_start);
if (array_key_exists($_t, $peakvalues)) {
	$_peakvalues[$_t] = $peakvalues[$_t];
	$_avgvalues[$_t] = $avgvalues[$_t];
} else {
	$_peakvalues[$_t] = 0;
	$_avgvalues[$_t] = 0;
}
$_start += 3600*24;
if ($_start < $_today) {
	goto lastxx_l;
}
$values = array($_peakvalues, $_avgvalues);
