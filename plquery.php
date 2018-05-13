<?php

$db = new PDO('mysql:host=127.0.0.1; dbname=plquery', 'plquery', file_get_contents('.dbpw'));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$stmt = $db->prepare('SET NAMES utf8');
$stmt->execute();

$ip = "142.44.161.46";
$port = 7777;
 
$ipparts = explode('.', $ip);
 
$pack = "SAMP";
 
$pack .= chr($ipparts[0]);
$pack .= chr($ipparts[1]);
$pack .= chr($ipparts[2]);
$pack .= chr($ipparts[3]);
 
$pack .= chr($port & 0xFF);
$pack .= chr($port >> 8 & 0xFF);
 
$pack .= 'c';
 
$sock = @fsockopen('udp://'.$ip, $port, $errno, $errstr, 15);
if ($sock === false) {
	//die('down\n');
	exit();
}
if (@fwrite($sock, $pack) === false) {
	//die('down\n');
	exit();
}
$res = @fread($sock, 2048);
if ($res === false) {
	//die('down\n');
	exit();
}
$res = str_split($res);
@fclose($sock);

if (count($res) < 12) {
	//echo(count($res) . ' ');
	//die('wtf\n');
	exit();
}

$stmtgetid = $db->prepare('SELECT i FROM p WHERE n=?');
$stmtaddplayer = $db->prepare('INSERT INTO p(n) VALUES(?)');
$stmtinserttime = $db->prepare('INSERT INTO t(i,t,s) VALUES(?,?,?)');

$time = time();

$pcount = (ord($res[11])) | (ord($res[12]) << 8);
$svgnumplayers = $pcount;
$svgpline1 = '';
$svgpline2 = '';
$i = 13;
for (;$pcount > 0; $pcount--) {
	$len = ord($res[$i++]);
	$name = '';
	for (; $len > 0; $len--, $i++) {
		$name .= $res[$i];
	}

	$score  = ord($res[$i++]) << 0;
	$score |= ord($res[$i++]) << 8;
	$score |= ord($res[$i++]) << 16;
	$score |= ord($res[$i++]) << 24;

	$svgp = "{$name}({$score}), ";
	$svgpline1a = $svgpline1;
	$svgpline1a .= $svgp;
	if (strlen($svgpline1a) < 125) {
		$svgpline1 = $svgpline1a;
	} else {
		$svgpline2 .= $svgp;
	}

	$stmtgetid->bindValue(1, $name);
	$stmtgetid->execute();
	$data = $stmtgetid->fetchAll();
	$id = 0;
	if (count($data) == 0) {
		$stmtaddplayer->bindValue(1, $name);
		$stmtaddplayer->execute();
		$id = $db->lastInsertId();
	} else {
		$id = $data[0]->i;
	}

	$stmtinserttime->bindValue(1, $id);
	$stmtinserttime->bindValue(2, $time);
	$stmtinserttime->bindValue(3, $score);
	$stmtinserttime->execute();
}

if (strlen($svgpline1) == 0) {
	$svgpline1 = 'Nobody online :(';
} else {
	$svgpline1 = substr($svgpline1, 0, -2);
}

if (strlen($svgpline2) > 0) {
	$svgpline2 = substr($svgpline2, 0, -2);
}

$formattedtime = date('j M H:i O', $time);

$d = <<<D
<svg id="s" width="700" height="3em" viewbox="0 0 700 50" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
	<style>text{font-size:0.7em}</style>
	<text x="2" y="1em">The Pilot's Life: {$svgnumplayers}/100 online</text>
	<text x="698" y="1em" text-anchor="end" id="t">Last update: {$formattedtime}</text>
	<text x="2" y="2.2em" text-anchor="left">{$svgpline1}</text>
	<text x="2" y="3.4em" text-anchor="left">{$svgpline2}</text>
</svg>
D;

file_put_contents('signature.svg', $d);
