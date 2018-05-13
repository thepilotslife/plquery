<?php

require('db.php');

$s = $db->prepare('select date_format(date(from_unixtime(t.t)),"%a") as dow, count(t.t) tot from t group by dow');

$total = 0;
$dow = array();
$s->execute();
while (($r = $s->fetch()) !== false) {
	$total += $r->tot;
	$dow[$r->dow] = $r->tot;
}
$data = array(
	'Mon' => $dow['Mon'],
	'Tue' => $dow['Tue'],
	'Wed' => $dow['Wed'],
	'Thu' => $dow['Thu'],
	'Fri' => $dow['Fri'],
	'Sat' => $dow['Sat'],
	'Sun' => $dow['Sun'],
);

require_once('SVGGraph/SVGGraph.php');
 
$settings = array(
  'back_colour'       => '#eee',    'stroke_colour'      => '#000',
  'back_stroke_width' => 0,         'back_stroke_colour' => '#eee',
  'axis_colour'       => '#333',    'axis_overlap'       => 2,
  'axis_font'         => 'Tahoma',  'axis_font_size'     => 10,
  'grid_colour'       => '#666',    'label_colour'       => '#000',
  'pad_right'         => 10,        'pad_left'           => 10,
  'fill_under'        => array(true, true),
  'show_labels'	      => true,
  'show_label_amount' => true,
  'graph_title' => 'activity by day (%)',
  'graph_title_position' => 'top',
  'graph_title_font_weight' => 'bold',
);
 
$colours = array('#ccf','#699','#93c','#996','#f39','#0f3','#99c');
 
$graph = new SVGGraph(300, 300, $settings);
$graph->colours = $colours;
 
$graph->Values($data);
$graph->Render('BarGraph');

fin('days.svg');
