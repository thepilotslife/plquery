<?php

require('db.php');

$s = $db->prepare('select p.n, count(p.i)*5/60 as time from t join p on t.i = p.i 
where t > unix_timestamp() - 86400*7 group by t.i order by time desc limit 20');
$s->execute();
$d = $s->fetchAll();

$values = array();

foreach ($d as $p) {
	$values[$p->n] = $p->time;
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
  'marker_size'       => 3,
  'marker_type'       => array('circle', 'square'),
  'marker_colour'     => array('blue', 'red'),
  'graph_title' => 'most active players last 7d (h)',
  'graph_title_position' => 'top',
  'graph_title_font_weight' => 'bold',
  'label_y' => $t,
  'show_data_labels' => true,
  'data_label_type' => 'plain',
  'data_label_padding' => 1,
  'data_label_space' => 1,
  'data_label_back_colour' => '#ccc',
  'data_label_position' => 'centre',
  'data_label_outline_thickness' => 1,
  'data_label_font_weight' => 'bold',
  'grid_division_h' => 5,
);
 
$colours = array(array('red', 'yellow'));
//$colours = array(array('blue', 'white'));
 
$graph = new SVGGraph(360, 450, $settings);
$graph->colours = $colours;
 
$graph->Values($values);
$graph->Render('HorizontalBarGraph');

fin('last7playtime.svg');
