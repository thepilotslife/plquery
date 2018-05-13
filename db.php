<?php

error_reporting(0);

date_default_timezone_set('UTC');
$t = 'generated on ' . date('j-M-Y H:i e');

$db = new PDO('mysql:host=127.0.0.1; dbname=plquery', 'plquery', file_get_contents('.dbpw'));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$s = $db->prepare('SET NAMES utf8');
$s->execute();

ob_start();

function fin($name) {
	$d = ob_get_flush();
	$d = preg_replace('@<script.*</script>@is', '', $d);
	file_put_contents($name, $d);
	exit();
}
