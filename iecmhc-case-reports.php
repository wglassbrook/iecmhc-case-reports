<?php

/**
 *  @package      IECMHC Case Reports
 *  @author       Wayne Glassbrook/Ciesa Inc. <wayne@ciesadesign.com>
 *  @license      GPL-2.0+
 *  @link         https://ciesadesign.com
 *  @copyright    2024 Wayne Glassbrook/Ciesa Inc.
 *
 *  Plugin Name:  IECMHC Case Reports
 *  Plugin URI:   https://ciesadesign.com
 *  Description:  A plugin for managing IECMHC case reports.
 *  Version:      4.0
 *  Author:       Wayne Glassbrook/Ciesa Inc.
 *  Author URI:   https://ciesadesign.com
 *  License:      GPLv2
 *  License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 *  Text Domain:  iecmhc-case-reports
 *
 * 
 *
 *  Copyright 2024 Wayne Glassbrook/Ciesa Inc. (email : wayne@ciesadesign.com)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
**/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'IECMHC_CASE_REPORTS_VERSION', '4.0' );
define( 'IECMHC_CASE_REPORTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'IECMHC_CASE_REPORTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include necessary files
require_once IECMHC_CASE_REPORTS_PLUGIN_DIR . 'inc/functions.php';
// require_once IECMHC_CASE_REPORTS_PLUGIN_DIR . 'inc/shortcodes.php';

// Plugin activation hook
register_activation_hook( __FILE__, 'iecmhc_case_reports_activate' );

function iecmhc_case_reports_activate() {
  // Activation code here
}

// Plugin deactivation hook
register_deactivation_hook( __FILE__, 'iecmhc_case_reports_deactivate' );

function iecmhc_case_reports_deactivate() {
  // Deactivation code here
}

// Plugin initialization
function iecmhc_case_reports_init() {
  load_plugin_textdomain( 'iecmhc-case-reports', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'iecmhc_case_reports_init' );

// Enqueue scripts and styles
function iecmhc_case_reports_enqueue_assets() {
  wp_enqueue_style( 'iecmhc-case-reports-style', IECMHC_CASE_REPORTS_PLUGIN_URL . 'assets/css/style.css', array(), IECMHC_CASE_REPORTS_VERSION );
  wp_enqueue_script( 'iecmhc-case-reports-script', IECMHC_CASE_REPORTS_PLUGIN_URL . 'assets/js/script.js', array( 'jquery' ), IECMHC_CASE_REPORTS_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'iecmhc_case_reports_enqueue_assets' );