<?php

namespace WP_CLI_GOOGLE_DRIVE;

use WP_CLI;
use WP_CLI_Google_Drive;
use WP_CLI_Helper;
use WP_CLI_Util;

/**
 * Google Drive Cloud Storage.
 *
 * ## EXAMPLES
 *
 *      # User authentication
 *      $ wp gdrive auth
 *      Success: User authentication verified.
 *
 *      # Show list of files and folder in root dir
 *      $ wp gdrive ls
 *
 *
 */
class Gdrive_Command extends \WP_CLI_Command {
	/**
	 * Verify user identity on Google.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : force change user auth.
	 *
	 * ## EXAMPLES
	 *
	 *      # User authentication
	 *      $ wp gdrive auth
	 *      Success: User authentication verified.
	 *
	 *      # change gmail account
	 *      $ wp gdrive auth --force
	 *
	 * @when before_wp_load
	 */
	function auth( $_, $assoc ) {

		// Sign in new user if not Force
		WP_CLI_Helper::pl_wait_start();
		$auth = WP_CLI_Google_Drive::auth();
		if ( $auth === true and ! isset( $assoc['force'] ) ) {
			$gdrive    = WP_CLI_Google_Drive::get_config();
			$user_info = WP_CLI_Google_Drive::get_user_info_by_id_token( $gdrive['id_token'] );
			if ( ! isset( $user_info['error'] ) ) {
				WP_CLI_Helper::pl_wait_end();
				WP_CLI::line( "name: " . $user_info['name'] );
				WP_CLI::line( "email: " . $user_info['email'] );
				WP_CLI::line( "locale: " . $user_info['locale'] );
			}
			exit;
		}

		//Define STDIN
		WP_CLI_Helper::pl_wait_end();
		WP_CLI_Util::define_stdin();

		// Get Google Client ID
		while ( true ) {
			echo "Enter " . WP_CLI_Helper::color( "Client ID", "Y" ) . ": ";
			$Client_ID = fread( STDIN, 200 );
			if ( is_string( trim( $Client_ID ) ) and ! empty( $Client_ID ) ) {
				break;
			}
		}

		// Get Google Client secret
		while ( true ) {
			echo "Enter " . WP_CLI_Helper::color( "Client secret", "Y" ) . ": ";
			$Client_Secret = fread( STDIN, 200 );
			if ( is_string( trim( $Client_Secret ) ) and ! empty( $Client_Secret ) ) {
				break;
			}
		}

		if ( isset( $Client_ID ) and isset( $Client_Secret ) ) {

			// Generate Google Auth Url
			$auth_link = WP_CLI_Google_Drive::create_auth_url( trim( $Client_ID ) );

			WP_CLI_Helper::br();
			WP_CLI_Helper::Browser( $auth_link );
			WP_CLI::line( "Open the following link in your browser: " );
			printf( "%s", $auth_link );
			WP_CLI_Helper::br( 2 );

			// Get Google Client ID
			while ( true ) {
				echo "Enter " . WP_CLI_Helper::color( "verification code", "Y" ) . ": ";
				$auth_code = fread( STDIN, 200 );
				if ( is_string( trim( $auth_code ) ) and ! empty( $auth_code ) ) {
					break;
				}
			}

			if ( isset( $auth_code ) ) {
				WP_CLI_Helper::pl_wait_start();
				$user_token = WP_CLI_Google_Drive::get_token_by_code( trim( $auth_code ), trim( $Client_ID ), trim( $Client_Secret ) );
				if ( isset( $user_token['error'] ) ) {
					WP_CLI::error( $user_token['message'] );
				} else {
					// Set User Token in WP-Cli Config
					WP_CLI_Google_Drive::save_user_token_in_wp_cli_config( array_merge( array( 'client_id' => $Client_ID, 'client_secret' => $Client_Secret ), $user_token ) );

					// Get User info
					WP_CLI_Helper::pl_wait_end();
					$user_info = WP_CLI_Google_Drive::get_user_info_by_id_token( $user_token['id_token'] );
					if ( ! isset( $user_info['error'] ) ) {
						WP_CLI_Helper::br();
						WP_CLI::line( "----" );
						WP_CLI::line( "name: " . $user_info['name'] );
						WP_CLI::line( "email: " . $user_info['email'] );
						WP_CLI::line( "locale: " . $user_info['locale'] );
						WP_CLI::line( "----" );
						WP_CLI_Helper::br();
					}
					WP_CLI::success( "User authentication verified." );
				}
			}
		}
	}

