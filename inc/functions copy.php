<?php
// Core functions for IECMHC Case Reports

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

      // Use 'Case Type' and 'Report Name' as keys to group and access reports
      if ( isset( $report_data['Case Type'] ) && isset( $report_data['Report Name'] ) ) {
        $case_type = $report_data['Case Type'];
        $report_name = $report_data['Report Name'];

        // Organize reports by Case Type and store other metadata under each report name
        if ( ! isset( $reports[ $case_type ] ) ) {
          $reports[ $case_type ] = [];
        }
        $reports[ $case_type ][ $report_name ] = $report_data;
      }
    }
  }

  return $reports;
}

// Shortcode for displaying the report form
function iecmhc_case_reports_shortcode() {
  $reports = iecmhc_get_report_data();

  ob_start();
  ?>
  <form id="iecmhc-case-reports-form" class="p-4 border rounded bg-light" method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="mb-3">
      <label for="case-type" class="form-label">Case Type</label>
      <select id="case-type" name="case_type" class="form-select form-control" required>
        <option value="" selected disabled>Select Case Type</option>
        <?php foreach ( $reports as $case_type => $report_names ) : ?>
          <option value="<?php echo esc_attr( $case_type ); ?>"><?php echo esc_html( $case_type ); ?></option>
        <?php endforeach; ?>
      </select>
      <div id="case-type-feedback" class="invalid-feedback">Please select a case type.</div>
    </div>

    <div class="mb-3">
      <label for="report-name" class="form-label">Report Name</label>
      <select id="report-name" name="report_name" class="form-select form-control" required disabled>
        <option value="" selected disabled>Select Report Name</option>
      </select>
      <div id="report-name-feedback" class="invalid-feedback">Please select a report name.</div>
    </div>

    <div class="mb-3">
      <label for="scope" class="form-label">Scope</label>
      <select id="scope" name="scope" class="form-select form-control" required disabled>
        <option value="" selected disabled>Select Scope</option>
      </select>
      <div id="scope-feedback" class="invalid-feedback">Please select a scope.</div>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
  </form>

  <div id="report-output" class="mt-4">
    <?php
      if ( isset($_POST['case_type']) && isset($_POST['report_name']) ) {
        // Handle the report display
        iecmhc_display_report($_POST['case_type'], $_POST['report_name']);
      }
    ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const reports = <?php echo wp_json_encode( $reports ); ?>;
      const caseTypeSelect = document.getElementById('case-type');
      const reportNameSelect = document.getElementById('report-name');

      caseTypeSelect.addEventListener('change', function() {
        const selectedCaseType = caseTypeSelect.value;

        // Reset Report Name field
        reportNameSelect.innerHTML = '<option value="">Select Report Name</option>';
        reportNameSelect.disabled = true;

        if (selectedCaseType && reports[selectedCaseType]) {
          Object.keys(reports[selectedCaseType]).forEach(reportName => {
            const option = document.createElement('option');
            option.value = reportName;
            option.textContent = reportName;
            reportNameSelect.appendChild(option);
          });
          reportNameSelect.disabled = false;
        }
      });
    });
  </script>
  <?php
  return ob_get_clean();
}
add_shortcode('iecmhc_case_reports_form', 'iecmhc_case_reports_shortcode');

// Function to include the report file based on POST data
function iecmhc_display_report($case_type, $report_name) {
  // Sanitize the inputs
  $case_type = sanitize_text_field($case_type);
  $report_name = sanitize_text_field($report_name);

  // Construct the file path for the selected report
  $report_file = plugin_dir_path(__DIR__) . 'reports/' . sanitize_title($case_type) . '-' . sanitize_title($report_name) . '.php';

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