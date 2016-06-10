<?php
/**
 * Capability export.
 */
class Groups_Export_Caps {
	
	/**
	 * Init hook to catch export file generation request.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );		
	}

	/**
	 * Catch request to generate export file.
	 */
	public static function wp_init() {		
		if ( Groups_Import_Export_Caps::is_user_export_request() ) {			
			if ( wp_verify_nonce( $_REQUEST['groups-caps-export'], 'export' ) ) {
				self::export_caps();
			}
		}
	}

	/**
	 * Renders the capabilities export file.
	 */
	public static function export_caps() {
		global $wpdb;
		if ( !headers_sent() ) {					
		
			$groups_group_table = _groups_get_tablename( 'group' );
			$groups_group_capability_table = _groups_get_tablename( 'group_capability' );
			$groups_capability_table = _groups_get_tablename( 'capability' );
			
			$select_query 	= 	"SELECT $groups_group_table.name,  $groups_capability_table.capability
								FROM $groups_group_table INNER JOIN $groups_group_capability_table
								ON $groups_group_table.group_id = $groups_group_capability_table.group_id 
								INNER JOIN $groups_capability_table
								ON $groups_capability_table.capability_id = $groups_group_capability_table.capability_id ";
						
			$results = $wpdb->get_results( $select_query );			
			
			$charset = get_bloginfo( 'charset' );
			$now     = date( 'Y-m-d-H-i-s', time() );
			header( 'Content-Description: File Transfer' );
			if ( !empty( $charset ) ) {
				header( 'Content-Type: text/tab-separated-values; charset=' . $charset );
			} else {
				header( 'Content-Type: text/tab-separated-values' );
			}
			header( "Content-Disposition: attachment; filename=\"groups-capabilities-$now.tsv\"" );			
			
			foreach( $results as $result ) {
				$group_names[] = $result->name;
				$capability_name[] = $result->capability;
				printf ( "$result->name\t");
				printf ( "$result->capability\t");
				printf ( "\n" );				
			}
			die;
		} else {
			wp_die( 'ERROR: headers already sent' );
		}
	}
}
Groups_Export_Caps::init();
