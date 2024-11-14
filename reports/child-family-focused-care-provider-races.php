<?php

/**
 * 
 *  Report Type: Child/Family Focused
 *  Report Name: Care Provider Races
 *  Scope: Consultant, County, Statewide
 *  Requirement: gcharts
 *  Description: Pie chart displaying number/percentage of provider races
 *  Version: 1.0
 * 
**/

// Retrieve the report scope from $_POST
$report_scope = isset($_POST['report_scope']) ? sanitize_text_field($_POST['report_scope']) : '';

// Start output buffering
ob_start();
?>

<h2>Child/Family Focused - Care Provider Races - <?php echo esc_html( $report_scope ); ?></h2>
<p>Aenean lacinia bibendum nulla sed consectetur. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas faucibus mollis interdum. Donec ullamcorper nulla non metus auctor fringilla.</p>
<p>Cras mattis consectetur purus sit amet fermentum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec id elit non mi porta gravida at eget metus. Donec ullamcorper nulla non metus auctor fringilla. Sed posuere consectetur est at lobortis. Sed posuere consectetur est at lobortis.</p>
<p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec ullamcorper nulla non metus auctor fringilla. Etiam porta sem malesuada magna mollis euismod. Donec ullamcorper nulla non metus auctor fringilla. Maecenas sed diam eget risus varius blandit sit amet non magna. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>

<?php
// Capture buffered content in $test_content
$test_content = ob_get_clean();

// Output or manipulate $test_content as needed
echo $test_content;

