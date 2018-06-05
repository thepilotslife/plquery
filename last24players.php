<?php

set_time_limit(3);

require('db.php');

$s = $db->prepare('select t.t, count(t.i) as players from t where t > unix_timestamp() - 86400 group by t.t order by t.t asc');
$s->execute();
$d = $s->fetchAll();

$values = array();

$errcount = 0;
$prevplayers = 0;
$min = 0;
$prev = 0;
// if the first count is 0, then the start time has no data
foreach ($d as $p) {
	if ($min == 0) {
		$min = $p->t;
	}
	if ($prev != 0) {
		$dif = round(($p->t - $prev) / 300);
		for ($i = 0; $i < $dif; $i++) {
			$t_ = date('H:i', $prev + ($i+1)*300);
			$values[$t_] = 0;
			if ($dif == 2 && $prevplayers > 1 && $p->players > 1) {
				$errcount++;
				$values[$t_] = round(($p->players + $prevplayers) / 2);
			}
		}
	}
	$values[date('H:i', $p->t)] = $p->players;
	$prevplayers = $p->players;
	$prev = $p->t;
}

if (count($values) == 0) {
	$_t = time();
	$errcount = 288;
	for ($i = 288; $i > 0; $i--) {
		$values[date('H:i', $_t)] = 0;
		$_t -= 300;
	}
}

$_t = $prev;
while (count($values) < 288) {
	$t += 300;
	if ($t > time()) {
		break;
	}
	$values[date('H:i', $_t)] = 0;
	$errcount++;
}

$_t = $min;
while (count($values) < 288) {
	$t -= 300;
	$values[date('H:i', $_t)] = 0;
	$errcount++;
}

require_once('SVGGraph/SVGGraph.php');
 
$settings = array(
  'back_colour'       => '#eee',    'stroke_colour'      => '#000',
  'back_stroke_width' => 0,         'back_stroke_colour' => '#eee',
  'axis_colour'       => '#333',    'axis_overlap'       => 2,
  'axis_font'         => 'Tahoma',  'axis_font_size'     => 10,
  'grid_colour'       => '#666',    'label_colour'       => '#000',
  'pad_right'         => 10,        'pad_left'           => 10,
  'fill_under'        => array(true, false),
  'marker_size'       => 0,
  'marker_type'       => array('circle', 'square'),
  'marker_colour'     => array('blue', 'red'),
  'graph_title' => 'player count last 24h (players) (no reponse for ~ ' . $errcount . ' queries)',
  'graph_title_position' => 'top',
  'graph_title_font_weight' => 'bold',
  'label_x' => $t,
  'axis_text_angle_h' => -90,
  'grid_division_h' => 6,
);

if ($errcount == 288) {
  $settings['axis_max_v'] = 1;
}
 
$colours = array('rgba(255,255,0,0.5)');
//$colours = array(array('blue', 'white'));
 
$graph = new SVGGraph(720, 250, $settings);
$graph->colours = $colours;
 
$graph->Values($values);
$graph->Render('LineGraph');

fin('last24players.svg');
