<?php

set_time_limit(3);

require('db.php');

$minday = 'DATE(from_unixtime(t.t)) >= DATE(curdate() - INTERVAL 91 DAY)';
$q = $db->query('select min(t.t) as m from t where ' . $minday);
$firstdatapoint = $q->fetch()->m;
$firstdatapoint += 3600*24; // for some reason..
$q->closeCursor();
$s = $db->prepare('select date_format(a.td,"%d %b") as tday, max(a.c) as p, sum(a.c) as sm from 
(select date(from_unixtime(t.t)) as td, count(t.t) as c from t 
where date(from_unixtime(t.t)) != curdate() and ' . $minday . ' group by t.t) as a 
group by tday order by a.td asc');

$dformat = 'd M';
include('lastxx.php');

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
