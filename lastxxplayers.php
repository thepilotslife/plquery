<?php

require('db.php');

$s = $db->prepare('select date_format(date(from_unixtime(a.t)),"%y-%m-%d") as tday, max(a.c) as p, avg(a.c) as av from 
(select t.t, count(t.t) as c from t group by t.t) as a group by tday');

$peakvalues = array();
$avgvalues = array();
$s->execute();
$datecount = 0;
while (($r = $s->fetch()) !== false) {
	$peakvalues[$r->tday] = $r->p;
	$avgvalues[$r->tday] = $r->av;
	$datecount++;
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
  'marker_size'       => 0,
  'graph_title' => 'player count since stats start (players)',
  'graph_title_position' => 'top',
  'graph_title_font_weight' => 'bold',
  'label_x' => $t,
  'grid_division_h' => $datecount / 8,
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

fin('lastxxplayers.svg');
