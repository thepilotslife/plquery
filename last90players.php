<?php

require('db.php');

$s = $db->prepare('select date_format(a.td,"%m-%d") as tday, max(a.c) as p, avg(a.c) as av from (select date(from_unixtime(t.t)) as td, count(t.t) as c from t where date(from_unixtime(t.t)) != DATE(NOW()) and date(from_unixtime(t.t)) >= DATE(NOW() - INTERVAL 91 DAY) group by t.t) as a group by tday');

$peakvalues = array();
$avgvalues = array();
$s->execute();
while (($r = $s->fetch()) !== false) {
	$peakvalues[$r->tday] = $r->p;
	$avgvalues[$r->tday] = $r->av;
}
$values = array($peakvalues, $avgvalues);

require_once('SVGGraph/SVGGraph.php');
 
$settings = array(
  'back_colour'       => '#eee',    'stroke_colour'      => '#000',
  'back_stroke_width' => 0,         'back_stroke_colour' => '#eee',
  'axis_colour'       => '#333',    'axis_overlap'       => 2,
  'axis_font'         => 'Tahoma',  'axis_font_size'     => 10,
  'grid_colour'       => '#666',    'label_colour'       => '#000',
  'pad_right'         => 10,        'pad_left'           => 10,
  'fill_under'        => array(true, true),
  'marker_size'       => 2,
  'marker_type'       => array('cross', 'triangle'),
  'marker_colour'     => array('black', 'black'),
  'graph_title' => 'player count last 90d (players)',
  'graph_title_position' => 'top',
  'graph_title_font_weight' => 'bold',
  'label_x' => $t,
  'legend_entries' => array('Peak', 'Average'),
  'legend_position' => 'top left 4 4',
  'legend_back_colour' => 'rgba(204,204,204,0.6)',
  'legend_colour' => '#800',
  'legend_round' => 5,
  'grid_division_h' => 5,
  'grid_show_subdivisions' => true,
);
 
$colours = array(
	array('rgba(0,0,255,0.7)','rgba(255,255,255,0.7)'),
	array('rgba(255,0,0,0.7)','rgba(255,255,0,0.7)'),
);
//$colours = array(array('blue', 'white'));
 
$graph = new SVGGraph(720, 300, $settings);
$graph->colours = $colours;
 
$graph->Values($values);
$graph->Render('MultiLineGraph');

fin('last90players.svg');
