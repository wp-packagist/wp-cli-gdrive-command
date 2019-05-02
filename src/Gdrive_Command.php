<?php

namespace WP_CLI_GOOGLE_DRIVE;

/**
 * Backup and Restore WordPress.
 *
 * ## EXAMPLES
 *
 *
 *
 */
class Gdrive_Command extends \WP_CLI_Command {
	/**
	 * Verify user identity on Google.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : Config key
	 * ---
	 * options:
	 *   - source
	 *   - backup_dir
	 * ---
	 *
	 * <value>
	 * : Config Value
	 *
	 * ## EXAMPLES
	 *
	 *      # Change backup source to gdrive
	 *      $ wp backup set source gdrive
	 *      Success: Saved backup source config.
	 *
	 * @when before_wp_load
	 */
	function auth( $_, $assoc ) {


	}

}