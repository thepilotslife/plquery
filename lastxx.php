<?php

$peakvalues = array();
$avgvalues = array();
$lastday = '';
$datecount = 0;
$s->execute();
while (($r = $s->fetch()) !== false) {
	$peakvalues[$r->tday] = $r->p;
	$avgvalues[$r->tday] = $r->av;
	$datecount++;
	$lastday = $r->tday;
}
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

	while (count($misseddates) > 0) {
		$__t = array_pop($misseddates);
		$peakvalues[$__t] = 0;
		$avgvalues[$__t] = 0;
	}
}
$values = array($peakvalues, $avgvalues);
