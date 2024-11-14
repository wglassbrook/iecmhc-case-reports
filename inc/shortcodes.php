<?php
// Shortcode functions for IECMHC Case Reports

function iecmhc_case_reports_form_shortcode() {
  $reports = iecmhc_get_report_data();

  ob_start();
  ?>
  <form id="iecmhc-case-reports-form" class="p-4 border rounded bg-light">
    <div class="mb-3">
      <label for="case-type" class="form-label">Case Type</label>
      <select id="case-type" name="case_type" class="form-select form-control">
        <option value="" selected disabled>Select Case Type</option>
        <?php foreach ( $reports as $case_type => $report_names ) : ?>
          <option value="<?php echo esc_attr( $case_type ); ?>"><?php echo esc_html( $case_type ); ?></option>
        <?php endforeach; ?>
      </select>
      <div id="case-type-feedback" class="invalid-feedback">Please select a case type.</div>
    </div>

    <div class="mb-3">
      <label for="report-name" class="form-label">Report Name</label>
      <select id="report-name" name="report_name" class="form-select form-control" disabled>
        <option value="" selected disabled>Select Report Name</option>
      </select>
      <div id="report-name-feedback" class="invalid-feedback">Please select a report name.</div>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
  </form>

  <!-- Empty div for displaying report content -->
  <div id="report-output" class="mt-4"></div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const reports = <?php echo wp_json_encode( $reports ); ?>;
      const caseTypeSelect = document.getElementById('case-type');
      const reportNameSelect = document.getElementById('report-name');
      const reportScopeSelect = document.getElementById('report-scope');
      const reportOutput = document.getElementById('report-output');
      const scopeFieldContainer = document.getElementById('scope-field-container');

      // Event listener for Case Type change
      caseTypeSelect.addEventListener('change', function() {
        const selectedCaseType = caseTypeSelect.value;

        // Reset Report Name and Report Scope fields
        reportNameSelect.innerHTML = '<option value="">Select Report Name</option>';
        reportNameSelect.disabled = true;
        reportScopeSelect.innerHTML = '<option value="">Select Report Scope</option>';
        scopeFieldContainer.style.display = 'none';

        // Populate Report Name options based on selected Case Type
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

      // Event listener for Report Name change to show Scope options if available
      reportNameSelect.addEventListener('change', function() {
        const selectedCaseType = caseTypeSelect.value;
        const selectedReportName = reportNameSelect.value;

        // Reset Report Scope field
        reportScopeSelect.innerHTML = '<option value="">Select Report Scope</option>';
        scopeFieldContainer.style.display = 'none';

        // Populate Report Scope options if Scope attribute is available
        if (selectedCaseType && selectedReportName) {
          const reportData = reports[selectedCaseType][selectedReportName];
          
          if (reportData && reportData['Scope']) {
            const scopes = reportData['Scope'].split(',').map(scope => scope.trim());
            scopes.forEach(scope => {
              const option = document.createElement('option');
              option.value = scope;
              option.textContent = scope;
              reportScopeSelect.appendChild(option);
            });
            scopeFieldContainer.style.display = 'block';
          }
        }
      });

      // Event listener for form submission
      document.getElementById('iecmhc-case-reports-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const caseType = caseTypeSelect.value;
        const reportName = reportNameSelect.value;
        const reportScope = reportScopeSelect.value;

        if (caseType && reportName) {
          // AJAX request to fetch the report content
          fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
              action: 'fetch_report_content',
              case_type: caseType,
              report_name: reportName,
              report_scope: reportScope
            })
          })
          .then(response => response.text())
          .then(data => {
            reportOutput.innerHTML = data;
          })
          .catch(error => console.error('Error:', error));
        }
      });
    });
  </script>

  <?php
  return ob_get_clean();
}
add_shortcode( 'iecmhc_case_reports_form', 'iecmhc_case_reports_form_shortcode' );
