<?php
// Core functions for IECMHC Reports

// Parse report files for content
function iecmhc_get_report_data() {
  $reports = [];
  $report_files = glob( plugin_dir_path( __FILE__ ) . '../reports/*.php' );

  foreach ( $report_files as $file ) {
    $contents = file_get_contents( $file );
    
    // Match everything between /** and */ in the comment block
    if ( preg_match( '/\/\*\*(.*?)\*\//s', $contents, $comment_block ) ) {
      $report_data = [];
      
      // Process each line in the comment block
      $lines = explode( "\n", $comment_block[1] );
      foreach ( $lines as $line ) {
        // Match lines in the format "Label: Value"
        if ( preg_match( '/\s*\*\s*(.+?):\s*(.+)/', $line, $matches ) ) {
          $label = trim( $matches[1] );
          $value = trim( $matches[2] );
          $report_data[ $label ] = $value;
        }
      }

      // Use 'Report Type' and 'Report Name' as keys to group and access reports
      if ( isset( $report_data['Report Type'] ) && isset( $report_data['Report Name'] ) ) {
        $report_type = $report_data['Report Type'];
        $report_name = $report_data['Report Name'];

        // Organize reports by Report Type and store other metadata under each report name
        if ( ! isset( $reports[ $report_type ] ) ) {
          $reports[ $report_type ] = [];
        }
        $reports[ $report_type ][ $report_name ] = $report_data;
      }
    }
  }

  return $reports;
}

