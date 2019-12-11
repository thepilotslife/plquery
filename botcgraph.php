<?php
require_once('SVGGraph/SVGGraph.php');
error_reporting(E_ALL);
ob_start();

$companies = array();

$order = array();
$lines = explode("\n",file_get_contents('botc.txt'));
if (count($lines) == 0) {
  exit();
}
array_pop($lines); // ends with LF

foreach (explode(',',explode(';',$lines[0])[1]) as $d) {
  $d = explode(':',$d);
  if (count($d) == 2) {
    $companies[] = $d[0];
    $order[$d[0]] = count($order);
  }
}

$cols = array();
for ($i = 0; $i < count($order); $i++) {
  $cols[] = 'hsl('.(255*$i/count($order)).',100%,50%)';
}

$min = explode(';',$lines[0])[0];
$max = 0;
$times = array();

$vals = array();
foreach ($lines as $l) {
  $xx = explode(';',$l);
  // stop at newyear
  if ($xx[0] > 1577836800000) {
    break;
  }
  $times[] = $max = $xx[0];
  foreach (explode(',',$xx[1]) as $d) {
    $d = explode(':',$d);
    if (count($d) == 2) {
      $vals[$order[$d[0]]][] = $d[1];
    }
  }
}

function lerp($a, $b, $x) { return round($a + ($b - $a) * $x); }
function prel($a, $b, $x) { return ($x - $a) / ($b - $a); }

$div = count($vals[0]);
$values = array();
foreach ($vals as $k => $_vals) {
  $values[$k] = array();
  $idx = 0;
  for ($i = 0; $i < 100; $i++) {
    if ($i == 99) {
      $v[] = $_vals[count($_vals)-1];
    } else {
      $t = $min + ($max - $min) * $i / 100;
      while ($times[$idx + 1] < $t) {
        $idx++;
      }
      $values[$k][] = lerp($_vals[$idx], $_vals[$idx + 1], prel($times[$idx], $times[$idx + 1], $t));
    }
  }
  $div = count($values[$k]);
}

$settings = array(
  'back_colour'       => '#eee',    'stroke_colour'      => '#000',
  'back_stroke_width' => 0,         'back_stroke_colour' => '#eee',
  'axis_colour'       => '#333',    'axis_overlap'       => 2,
  'axis_font'         => 'Tahoma',  'axis_font_size'     => 10,
  'grid_colour'       => '#666',    'label_colour'       => '#000',
  'pad_right'         => 10,        'pad_left'           => 10,
  'fill_under'        => true,      'fill_opacity'       => .2,
  'marker_size'       => 3,
  'line_stroke_width' => 1,
  'graph_title' => 'company hauls during BOTC',
  'graph_title_position' => 'top',
  'graph_title_font_weight' => 'bold',
  'marker_type' => array('circle', 'square', 'cross', 'triangle', 'x', 'star', 'threestar', 'fourstar', 'diamond'),
  'legend_entries' => $companies,
  'legend_position' => 'top left 4 4',
  'legend_back_colour' => 'rgba(204,204,204,0.6)',
  'legend_colour' => '#800',
  'label_x' => 'Battle of the Companies (dec11 - dec31 2019), updated '.date('j M H:i O', time()),
  //'legend_padding' => 0,
  //'legend_entry_height' => 15,
  //'grid_division_h' => ($max - $min) / 10,
  //'grid_division_h' => ($max - $min),
  //'grid_show_subdivisions' => true,
  'grid_division_h' => $div - 1, // why -1? no idea
  'show_axis_text_h' => false,
);
$graph = new SVGGraph(1280, 720, $settings);
$graph->colours = $cols;
$graph->Values($values);
$graph->Render('MultiLineGraph');
$d = ob_get_flush();
$d = preg_replace('@<script.*</script>@is', '', $d);
file_put_contents('botc.svg', $d);
