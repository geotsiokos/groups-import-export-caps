<?php
/**
 * Capability import.
 */
class Groups_Import_Caps {

	const MAX_FGET_LENGTH = 1024;
	const BASE_DELTA = 1048576;
	const DELTA_F    = 1.62;

	private static $admin_messages = array();
	
	private static $column_names = array( 'group', 'capability'	);

	private static $notify_users = true;

	/**
	 * Init hook to catch import file generation request.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
	}

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			echo '<div style="padding:1em;margin:1em;border:1px solid #aa0;border-radius:4px;background-color:#ffe;color:#333;">';
			foreach ( self::$admin_messages as $msg ) {
				echo '<p>';
				echo $msg;
				echo '</p>';
			}
			echo '</div>';
		}
	}

	/**
	 * Catch request to generate import file.
	 */
	public static function wp_init() {
		
		if ( Groups_Import_Export_Caps::is_user_import_request() ) {
			if ( wp_verify_nonce( $_REQUEST['groups-caps-import'], 'import' ) ) {
				if ( $_REQUEST['action'] == 'import_users' ) {
					self::import_caps( !empty( $_REQUEST['test'] ) );
				}
			} 
		}
	}

	/**
	 * Import from uploaded file.
	 * 
	 * @param boolean $test if true, no users are imported; defaults to false
	 * @return int number of records created
	 */
	private static function import_caps( $test = false ) {

		global $wpdb;

		$charset = get_bloginfo( 'charset' );
		$now     = date( 'Y-m-d H:i:s', time() );

		$memory_limit = ini_get( 'memory_limit' );
		preg_match( '/([0-9]+)(.)/', $memory_limit, $matches );
		if ( isset( $matches[2] ) ) {
			$exp = array( 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5, 'E' => 6 );
			if ( key_exists( $matches[2], $exp ) ) {
				$memory_limit *= pow( 1024, $exp[$matches[2]] );
			}
		}

		$bytes              = memory_get_usage( true );
		$max_execution_time = ini_get( 'max_execution_time' );
		if ( function_exists( 'getrusage' ) ) {
			$resource_usage = getrusage();
			if ( isset( $resource_usage['ru_utime.tv_sec'] ) ) {
				$initial_execution_time = $resource_usage['ru_stime.tv_sec'] + $resource_usage['ru_utime.tv_sec'] + 2; // add 2 as top value for the sum of ru_stime.tv_usec and ru_utime.tv_usec
			}
		}

		$assign_groups = array();
		$requested_groups = !empty( $_REQUEST['groups'] ) ? $_REQUEST['groups'] : array();
		$create_groups    = !empty( $_REQUEST['create_groups'] );		

		self::$notify_users = !empty( $_REQUEST['notify_users'] );

		if ( isset( $_FILES['file'] ) ) {
			if ( $_FILES['file']['error'] == UPLOAD_ERR_OK ) {
				$tmp_name = $_FILES['file']['tmp_name'];
				if ( file_exists( $tmp_name ) ) {
					if ( $h = @fopen( $tmp_name, 'r' ) ) {

						$imported           = 0;
						$updated            = 0;
						$valid              = 0;
						$invalid            = 0;
						$skipped            = 0;
						$line_number        = 0;
						$update_users       = false; 
						$stop_on_errors     = true; 
						
						$errors             = 0;
						$warnings           = 0;
						$skip_limit_checks  = !empty( $_REQUEST['skip_limit_checks'] );
						
						$column_names = self::$column_names;
						while( !feof( $h ) ) {

							$line  = '';
							$chunk = '';
							while( ( $chunk = fgets( $h, self::MAX_FGET_LENGTH ) ) !== false ) {
								$line .= $chunk;
								if ( ( strpos( $chunk, "\n" ) !== false ) || feof( $h ) ) {
									break;
								}
							}
							if ( strlen( $line ) == 0 ) {
								break;
							}

							$line_number++;

							$skip = false;
							$line = preg_replace( '/\r|\n/', '', $line );
							$line = trim( $line );							

							// skip comment and empty lines
							if ( strpos( $line, '#' ) === 0 ) {
								continue;
							}
							if ( strlen( $line ) === 0 ) {
								continue;
							}							

							// data values
							$data = explode( "\t", $line );
							$userdata = array();
							$group_data = array();
							
							foreach( $column_names as $i => $column_name ) {
								if ( isset( $data[$i] ) ) {
									$value = trim( $data[$i] );									
									$group_data[$column_name] = $value;
								}
							}							

							// data check
							if ( empty( $group_data['group'] ) ) {
								self::$admin_messages[] = sprintf( __( 'Error on line %d, group name not valid or blank.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ), $line_number );
								$errors++;
								$skip = true;
							}
							
							// import
							if ( !$skip ) {
								if ( !empty( $group_data['group'] ) && !empty( $group_data['capability'] ) ) {
									$valid++;								
									if ( self::import_capability( $group_data ) ) {
										$imported++;										
									}
								}
							} else {
								$skipped++;
							}
							
							if ( $stop_on_errors && ( $errors > 0 ) ) {
								break;
							}

							if ( !$skip_limit_checks ) {
								// memory guard
								if ( is_numeric( $memory_limit ) ) {
									$old_bytes = $bytes;
									$bytes     = memory_get_usage( true );
									$remaining = $memory_limit - $bytes;
									$delta = self::BASE_DELTA;
									if ( $bytes > $old_bytes ) {
										$delta += intval( ( $bytes - $old_bytes ) * self::DELTA_F );
									}
									if ( $remaining < $delta ) {
										self::$admin_messages[] = sprintf( __( 'Warning, stopped after line %d to avoid exhausting the available memory for PHP. Consider raising <a href="http://php.net/manual/en/ini.core.php#ini.memory-limit">memory_limit</a> or reducing the number of records imported.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ), $line_number );
										break;
									}
								}
	
								// time guard
								if ( function_exists( 'getrusage' ) ) {
									$resource_usage = getrusage();
									if ( isset( $resource_usage['ru_utime.tv_sec'] ) ) {
										$execution_time = $resource_usage['ru_stime.tv_sec'] + $resource_usage['ru_utime.tv_sec'] + 2; // add 2 as top value for the sum of ru_stime.tv_usec and ru_utime.tv_usec
										$d = ceil( $execution_time - $initial_execution_time );
										if ( intval( $d * self::DELTA_F ) > ( $max_execution_time - $d ) ) {
											self::$admin_messages[] = sprintf( __( 'Warning, stopped after line %d to avoid reaching the maximum execution time for PHP. Consider raising <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time">max_execution_time</a> or reducing the number of records imported.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ), $line_number );
											break;
										}
									}
								}
							}
						}
						@fclose( $h );

						self::$admin_messages[] = sprintf( _n( '1 valid entry has been read.', '%d valid entries have been read.', $valid, GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ), $valid );
						self::$admin_messages[] = sprintf( _n( '1 entry has been skipped.', '%d entries have been skipped.', $skipped, GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ), $skipped );
						self::$admin_messages[] = sprintf( _n( '1 capability has been imported.', '%d capabilities have been imported.', $imported, GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ), $imported );
						self::$admin_messages[] = sprintf( _n( '1 capability has been updated.', '%d capabilities have been updated.', $updated, GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ), $updated );

					} else {
						self::$admin_messages[] = __( 'Import failed (error opening temporary file).', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
					}
				}
			} else if ( $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE ) {
				self::$admin_messages[] = __( 'Please choose a file to import from.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
			}
		}

	}

	
	/**
	 * Insert a new capability and relate it to group
	 * 
	 * @param array $group_data
	 * @return boolean true on success
	 */
	private static function import_capability ( $group_data = array() ) {

		$result = false;
		
		$group_name = $group_data['group'];
		$capability_name = $group_data['capability'];
		$capability_id = 0;
		
		if ( Groups_Group::read_by_name( $group_name ) ) {
			$group = Groups_Group::read_by_name( $group_name );
			$group_id = $group->group_id;
		
			if ( Groups_Capability::read_by_capability( $capability_name ) ) {
				
				$capability = Groups_Capability::read_by_capability( $capability_name );
				$capability_id = $capability->capability_id;
							
			} else {				
				$capability_id = Groups_Capability::create( array( 'capability' => $capability_name ) );
			}
			
			if ( Groups_Group_Capability::create( array( 'group_id' => $group_id, 'capability_id' => $capability_id ) ) ) {
				$result = true;
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}		
				
		return $result;
	}
}
Groups_Import_Caps::init();