// Shortcode for displaying the report form
function iecmhc_reports_shortcode() {
  $reports = iecmhc_get_report_data();

  // Initialize selected values
  $selected_report_type = isset($_POST['report_type']) ? sanitize_text_field($_POST['report_type']) : '';
  $selected_report_name = isset($_POST['report_name']) ? sanitize_text_field($_POST['report_name']) : '';
  $selected_report_scope = isset($_POST['report_scope']) ? sanitize_text_field($_POST['report_scope']) : '';

  ob_start();
  ?>

  <?php 
    $user_id = get_current_user_id();
    $consultant_id = get_field('consultant_number', 'user_'.$user_id);
    $countyprogram_id = get_field('consultant_countprogram', 'user_'.$user_id);
  
    echo 'User ID: '.$user_id;
    echo '<br>Consultant ID: '.$consultant_id;
    echo '<br>County/Program ID: '.$countyprogram_id;
  ?>

  <form id="iecmhc-reports-form" class="p-4 border rounded bg-light" method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="mb-3">
      <label for="report-type" class="form-label">Report Type</label>
      <select id="report-type" name="report_type" class="form-select form-control" required>
        <option value="" selected disabled>Select Report Type</option>
        <?php foreach ( $reports as $report_type => $report_names ) : ?>
          <option value="<?php echo esc_attr( $report_type ); ?>"><?php echo esc_html( $report_type ); ?></option>
        <?php endforeach; ?>
      </select>
      <div id="report-type-feedback" class="invalid-feedback">Please select a report type.</div>
    </div>

    <div class="mb-3">
      <label for="report-name" class="form-label">Report Name</label>
      <select id="report-name" name="report_name" class="form-select form-control" required disabled>
        <option value="" selected disabled>Select Report Name</option>
      </select>
      <div id="report-name-feedback" class="invalid-feedback">Please select a report name.</div>
    </div>

    <div class="mb-3" id="scope-field" style="display: none;">
      <label for="report-scope" class="form-label">Report Scope</label>
      <select id="report-scope" name="report_scope" class="form-select form-control">
      </select>
      <div id="report-scope-feedback" class="invalid-feedback">Please select a report scope.</div>
    </div>

    <div class="mb-3" id="county-id-field" style="display: none;">
      <label for="county-id" class="form-label">County/Program ID</label>
      <input type="text" id="county-id" name="county_id" class="form-control" pattern="\d{3}"
            title="Please enter a 3-digit ID (including leading zeros if necessary)" 
            placeholder="###">
      <div class="invalid-feedback">Please enter a 3-digit County/Program ID.</div>
    </div>

    <div class="mb-3" id="consultant-id-field" style="display: none;">
      <label for="consultant-id" class="form-label">Consultant ID</label>
      <input type="text" id="consultant-id" name="consultant_id" class="form-control" pattern="\d{3}"
            title="Please enter a 3-digit ID (including leading zeros if necessary)" 
            placeholder="###">
      <div class="invalid-feedback">Please enter a 3-digit Consultant ID.</div>
    </div>

    <button type="submit" class="btn btn-primary">Submit <i class="fa fa-check-circle" aria-hidden="true"></i></button>
    <button type="button" class="btn btn-danger" onclick="clearForm()">Clear <i class="fa fa-times-circle" aria-hidden="true"></i></button>

  </form>

  <script>
    function clearForm() {
      // Clear all the select fields
      document.getElementById('report-type').selectedIndex = 0; // Reset Report Type
      document.getElementById('report-name').innerHTML = '<option value="" selected disabled>Select Report Name</option>'; // Reset Report Name
      document.getElementById('report-name').disabled = true; // Disable Report Name
      document.getElementById('report-scope').innerHTML = '<option value="">Select Scope</option>'; // Reset Scope
      document.getElementById('scope-field').style.display = 'none'; // Hide Scope field
      document.getElementById('county-id').value = '';
      document.getElementById('consultant-id').value = '';

      // Refresh the page with a GET request to clear $_GET data
      window.location.href = '<?php echo esc_url($_SERVER['REQUEST_URI']); ?>';
    }
  </script>

  <div id="report-output" class="mt-4">
    <?php
      if ( isset($_POST['report_type']) && isset($_POST['report_name']) ) {
        // Handle the report display
        iecmhc_display_report($_POST['report_type'], $_POST['report_name']);
      }
    ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const reports = <?php echo wp_json_encode( $reports ); ?>;
      const reportTypeSelect = document.getElementById('report-type');
      const reportNameSelect = document.getElementById('report-name');
      const scopeField = document.getElementById('scope-field');
      const reportScopeSelect = document.getElementById('report-scope');
      const countyIdField = document.getElementById('county-id-field');
      const consultantIdField = document.getElementById('consultant-id-field');

      // Populate the report name select based on selected report type
      reportTypeSelect.addEventListener('change', function() {
        const selectedCaseType = reportTypeSelect.value;

        // Reset Report Name and Scope fields
        reportNameSelect.innerHTML = '<option value="">Select Report Name</option>';
        reportNameSelect.disabled = true;
        scopeField.style.display = 'none';
        countyIdField.style.display = 'none';
        consultantIdField.style.display = 'none';

        if (selectedCaseType && reports[selectedCaseType]) {
          Object.keys(reports[selectedCaseType]).forEach(reportName => {
            const option = document.createElement('option');
            option.value = reportName;
            option.textContent = reportName;
            reportNameSelect.appendChild(option);
          });
          reportNameSelect.disabled = false;

          if ('<?php echo esc_js($selected_report_name); ?>') {
            reportNameSelect.value = '<?php echo esc_js($selected_report_name); ?>';
            reportNameSelect.dispatchEvent(new Event('change')); // Trigger change to populate scope
          }
        }
      });

      // Populate the scope field based on selected report name
      reportNameSelect.addEventListener('change', function() {
        const selectedCaseType = reportTypeSelect.value;
        const selectedReportName = reportNameSelect.value;

        // Clear the Scope field first
        reportScopeSelect.innerHTML = '';

        // Set "Statewide" as the default, but only if it's not already present
        const defaultOption = document.createElement('option');
        defaultOption.value = "Statewide";
        defaultOption.textContent = "Statewide";
        reportScopeSelect.appendChild(defaultOption); // Add default option

        if (selectedCaseType && selectedReportName && reports[selectedCaseType][selectedReportName]) {
          const reportData = reports[selectedCaseType][selectedReportName];

          // Check if 'Scope' exists and handle accordingly
          if (reportData['Scope']) {
            const scopes = Array.from(new Set(reportData['Scope'].split(',').map(scope => scope.trim()))); // Ensure unique scopes
            scopes.forEach(scope => {
              if (scope !== "Statewide") { // Avoid duplicate "Statewide"
                const option = document.createElement('option');
                option.value = scope;
                option.textContent = scope;
                reportScopeSelect.appendChild(option);
              }
            });
            scopeField.style.display = 'block'; // Show the scope field if there are options
          } else {
            scopeField.style.display = 'none'; // Hide the scope field if no scope exists
          }
        }

        // Select the previously submitted report scope if available
        if ('<?php echo esc_js($selected_report_scope); ?>') {
          reportScopeSelect.value = '<?php echo esc_js($selected_report_scope); ?>';
        }
      });

      // Show fields conditionally based on selected scope
      reportScopeSelect.addEventListener('change', function() {
        const selectedScope = reportScopeSelect.value;
  
        // Get the County/Program ID and Consultant ID fields
        const countyIdField = document.getElementById('county-id-field');
        const consultantIdField = document.getElementById('consultant-id-field');
        const countyIdLabel = document.querySelector('label[for="county-id"]'); // The label for County/Program ID
        
        // Reset visibility
        countyIdField.style.display = 'none';
        consultantIdField.style.display = 'none';

        if (selectedScope === 'County') {
          countyIdLabel.textContent = 'County ID';  // Update label to "County ID"
          countyIdField.style.display = 'block';
        } else if (selectedScope === 'Program') {
          countyIdLabel.textContent = 'Program ID';  // Update label to "Program ID"
          countyIdField.style.display = 'block';
        } else if (selectedScope === 'Consultant') {
          consultantIdField.style.display = 'block';
        }
      });

      // Initialize form with previously submitted values
      if ('<?php echo esc_js($selected_report_type); ?>') {
        reportTypeSelect.value = '<?php echo esc_js($selected_report_type); ?>';
        reportTypeSelect.dispatchEvent(new Event('change'));
      }
    });
  </script>
  <?php
  return ob_get_clean();
}
add_shortcode('iecmhc_reports_form', 'iecmhc_reports_shortcode');

