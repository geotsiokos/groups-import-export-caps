<?php

/**
 * Admin section.
 */
class Groups_Import_Export_Caps_Admin {

	/**
	 * Admin options setup.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		//add_filter( 'plugin_action_links_'. plugin_basename( GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_FILE ), array( __CLASS__, 'admin_settings_link' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Admin options admin setup.
	 */
	public static function admin_init() {
		wp_register_style( 'groups_import_export_admin', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_URL . 'css/admin.css', array(), GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_VERSION );
	}

	/**
	 * Loads styles for the admin section.
	 */
	public static function admin_print_styles() {
		wp_enqueue_style( 'groups_import_export_admin' );
	}

	/**
	 * Enqueues the select script.
	 */
	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( isset( $screen->id ) ) {
			switch( $screen->id ) {
				case 'groups_page_groups-caps-import' :
				case 'groups_page_groups-caps-export' :
					require_once GROUPS_VIEWS_LIB . '/class-groups-uie.php';
					Groups_UIE::enqueue( 'select' );
					break;
			}
		}
	}

	public static function admin_print_scripts() {
	}

	/**
	 * Add a menu item to the Appearance menu.
	 */
	public static function admin_menu() {
		$page = add_submenu_page(
			'groups-admin',
			__( 'Import Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ),
			__( 'Import Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ),
			'manage_options',
			'groups-caps-import',
			array( __CLASS__, 'caps_import' )
		);
		add_action( 'admin_print_styles-' . $page, array( __CLASS__, 'admin_print_styles' ) );
		add_action( 'admin_print_scripts-' . $page, array( __CLASS__, 'admin_print_scripts' ) );
		add_action( 'load-' . $page, array( __CLASS__, 'add_help_tab' ) );

		$page = add_submenu_page(
			'groups-admin',
			__( 'Export Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ),
			__( 'Export Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ),
			'manage_options',
			'groups-caps-export',
			array( __CLASS__, 'caps_export' )
		);
		add_action( 'admin_print_styles-' . $page, array( __CLASS__, 'admin_print_styles' ) );
		add_action( 'admin_print_scripts-' . $page, array( __CLASS__, 'admin_print_scripts' ) );
		add_action( 'load-' . $page, array( __CLASS__, 'add_help_tab' ) );
	}

	/**
	 * Adds the help tab.
	 */
	public static function add_help_tab() {
		if ( $screen = get_current_screen() ) {
			$id = null;
			switch( $screen->id ) {
				case 'groups_page_groups-caps-import' :
					$id = 'import-caps';
					$title = __( 'Import Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content = '';
					$content .= '<h2>';
					$content .= __( 'File Format', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content .= '</h2>';
					$content .= '<p>';
					$content .= __( 'The accepted file format is a text file with values separated by tabs, providing one or more of the following values on a single line for each user:', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content .= '</p>';
					$content .= '<ol>';
					$content .= '<li>';
					$content .= __( '<code>Group name</code> The text name of the group.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content .= '</li>';
					$content .= '<li>';
					$content .= __( '<code>Capability name</code> The capability name which is attached to that group.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content .= '</li>';
					$content .= '</ol>';				

					break;
				case 'groups_page_groups-caps-export' :
					$id = 'export-caps';
					$title = __( 'Export Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content = '';
					$content .= '<h2>';
					$content .= __( 'File Format', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content .= '</h2>';
					$content .= '<p>';
					$content .= __( 'Here a text file can be generated with values separated by tabs. A single line is created for each group.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					$content .= '</p>';
			}
			if ( $id !== null ) {
				$screen->add_help_tab( array(
					'id'      => $id,
					'title'   => $title,
					'content' => $content
				) );
			}
		}
	}

	/**
	 * User import.
	 */
	public static function caps_import() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ) );
		}
		echo
			'<h2>' .
			__( 'Import Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ) .
			'</h2>';
		echo '<div class="groups-caps-import">';
		include_once ( GROUPS_IMPORT_EXPORT_CAPS_ADMIN_LIB . '/caps-import.php' );
		echo '</div>';
	}

	/**
	 * User export.
	 */
	public static function caps_export() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ) );
		}
		echo
			'<h2>' .
			__( 'Export Capabilities', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ) .
			'</h2>';
		echo '<div class="groups-caps-export">';
		include_once ( GROUPS_IMPORT_EXPORT_CAPS_ADMIN_LIB . '/caps-export.php' );
		echo '</div>';
	}

	/**
	 * Adds plugin links.
	 *
	 * @param array $links
	 * @param array $links with additional links
	 */
	public static function admin_settings_link( $links ) {
		if ( current_user_can( 'manage_options' ) ) {
			$links[] = '<a href="' . get_admin_url( null, 'admin.php?page=groups-caps-import' ) . '">' . __( 'Import Users', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ) . '</a>';
			$links[] = '<a href="' . get_admin_url( null, 'admin.php?page=groups-caps-export' ) . '">' . __( 'Export Users', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ) . '</a>';
		}
		return $links;
	}

}
add_action( 'init', array( 'Groups_Import_Export_Caps_Admin', 'init' ) );
