<?php

/**
 * 
 *  Report Type: Programmatic
 *  Report Name: Care Provider Races
 *  Scope: Consultant, Program, Statewide
 *  Requirement: gcharts
 *  Description: Pie chart displaying number/percentage of provider races
 *  Version: 1.0
 * 
**/

// Retrieve the report scope from $_POST
$report_scope = isset($_POST['report_scope']) ? sanitize_text_field($_POST['report_scope']) : '';
if($report_scope == 'Consultant') {
  $report_scope_slug = 'consultant';
}elseif($report_scope == 'County') {
  $report_scope_slug = 'countyprogram';
}elseif($report_scope == 'Program') {
  $report_scope_slug = 'countyprogram';
}else{
  $report_scope_slug = 'statewide';
}
// Start output buffering
ob_start();
?>

<style>
#chartdiv {
  display: none;
  width: 100%;
  height: 500px;
}
</style>

<!-- HTML -->
<h2>Programmatic - Care Provider Races - <?php echo esc_html($report_scope); ?></h2>

<?php echo do_shortcode("[case_data_chart xtitle='Care Provider Races' case_type='programmatic' data_set='provider_races' 3d='true' height='500px' filter_type='".$report_scope_slug."']"); ?>

<div id="chartdiv"></div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  // Ensure amCharts is loaded and ready
  if (typeof am5 !== 'undefined') {
    console.log("Initializing chart...");

    // Create root element
    var root = am5.Root.new("chartdiv");

    // Set themes
    root.setThemes([
      am5themes_Animated.new(root)
    ]);

    // Create chart
    var chart = root.container.children.push(am5percent.PieChart.new(root, {
      layout: root.verticalLayout
    }));

    // Create series
    var series = chart.series.push(am5percent.PieSeries.new(root, {
      valueField: "value",
      categoryField: "category"
    }));

    // Set data
    series.data.setAll([
      { value: 10, category: "One" },
      { value: 9, category: "Two" },
      { value: 6, category: "Three" },
      { value: 5, category: "Four" },
      { value: 4, category: "Five" },
      { value: 3, category: "Six" },
      { value: 1, category: "Seven" },
    ]);

    // Play initial series animation
    series.appear(1000, 100);
  } else {
    console.error("amCharts library is not loaded.");
  }
});
</script>

<?php
// Capture buffered content in $test_content
$test_content = ob_get_clean();

// Output or manipulate $test_content as needed
echo $test_content;