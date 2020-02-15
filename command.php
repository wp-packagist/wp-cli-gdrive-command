<?php

namespace WP_CLI_GOOGLE_DRIVE;

# Check Exist WP-CLI
if ( ! class_exists('WP_CLI')) {
    return;
}

# Register 'gdrive' Command
\WP_CLI::add_command('gdrive', Gdrive_Command::class);