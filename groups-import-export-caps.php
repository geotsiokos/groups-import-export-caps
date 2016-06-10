<?php
/**
 * groups-import-export-caps.php
 * 
 * This code is released under the GNU General Public License.
 * 
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * This header and all notices must be kept intact.
 * 
 * @author George Tsiokos
 * @package groups-import-export-caps
 * @since 1.0.0
 *
 * Plugin Name: Groups Import Export Caps
 * Plugin URI: http://www.itthinx.com/plugins/groups-import-export-caps/
 * Description: Import and export capabilities extension for <a href="http://www.itthinx.com/plugins/groups/">Groups</a>.
 * Author: George Tsiokos
 * Author URI: http://www.netpad.gr
 * Version: 1.0.0
 */
define( 'GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_VERSION', '1.2.3' );
define( 'GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN', 'groups-import-export' );
define( 'GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_FILE', __FILE__ );
define( 'GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DIR', WP_PLUGIN_DIR . '/groups-import-export-caps' );
define( 'GROUPS_IMPORT_EXPORT_CAPS_CORE_LIB', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DIR . '/lib/core' );
define( 'GROUPS_IMPORT_EXPORT_CAPS_ADMIN_LIB', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DIR . '/lib/admin' );
define( 'GROUPS_IMPORT_EXPORT_CAPS_VIEWS_LIB', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DIR . '/lib/views' );

require_once( GROUPS_IMPORT_EXPORT_CAPS_CORE_LIB . '/class-groups-import-export-caps.php' );