// Function to include the report file based on POST data
function iecmhc_display_report($report_type, $report_name) {
  // Sanitize the inputs
  $report_type = sanitize_text_field($report_type);
  $report_name = sanitize_text_field($report_name);

  // Construct the file path for the selected report
  $report_file = plugin_dir_path(__DIR__) . 'reports/' . sanitize_title($report_type) . '-' . sanitize_title($report_name) . '.php';

  // Check if the report file exists and include it
  if (file_exists($report_file)) {
    include $report_file; // This will display the report contents
  } else {
    echo '<p>Error: Report file not found. file: '.$report_file.'</p>';
  }
}

// Enqueue the necessary scripts for reports
function iecmhc_enqueue_scripts() {
  // Register your script
  wp_register_script('amcharts', 'https://cdn.amcharts.com/lib/5/index.js', [], null, true);
  wp_register_script('amcharts_percent', 'https://cdn.amcharts.com/lib/5/percent.js', ['amcharts'], null, true);
  wp_register_script('amcharts_animated', 'https://cdn.amcharts.com/lib/5/themes/Animated.js', ['amcharts_percent'], null, true);

  // Enqueue your scripts
  wp_enqueue_script('amcharts');
  wp_enqueue_script('amcharts_percent');
  wp_enqueue_script('amcharts_animated');
}

// Hook into wp_enqueue_scripts to load your scripts
add_action('wp_enqueue_scripts', 'iecmhc_enqueue_scripts');