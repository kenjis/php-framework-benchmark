<?php
/**
 * @copyright (c) 2013, Kenji Suzuki, Kenichi Ando, Naoaki Yamada, Yoshiyuki Yamamoto, Yuta Sakurai, Hitoshi Asano
 * @license https://github.com/php-recipe/php-recipe-2nd/blob/master/LICENSE BSD-3-Clause
 * @link https://github.com/php-recipe/php-recipe-2nd/blob/master/htdocs/php-recipe/04/06/make_chart_parts.php
 */

# グラフを描画するJavaScriptの関数とグラフを表示させる<div>タグを
# 生成するユーザー定義関数を定義します。
function makeChartParts($data, $options, $type)
{
  // JavaScriptの関数名、<div>タブのIDが
  // 重複しないようにするための連番☆レシピ037☆（静的変数とは？）
  static $index = 1;

  // グラフの種類からAPIロード時の「packages」を確定し、APIロードを生成
  $package = 'corechart';
  $special_type = array('GeoChart', 'AnnotatedTimeLine','TreeMap', 'OrgChart',
                        'Gauge', 'Table', 'TimeLine', 'GeoMap', 'MotionChart');
  if (in_array($type, $special_type)) {
    $package = strtolower($type);
  }
  $load = 'google.load("visualization", "1", {packages:["' . $package . '"]});';

  // データとオプションをJSON形式へ
  $jsData = json_encode($data);
  $jsonOptions = json_encode($options);

  // グラフを描画するJavaScript関数を生成
  $chart = <<<CHART_FUNC
    {$load}
    google.setOnLoadCallback(drawChart{$index});
    function drawChart{$index}() {
      var data = {$jsData};
      var chartData = new google.visualization.arrayToDataTable(data);
      var options = {$jsonOptions};
      var chartDiv = document.getElementById('chart{$index}');
      var chart = new google.visualization.{$type}(chartDiv);
      chart.draw(chartData, options);
    }\n
CHART_FUNC;

  // グラフを表示する<div>タグを生成
  $div = '<div id="chart' . $index . '"></div>';

  $index++;  // 連番を1加算しておく
  return array($chart, $div);
}
/* ?>終了タグ省略 ☆レシピ001☆（サーバーのPHP情報を知りたい） */
