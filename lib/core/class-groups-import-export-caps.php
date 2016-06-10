<?php
/**
 * class-groups-import-export.php
 * 
 */

/**
 * Plugin controller and booter.
 */
class Groups_Import_Export_Caps {
	
	public static $admin_messages = array();
	
	const DEFAULT_LIMIT = 100;
	
	/**
	 * Boots the plugin.
	 */
	public static function boot() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		load_plugin_textdomain( GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN, null, 'groups-import-export/languages' );
		if ( self::check_dependencies() ) {
			if ( is_admin() ) {
				require_once( GROUPS_IMPORT_EXPORT_CAPS_ADMIN_LIB . '/class-groups-import-export-caps-admin.php' );
				require_once( GROUPS_IMPORT_EXPORT_CAPS_CORE_LIB . '/class-groups-export-caps.php' );
			}
			if ( self::is_user_import_request() ) {
				require_once( GROUPS_IMPORT_EXPORT_CAPS_CORE_LIB . '/class-groups-import-caps.php' );
			}
			if ( self::is_user_export_request() ) {
				require_once( GROUPS_IMPORT_EXPORT_CAPS_CORE_LIB . '/class-groups-export-caps.php' );
			}
			
		} else {
			self::$admin_messages[] = __( '<div class="error"><em>Groups Import Export Capabilities</em> is an extension for <a href="http://www.itthinx.com/plugins/groups/">Groups</a> which is required, please install and activate <em>Groups</em>.</div>', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
		}
	}
	
	public static function is_user_import_request() {
		return isset( $_REQUEST['groups-caps-import'] ) && isset( $_REQUEST['action'] );
	}
	
	/**
	 * Returns true if the request is for a user export.
	 *
	 * @return boolean
	 */
	public static function is_user_export_request() {
		return isset( $_REQUEST['groups-caps-export'] ) && isset( $_REQUEST['action'] );
	}
	
	/**
	 * Checks if Groups is activated.
	 * @return true if Groups is there, false otherwise
	 */
	public static function check_dependencies() {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_sitewide_plugins = array_keys( $active_sitewide_plugins );
			$active_plugins = array_merge( $active_plugins, $active_sitewide_plugins );
		}
		$groups_is_active = in_array( 'groups/groups.php', $active_plugins );
		define( 'GROUPS_IMPORT_EXPORT_CAPS_GROUPS_IS_ACTIVE', $groups_is_active );
		return $groups_is_active;
	}
	
	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}
	
}Groups_Import_Export_Caps::boot();