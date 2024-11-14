<?php

/**
 * 
 *  Report Type: Programmatic
 *  Report Name: Care Provider Races
 *  Scope: Consultant, Program, Statewide
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

<h2>Programmatic - Care Provider Races - <?php echo esc_html($report_scope); ?></h2>
<div class="report-chart"></div>

<?php //echo do_shortcode("[case_data_chart xtitle='Care Provider Races' case_type='programmatic' data_set='provider_races' 3d='true' height='500px' filter_type='".$report_scope_slug."']"); ?>

<?php
// Capture buffered content in $test_content
$report_content = ob_get_clean();

// Output or manipulate $test_content as needed
echo $report_content;