	/**
	 * List of files and folder.
	 *
	 * ## OPTIONS
	 *
	 * [<path>]
	 * : show files in custom path.
	 *
	 * ## EXAMPLES
	 *
	 *      # Show list of files and folder in root dir
	 *      $ wp gdrive ls
	 *
	 *      # show list of files from custom path
	 *      $ wp gdrive ls /folder/folder/
	 *
	 * @when before_wp_load
	 * @alias list
	 */
	function ls( $_, $assoc ) {

		// Current Path
		$current_path = "/";
		if ( isset( $_[0] ) and ! empty( $_[0] ) and trim( $_[0] ) != "/" ) {
			// Current Path
			$current_path = "/" . trim( WP_CLI_Google_Drive::sanitize_path( $_[0] ), "/" ) . "/";

			// Check Exist Path
			WP_CLI_Helper::pl_wait_start();
			$path_id = WP_CLI_Google_Drive::get_id_by_path( $_[0] );
			if ( $path_id === false ) {
				WP_CLI::error( "the path is not found in your Google Drive service." );
			} else {
				// Check is file not folder
				if ( $path_id['mimeType'] != WP_CLI_Google_Drive::$folder_mime_type ) {
					WP_CLI::error( "Your address includes the file." );
				}
			}
		}

		// Show Please Wait
		if ( ! isset( $path_id ) ) {
			WP_CLI_Helper::pl_wait_start();
		}

		// Get List Of File
		$args = array();
		if ( isset( $path_id ) ) {
			$args = array( 'q' => "'" . $path_id['id'] . "' in parents and trashed=false" );
		}
		$files = WP_CLI_Google_Drive::file_list( $args );

		// Check Error
		if ( isset( $files['error'] ) ) {
			WP_CLI::error( $files['message'] );
		}

		// Check Empty Dir
		if ( count( $files ) < 1 ) {
			WP_CLI::error( "There are no files in this path." );
		}

		// Show Files
		self::list_table( $current_path, $files );
	}

	/**
	 * Show List Table Of Files
	 *
	 * @param array $files
	 */
	private static function list_table( $current_path, $files = array() ) {

		// Remove Please Wait
		WP_CLI_Helper::pl_wait_end();

		// Show Table List
		$list = array();
		foreach ( $files as $file ) {
			$list[] = array(
				'name'         => WP_CLI_Util::substr( $file['name'], 100 ),
				'type'         => ( $file['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type ? "folder" : "file" ),
				'size'         => ( isset( $file['size'] ) ? \WP_CLI_FileSystem::size_format( $file['size'] ) : '-' ),
				'createdTime'  => ( isset( $file['createdTime'] ) ? self::sanitize_date_time( $file['createdTime'] ) : '-' ),
				'modifiedTime' => ( isset( $file['modifiedTime'] ) ? self::sanitize_date_time( $file['modifiedTime'] ) : '-' ),
				'mimeType'     => ( ( isset( $file['mimeType'] ) and $file['mimeType'] != WP_CLI_Google_Drive::$folder_mime_type ) ? $file['mimeType'] : '-' ),
			);
		}

		WP_CLI_Helper::create_table( $list );
	}

	/**
	 * Sanitize Date time
	 *
	 * @param $time
	 * @return string
	 */
	private static function sanitize_date_time( $time ) {
		$exp          = explode( "T", $time );
		$explode_time = explode( ".", $exp[1] );
		return $exp[0] . " " . $explode_time[0];
	}

}