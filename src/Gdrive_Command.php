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
 *      # Show Google Drive Storage
 *      $ wp gdrive storage
 *
 *      # remove file with custom path
 *      $ wp gdrive rm /folder/file.zip
 *      Success: Removed File.
 *
 *      # get download link a file
 *      $ wp gdrive share /folder/file.mp3
 *
 *      # private a file for disable download link
 *      $ wp gdrive private /folder/file.mp3
 *
 *      # Rename a file.
 *      $ wp gdrive rename /folder/file.mp3 new.mp3
 *
 *      # Copy a file.
 *      $ wp gdrive copy /folder/file.mp3 /folder/custom/
 *
 *      # Move a file.
 *      $ wp gdrive move /folder/file.mp3 /folder/custom/
 *
 *      # Show list of files and folder in trash.
 *      $ wp gdrive trash
 *
 *      # Create a new folder in root.
 *      $ wp gdrive mkdir wordpress
 *
 *      # Restore a file from trash.
 *      $ wp gdrive restore backup.zip
 *
 *      # Download backup.zip file from root dir in Google Drive.
 *      $ wp gdrive get backup.zip
 *      Success: Download completed.
 *
 *      # Upload backup.zip file to root dir in Google Drive.
 *      $ wp gdrive upload backup.zip
 *      Success: Upload completed.
 *
 *      # Automatic create zip archive from the /wp-content/ folder and upload to custom dir.
 *      $ wp gdrive upload /wp-content/ /wordpress/backup --zip
 *      Success: Upload completed.
 *
 */
class Gdrive_Command extends \WP_CLI_Command
{
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
    function auth($_, $assoc)
    {
        // Sign in new user if not Force
        WP_CLI_Helper::pl_wait_start();
        $auth = WP_CLI_Google_Drive::auth();
        if ($auth['status'] === true and ! isset($assoc['force'])) {
            $user_info = WP_CLI_Google_Drive::get_user_info_by_access_token($auth['access_token']);
            if ( ! isset($user_info['error'])) {
                WP_CLI_Helper::pl_wait_end();
                WP_CLI::line("Name: " . $user_info['name']);
                WP_CLI::line("Email: " . $user_info['email']);
                WP_CLI::line("Locale: " . $user_info['locale']);
            }
            exit;
        }

        //Define STDIN
        WP_CLI_Helper::pl_wait_end();
        WP_CLI_Util::define_stdin();

        // Get Google Client ID
        while (true) {
            echo "Enter " . WP_CLI_Helper::color("Client ID", "Y") . ": ";
            $Client_ID = fread(STDIN, 200);
            if (is_string(trim($Client_ID)) and ! empty($Client_ID)) {
                break;
            }
        }

        // Get Google Client secret
        while (true) {
            echo "Enter " . WP_CLI_Helper::color("Client secret", "Y") . ": ";
            $Client_Secret = fread(STDIN, 200);
            if (is_string(trim($Client_Secret)) and ! empty($Client_Secret)) {
                break;
            }
        }

        if (isset($Client_ID) and isset($Client_Secret)) {
            // Generate Google Auth Url
            $auth_link = WP_CLI_Google_Drive::create_auth_url(trim($Client_ID));

            WP_CLI_Helper::br();
            WP_CLI_Helper::Browser($auth_link);
            WP_CLI::line("Open the following link in your browser: ");
            printf("%s", $auth_link);
            WP_CLI_Helper::br(2);

            // Get Google Client ID
            while (true) {
                echo "Enter " . WP_CLI_Helper::color("verification code", "Y") . ": ";
                $auth_code = fread(STDIN, 200);
                if (is_string(trim($auth_code)) and ! empty($auth_code)) {
                    break;
                }
            }

            if (isset($auth_code)) {
                WP_CLI_Helper::pl_wait_start();
                $user_token = WP_CLI_Google_Drive::get_token_by_code(trim($auth_code), trim($Client_ID), trim($Client_Secret));
                if (isset($user_token['error'])) {
                    WP_CLI::error($user_token['message']);
                } else {
                    // Set User Token in WP-Cli Config
                    WP_CLI_Google_Drive::save_user_token_in_wp_cli_config(array_merge(array('client_id' => $Client_ID, 'client_secret' => $Client_Secret), $user_token));

                    // Get User info
                    WP_CLI_Helper::pl_wait_end();
                    $user_info = WP_CLI_Google_Drive::get_user_info_by_access_token($user_token['access_token']);
                    if ( ! isset($user_info['error'])) {
                        WP_CLI_Helper::br();
                        WP_CLI::line("----");
                        WP_CLI::line("Name: " . $user_info['name']);
                        WP_CLI::line("Email: " . $user_info['email']);
                        WP_CLI::line("Locale: " . $user_info['locale']);
                        WP_CLI::line("----");
                        WP_CLI_Helper::br();
                    }
                    WP_CLI::success("User authentication verified.");
                }
            }
        }
    }

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
     * @alias about
     */
    function storage($_, $assoc)
    {
        WP_CLI_Helper::pl_wait_start();
        $about = WP_CLI_Google_Drive::about();
        WP_CLI_Helper::pl_wait_end();
        if (isset($about['error'])) {
            WP_CLI::error($about['message']);
        } else {
            if (isset($about['user']['displayName'])) {
                WP_CLI::line("displayName: " . $about['user']['displayName']);
            }
            if (isset($about['user']['emailAddress'])) {
                WP_CLI::line("emailAddress: " . $about['user']['emailAddress']);
            }
            foreach ($about['storageQuota'] as $key => $value) {
                WP_CLI::line("$key: " . \WP_CLI_FileSystem::size_format($value));
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
    function ls($_, $assoc)
    {
        // Current Path
        if (isset($_[0]) and ! empty($_[0]) and trim($_[0]) != "/") {
            // Check Exist Path
            WP_CLI_Helper::pl_wait_start();
            $path_id = WP_CLI_Google_Drive::get_id_by_path($_[0]);
            if ($path_id === false) {
                WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
            } else {
                // Check is file not folder
                if ($path_id['mimeType'] != WP_CLI_Google_Drive::$folder_mime_type) {
                    WP_CLI::error("Your address includes the file.");
                }
            }
        }

        // Show Please Wait
        if ( ! isset($path_id)) {
            WP_CLI_Helper::pl_wait_start();
        }

        // Get List Of File
        $args = array();
        if (isset($path_id)) {
            $args = array('q' => "'" . $path_id['id'] . "' in parents and trashed=false");
        }
        $files = WP_CLI_Google_Drive::file_list($args);

        // Check Error
        if (isset($files['error'])) {
            WP_CLI::error($files['message']);
        }

        // Check Empty Dir
        if (count($files) < 1) {
            WP_CLI::error("There are no files in this path.");
        }

        // Show Files
        self::list_table($files, true);
    }

    /**
     * Remove File or folder By Path.
     *
     * ## OPTIONS
     *
     * <path>
     * : file or folder path.
     *
     * [--trash]
     * : Move file to trash.
     *
     * [--force]
     * : Force removing file and folder without question.
     *
     * ## EXAMPLES
     *
     *      # remove file with custom path
     *      $ wp gdrive rm /folder/file.zip
     *      Success: Removed File.
     *
     *      # Move file to trash.
     *      $ wp gdrive rm /folder/file.zip --trash
     *      Success: Moved file to trash.
     *
     *      # remove dir with force
     *      $ wp gdrive ls /folder/ --force
     *      Success: Removed Folder.
     *
     * @when before_wp_load
     * @alias remove
     * @throws \Exception
     */
    function rm($_, $assoc)
    {
        // Get Path
        if (trim($_[0]) == "/" || trim($_[0]) == "\\" || trim($_[0]) == "root") {
            WP_CLI::error("You can not delete the root folder.");
        }

        // Check Exist Path
        WP_CLI_Helper::pl_wait_start();
        $path_id = WP_CLI_Google_Drive::get_id_by_path($_[0]);
        if ($path_id === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
        }

        // Check path is file or folder
        $type = "file";
        if ($path_id['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type) {
            $type = "folder";
        }

        // Check confirm is not Force
        if ( ! isset($assoc['force'])) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::confirm("Are you sure you want to " . (isset($assoc['trash']) ? "move to trash" : "removed") . " the " . $type . " ?");
        }

        WP_CLI_Helper::pl_wait_start();

        // Prepare remove arg
        $arg = array('fileId' => $path_id['id']);
        if (isset($assoc['trash'])) {
            $arg['trashed'] = true;
        }

        // Remove action
        $remove = WP_CLI_Google_Drive::file_remove($arg);
        WP_CLI_Helper::pl_wait_end();
        if (isset($remove['error'])) {
            WP_CLI::error($remove['message']);
        } else {
            WP_CLI::success((isset($assoc['trash']) ? "The '" . trim($_[0]) . "' moved to trash." : "Removed {$type}."));
        }
    }

    /**
     * Get Download Link a file or folder.
     *
     * ## OPTIONS
     *
     * <path>
     * : file or folder path.
     *
     * ## EXAMPLES
     *
     *      # get download link a file
     *      $ wp gdrive share /folder/file.mp3
     *
     *      # get share link a folder
     *      $ wp gdrive share /folder/folder/
     *
     * @when before_wp_load
     */
    function share($_, $assoc)
    {
        // Get Path
        if (trim($_[0]) == "/" || trim($_[0]) == "\\" || trim($_[0]) == "root") {
            WP_CLI::error("You can not get the root folder share link.");
        }

        // Check Exist Path
        WP_CLI_Helper::pl_wait_start();
        $path_id = WP_CLI_Google_Drive::get_id_by_path($_[0]);
        if ($path_id === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
        }

        // Add Share Permission
        $share_link = WP_CLI_Google_Drive::file_permission(array('fileId' => $path_id['id'], 'permission' => 'public'));
        if (isset($share_link['error'])) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error($share_link['message']);
        } else {
            // Get Share Url
            $file_inf = WP_CLI_Google_Drive::file_get(array('fileId' => $path_id['id']));
            WP_CLI_Helper::pl_wait_end();
            if (isset($file_inf['error'])) {
                WP_CLI::error($file_inf['message']);
            } else {
                if (isset($file_inf['webContentLink'])) {
                    WP_CLI::line(WP_CLI_Helper::color("Download Link: ", "Y") . $file_inf['webContentLink']);
                }
                if (isset($file_inf['webViewLink'])) {
                    WP_CLI::line(WP_CLI_Helper::color("WebView " . (isset($file_inf['webContentLink']) ? " " : "") . "Link: ", "Y") . $file_inf['webViewLink']);
                }
                exit;
            }
        }
    }

    /**
     * Private a file or folder.
     *
     * ## OPTIONS
     *
     * <path>
     * : file or folder path.
     *
     * ## EXAMPLES
     *
     *      # private a file for disable download link
     *      $ wp gdrive private /folder/file.mp3
     *
     *      # private a folder by path
     *      $ wp gdrive private /folder/folder/
     *
     * @when before_wp_load
     * @subcommand private
     */
    function _private($_, $assoc)
    {
        // Get Path
        if (trim($_[0]) == "/" || trim($_[0]) == "\\" || trim($_[0]) == "root") {
            WP_CLI::error("You can not get the root folder share link.");
        }

        // Check Exist Path
        WP_CLI_Helper::pl_wait_start();
        $path_id = WP_CLI_Google_Drive::get_id_by_path($_[0]);
        if ($path_id === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
        }

        // Check path is file or folder
        $type = "file";
        if ($path_id['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type) {
            $type = "folder";
        }

        // Private a File
        $private_file = WP_CLI_Google_Drive::file_permission(array('fileId' => $path_id['id'], 'permission' => 'private'));
        if (isset($private_file['error'])) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error($private_file['message']);
        } else {
            WP_CLI::success("The " . $type . " was the private and disable download link.");
        }
    }

    /**
     * Rename a file or folder.
     *
     * ## OPTIONS
     *
     * <path>
     * : file or folder path.
     *
     * <new_name>
     * : new name of the file or folder.
     *
     * ## EXAMPLES
     *
     *      # Rename a file.
     *      $ wp gdrive rename /folder/file.mp3 new.mp3
     *
     *      # Rename a folder
     *      $ wp gdrive rename /folder/folder/ new_folder_name
     *
     * @when before_wp_load
     * @alias ren
     */
    function rename($_, $assoc)
    {
        // Get Path
        if (trim($_[0]) == "/" || trim($_[0]) == "\\" || trim($_[0]) == "root") {
            WP_CLI::error("You can not get the root folder share link.");
        }

        // Check Exist Path
        WP_CLI_Helper::pl_wait_start();
        $path_id = WP_CLI_Google_Drive::get_id_by_path($_[0]);
        if ($path_id === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
        }

        // Check path is file or folder
        $type = "file";
        if ($path_id['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type) {
            $type = "folder";
        }

        // Renamed the file
        $rename_file = WP_CLI_Google_Drive::file_rename(array('fileId' => $path_id['id'], 'new_name' => trim($_[1])));
        WP_CLI_Helper::pl_wait_end();
        if (isset($rename_file['error'])) {
            WP_CLI::error($rename_file['message']);
        } else {
            WP_CLI::success("Renamed '" . $_[0] . "' " . $type . " to " . WP_CLI_Helper::color(trim($_[1]), "Y") . ".");
        }
    }

    /**
     * Copy a file or folder.
     *
     * ## OPTIONS
     *
     * <path>
     * : file or folder path.
     *
     * <new_path>
     * : new path of the file that is contain a folder path.
     *
     * ## EXAMPLES
     *
     *      # Copy a file.
     *      $ wp gdrive copy /folder/file.mp3 /folder/custom/
     *
     *      # Copy a folder.
     *      $ wp gdrive copy /folder/name/ /custom
     *
     * @when before_wp_load
     * @alias cp
     */
    function copy($_, $assoc)
    {
        // Get Path
        if (trim($_[0]) == "/" || trim($_[0]) == "\\" || trim($_[0]) == "root" || trim($_[0]) == "home") {
            WP_CLI::error("You can not copy the root folder.");
        }
        WP_CLI_Helper::pl_wait_start();

        // Check Exist Path
        $path_id = WP_CLI_Google_Drive::get_id_by_path($_[0]);
        if ($path_id === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
        }

        // Check file or folder
        $type = "file";
        if ($path_id['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type) {
            $type = "folder";
        }

        // Check new path
        $new_path = 'root';
        if (trim($_[1]) == "/" || trim($_[1]) == "\\" || trim($_[1]) == "root" || trim($_[1]) == "home") {
            $new_path_id = 'root';
        } else {
            $new_path_id = WP_CLI_Google_Drive::get_id_by_path($_[1]);
            if ($new_path_id === false) {
                WP_CLI_Helper::pl_wait_end();
                WP_CLI::error("The '" . $_[1] . "' is not found in your Google Drive.");
            } else {
                // new Path only folder
                if ($new_path_id['mimeType'] != WP_CLI_Google_Drive::$folder_mime_type) {
                    WP_CLI::error("The new path must be a folder.");
                }

                // Get New Path id
                $new_path    = trim($_[1]);
                $new_path_id = $new_path_id['id'];
            }
        }

        // Copy
        $copy = WP_CLI_Google_Drive::file_copy(array('fileId' => $path_id['id'], 'toId' => $new_path_id));
        WP_CLI_Helper::pl_wait_end();
        if (isset($copy['error'])) {
            WP_CLI::error($copy['message']);
        } else {
            WP_CLI::success("The '" . $_[0] . "' " . $type . " copied to '" . trim($new_path) . "'.");
        }
    }

    /**
     * Move a file or folder.
     *
     * ## OPTIONS
     *
     * <path>
     * : file or folder path.
     *
     * <new_path>
     * : new path of the file that is contain a folder path.
     *
     * ## EXAMPLES
     *
     *      # Move a file.
     *      $ wp gdrive move /folder/file.mp3 /folder/custom/
     *
     *      # Move a folder.
     *      $ wp gdrive move /folder/name/ /custom
     *
     * @when before_wp_load
     * @alias mv
     */
    function move($_, $assoc)
    {
        // Get Path
        if (trim($_[0]) == "/" || trim($_[0]) == "\\" || trim($_[0]) == "root" || trim($_[0]) == "home") {
            WP_CLI::error("You can not move the root folder.");
        }
        WP_CLI_Helper::pl_wait_start();

        // Check Exist Path
        $path_id = WP_CLI_Google_Drive::get_id_by_path($_[0]);
        if ($path_id === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
        }

        // Check file or folder
        $type = "file";
        if ($path_id['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type) {
            $type = "folder";
        }

        // Check new path
        $new_path = 'root';
        if (trim($_[1]) == "/" || trim($_[1]) == "\\" || trim($_[1]) == "root" || trim($_[1]) == "home") {
            $new_path_id = 'root';
        } else {
            $new_path_id = WP_CLI_Google_Drive::get_id_by_path($_[1]);
            if ($new_path_id === false) {
                WP_CLI_Helper::pl_wait_end();
                WP_CLI::error("The '" . $_[1] . "' is not found in your Google Drive.");
            } else {
                // new Path only folder
                if ($new_path_id['mimeType'] != WP_CLI_Google_Drive::$folder_mime_type) {
                    WP_CLI::error("The new path must be a folder.");
                }

                // Get New Path id
                $new_path    = trim($_[1]);
                $new_path_id = $new_path_id['id'];
            }
        }

        // Get current file parent
        $file_inf = WP_CLI_Google_Drive::file_get(array('fileId' => $path_id['id']));
        if (isset($file_inf['error'])) {
            WP_CLI::error($file_inf['message']);
        } else {
            $previousParents = join(',', $file_inf['parents']);
        }

        // Move
        $move = WP_CLI_Google_Drive::file_move(array('fileId' => $path_id['id'], 'currentParent' => $previousParents, 'toId' => $new_path_id));
        WP_CLI_Helper::pl_wait_end();
        if (isset($move['error'])) {
            WP_CLI::error($move['message']);
        } else {
            WP_CLI::success("The '" . $_[0] . "' " . $type . " moved to '" . trim($new_path) . "'.");
        }
    }

    /**
     * List of files and folder in trash.
     *
     * ## OPTIONS
     *
     * [--clear]
     * : empty trash.
     *
     * ## EXAMPLES
     *
     *      # Show list of files and folder in trash.
     *      $ wp gdrive trash
     *
     *      # Clear all files in trash.
     *      $ wp gdrive trash --clear
     *
     * @when before_wp_load
     */
    function trash($_, $assoc)
    {
        // Check empty
        if (isset($assoc['clear'])) {
            WP_CLI::confirm("Are you sure you want to remove all files in trash ?");
            WP_CLI_Helper::pl_wait_start();
            $remove = WP_CLI_Google_Drive::file_remove(array('fileId' => 'trash'));
            WP_CLI_Helper::pl_wait_end();
            if (isset($remove['error'])) {
                WP_CLI::error($remove['message']);
            } else {
                WP_CLI::success("Removed all files in trash.");
            }

            exit;
        }

        // Show Please Wait
        WP_CLI_Helper::pl_wait_start();

        // Get List Of File in trash
        $files = WP_CLI_Google_Drive::file_list(array('q' => "trashed=true"));

        // Check Error
        if (isset($files['error'])) {
            WP_CLI::error($files['message']);
        }

        // Check Empty Dir
        if (count($files) < 1) {
            WP_CLI::error("Trash is empty.");
        }

        // Show Files
        self::list_table($files, false);
    }

    /**
     * Create folder in Google Drive.
     *
     * ## OPTIONS
     *
     * <path>
     * : folder path.
     *
     * ## EXAMPLES
     *
     *      # Create a new folder in root.
     *      $ wp gdrive mkdir wordpress
     *
     *      # Create a Nested Folder.
     *      $ wp gdrive mkdir wordpress/wp-content/plugins
     *
     * @when before_wp_load
     */
    function mkdir($_, $assoc)
    {
        WP_CLI_Helper::pl_wait_start();
        $folder = WP_CLI_Google_Drive::make_folder_by_path(trim($_[0]));
        WP_CLI_Helper::pl_wait_end();
        if (isset($folder['error'])) {
            WP_CLI::error($folder['message']);
        } else {
            WP_CLI::success("Created folder.");
        }
    }

    /**
     * Restore a file and folder from trash.
     *
     * ## OPTIONS
     *
     * <name>
     * : file or folder name.
     *
     * ## EXAMPLES
     *
     *      # Restore a file from trash.
     *      $ wp gdrive restore backup.zip
     *
     * @when before_wp_load
     */
    function restore($_, $assoc)
    {
        // Show Please Wait
        WP_CLI_Helper::pl_wait_start();

        // Get List Of File in trash
        $files = WP_CLI_Google_Drive::file_list(array('q' => "trashed=true"));

        // Check Error
        if (isset($files['error'])) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error($files['message']);
        }

        // Check Exist file in trash
        $fileId = false;
        foreach ($files as $file) {
            if ($file['name'] == trim($_[0])) {
                $fileId = $file['id'];
            }
        }
        if ($fileId === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("Your file or folder does not exist in the trash.");
        }

        //Restore File
        $restore = WP_CLI_Google_Drive::file_restore(array('fileId' => $fileId));
        WP_CLI_Helper::pl_wait_end();
        if (isset($restore['error'])) {
            WP_CLI::error($restore['message']);
        } else {
            WP_CLI::success("Restored '" . $_[0] . "'.");
        }
    }

    /**
     * Download a file.
     *
     * ## OPTIONS
     *
     * <path>
     * : The path of file for Download from Google Drive.
     *
     * [<saveTo>]
     * : The address where the file will be saved in your machine.
     *
     * [--name=<file_name>]
     * : New file name to save.
     *
     * [--e]
     * : Extract Zip file after downloading.
     *
     * ## EXAMPLES
     *
     *      # Download backup.zip file from root dir in Google Drive.
     *      $ wp gdrive get backup.zip
     *      Success: Download completed.
     *
     *      # Download backup.zip file and save to custom dir.
     *      $ wp gdrive get backup.zip /folder/ --name=package.zip
     *      Success: Download completed.
     *
     *      # Automatic unzip file after download.
     *      $ wp gdrive get backup.zip /folder/ --e
     *      Success: Completed download and extract file.
     *
     * @when before_wp_load
     * @alias dl
     */
    function get($_, $assoc)
    {
        // Show Loading
        WP_CLI_Helper::pl_wait_start();

        // Check SaveTo Path
        if ( ! isset($_[1]) || (isset($_[1]) and (trim($_[1]) == "/" || trim($_[1]) == "\\"))) {
            $saveTo = WP_CLI_Util::getcwd();
        } else {
            $saveTo    = $_[1];
            $path_info = pathinfo($_[1]);
            if (isset($path_info['extension'])) {
                $saveTo = $path_info['dirname'];
            }
            $saveTo = \WP_CLI_FileSystem::path_join(WP_CLI_Util::getcwd(), $saveTo);
        }

        // Check File Exist For Download
        $file = WP_CLI_Google_Drive::get_id_by_path($_[0]);
        if ($file === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error("The '" . $_[0] . "' is not found in your Google Drive.");
        } else {
            // Check is file not folder
            if ($file['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type) {
                WP_CLI_Helper::pl_wait_end();
                WP_CLI::error("Your '" . $_[0] . "' path includes the folder.");
            }
        }

        // Check file is Original OR Google Doc or Not Downloadable.
        if (isset($file['originalFilename'])) {
            $type_file = 'original';
        } else {
            if (isset($file['exportLinks'])) {
                $type_file = 'googleDoc';
            } else {
                WP_CLI_Helper::pl_wait_end();
                WP_CLI::log(WP_CLI_Helper::color("WebViewLink: ", "Y") . $file['webViewLink']);
                exit;
            }
        }

        // Sanitize file name
        $file_name = $file['name'];
        if (isset($assoc['name'])) {
            if ($type_file == "googleDoc") {
                $assoc['name'] = pathinfo($assoc['name'], PATHINFO_FILENAME);
            }
            $file_name = preg_replace(WP_CLI_Google_Drive::$preg_filename, '', $assoc['name']);
        }

        // Set Global File
        if (isset($file['size'])) {
            $GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM'] = array(
                'size' => $file['size'],
                'type' => 'Download'
            );
        }

        // Download Original File
        if ($type_file == "original") {
            WP_CLI_Helper::pl_wait_end();
            $download_file = WP_CLI_Google_Drive::download_original_file(array(
                'fileId'   => $file['id'],
                'path'     => $saveTo,
                'filename' => $file_name,
                'hook'     => array(__CLASS__, "progress")
            ));
            if (isset($download_file['error'])) {
                WP_CLI::error($download_file['message']);
            } else {
                self::save_file(\WP_CLI_FileSystem::path_join($saveTo, $file_name), $assoc);
            }
        } else {
            // Get List Of Export Link
            WP_CLI_Helper::pl_wait_end();
            WP_CLI_Helper::br();
            $i                = 1;
            $export_type_list = array();
            foreach ($file['exportLinks'] as $key => $value) {
                // Get file Extension
                $parse_url = parse_url($value);
                parse_str($parse_url['query'], $url_query);
                $extension = $url_query['exportFormat'];

                // Log
                WP_CLI::line("{$i}. " . WP_CLI_Helper::color($file_name . "." . $extension, "Y"));
                WP_CLI::line(WP_CLI_Helper::color("     MimeType: " . $key, "B"));
                WP_CLI_Helper::br();

                //Push to list
                $export_type_list[$i] = array(
                    'mimeType'  => $key,
                    'extension' => $extension,
                    'filename'  => $file_name . '.' . $extension
                );

                $i++;
            }
            WP_CLI_Helper::br();

            WP_CLI_Util::define_stdin();
            while (true) {
                echo "Please type the number list and press enter key: ";
                $ID = fread(STDIN, 80);
                if (is_numeric(trim($ID)) and $ID <= $i and $ID > 0) {
                    break;
                }
            }
            if (isset($ID)) {
                WP_CLI_Helper::pl_wait_start();

                // Get MimeType For Download
                $file_inf = $export_type_list[(int)$ID];

                // Start Download
                WP_CLI_Helper::pl_wait_end();
                $download_file = WP_CLI_Google_Drive::export_file(array(
                    'fileId'   => $file['id'],
                    'path'     => $saveTo,
                    'filename' => $file_inf['filename'],
                    'mimeType' => $file_inf['mimeType'],
                    'hook'     => array(__CLASS__, "progress")
                ));
                if (isset($download_file['error'])) {
                    WP_CLI::error($download_file['message']);
                } else {
                    self::save_file(\WP_CLI_FileSystem::path_join($saveTo, $file_inf['filename']), $assoc);
                }
            }
        }
    }

    /**
     * Upload a file.
     *
     * ## OPTIONS
     *
     * <path>
     * : The path of file or folder for Upload.
     *
     * [<UploadTo>]
     * : The path dir where the file will be saved in Google Drive.
     *
     * [--name=<file_name>]
     * : New file name to save.
     *
     * [--zip]
     * : Create Zip file before uploading.
     *
     * [--force]
     * : Force upload even if it already exists.
     *
     * ## EXAMPLES
     *
     *      # Upload backup.zip file to root dir in Google Drive.
     *      $ wp gdrive upload backup.zip
     *      Success: Upload completed.
     *
     *      # Automatic create zip archive from the /wp-content/ folder and upload to custom dir.
     *      $ wp gdrive upload /wp-content/ /wordpress/backup --zip
     *      Success: Upload completed.
     *
     *      # Upload with custom name.
     *      $ wp gdrive upload backup.zip --name=wordpress.zip
     *      Success: Upload completed.
     *
     * @when before_wp_load
     * @alias send
     */
    function upload($_, $assoc)
    {
        // Show Please Wait
        WP_CLI_Helper::pl_wait_start();

        // Get Google Cache Path dir
        $cache_dir = WP_CLI_Google_Drive::get_cache_dir();
        if ($cache_dir['status'] === false) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error($cache_dir['message']);
        } else {
            $cache_dir = $cache_dir['path'];
        }

        // Prepare SaveTo Google Drive Path
        $upload_to      = 'root';
        $upload_to_path = '/';
        if (isset($_[1])) {
            // Check is root Google Drive
            if (trim($_[1]) == "/" || trim($_[1]) == "\\" || trim($_[1]) == "root" || trim($_[1]) == "home") {
                $upload_to = 'root';
            } else {
                // Create Dir for Custom Path
                $google_dir = WP_CLI_Google_Drive::make_folder_by_path(trim($_[1]));
                if (isset($google_dir['error'])) {
                    WP_CLI_Helper::pl_wait_end();
                    WP_CLI::error($google_dir['message']);
                }

                // Set Folder ID
                $upload_to_path = ltrim(WP_CLI_Google_Drive::sanitize_path(trim($_[1])), "/");
                $upload_to      = $google_dir['id'];
            }
        }

        // Prepare File Path
        $file_path = \WP_CLI_FileSystem::path_join(WP_CLI_Util::getcwd(), WP_CLI_Google_Drive::sanitize_path(trim($_[0])));
        if ( ! file_exists($file_path)) {
            WP_CLI_Helper::pl_wait_end();
            WP_CLI::error('File Path not found.');
        }
        $path_info = pathinfo($file_path);

        // Check File path is dir or file
        $type = (is_dir($file_path) ? 'dir' : 'file');

        // Upload File
        if ($type == "file") {
            // Check File name
            $filename = basename($file_path);
            if (isset($assoc['name'])) {
                $filename = preg_replace(WP_CLI_Google_Drive::$preg_filename, '', $assoc['name']);
            }

            // Check Zip File
            if (isset($assoc['zip']) and isset($path_info['extension']) and $path_info['extension'] != "zip") {
                // Create Archive From File
                $ZIP = \WP_CLI_FileSystem::zip_archive_file(array(
                    'new_name' => $filename,
                    'saveTo'   => $cache_dir,
                    'filepath' => $file_path
                ));
                if ($ZIP['status'] === false) {
                    WP_CLI_Helper::pl_wait_end();
                    WP_CLI::error($ZIP['message']);
                }

                // Get New file Path
                $file_path = $ZIP['zip_path'];
                $filename  = $ZIP['name'];
            }

            // Check Exist Files in Google Drive
            $gdrive_file_path = rtrim($upload_to_path, "/") . "/" . $filename;
            $_exist           = WP_CLI_Google_Drive::get_id_by_path($gdrive_file_path);
            if ($_exist != false and ! isset($assoc['force'])) {
                self::clear_cache();
                WP_CLI::error("The '" . $gdrive_file_path . "' is now exist in your Google Drive.");
            }

            // Set Global Stream
            $GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM'] = array(
                'size' => filesize($file_path),
                'type' => "Upload $filename"
            );

            // Upload File
            WP_CLI_Helper::pl_wait_end();
            $upload_file = self::curl_upload(array(
                'parentId'  => $upload_to,
                'file_path' => $file_path,
                'new_name'  => $filename
            ));
            self::clear_cache();
            if (isset($upload_file['error'])) {
                WP_CLI::error($upload_file['message']);
            } else {
                WP_CLI::success("Uploaded '$filename' file." . (isset($upload_file['data']['id']) ? WP_CLI_Helper::color(" [fileId: " . $upload_file['data']['id'] . "]", "B") : str_repeat(" ", 20)));
            }
        } else {
            //if dir and Zip Archived Upload
            if (isset($assoc['zip'])) {
                // Check File name
                $filename = basename($file_path);
                if (isset($assoc['name'])) {
                    $filename = preg_replace(WP_CLI_Google_Drive::$preg_filename, '', $assoc['name']);
                }

                // Create Archive From File
                $ZIP = \WP_CLI_FileSystem::create_zip(array(
                    'saveTo'   => $cache_dir,
                    'new_name' => $filename,
                    'source'   => $file_path
                ));
                if ($ZIP['status'] === false) {
                    WP_CLI_Helper::pl_wait_end();
                    WP_CLI::error($ZIP['message']);
                }

                // Get New file Path
                $file_path = $ZIP['zip_path'];
                $filename  = $ZIP['name'];

                // Check Exist Files in Google Drive
                $gdrive_file_path = rtrim($upload_to_path, "/") . "/" . $filename;
                $_exist           = WP_CLI_Google_Drive::get_id_by_path($gdrive_file_path);
                if ($_exist != false and ! isset($assoc['force'])) {
                    self::clear_cache();
                    WP_CLI::error("The '" . $gdrive_file_path . "' is now exist in your Google Drive.");
                }

                // Set Global Stream
                $GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM'] = array(
                    'size' => filesize($file_path),
                    'type' => "Upload $filename"
                );

                // Upload File
                WP_CLI_Helper::pl_wait_end();
                $upload_file = self::curl_upload(array(
                    'parentId'  => $upload_to,
                    'file_path' => $file_path,
                    'new_name'  => $filename
                ));
                self::clear_cache();
                if (isset($upload_file['error'])) {
                    WP_CLI::error($upload_file['message']);
                } else {
                    WP_CLI::success("Uploaded '$filename' file." . (isset($upload_file['data']['id']) ? WP_CLI_Helper::color(" [fileId: " . $upload_file['data']['id'] . "]", "B") : str_repeat(" ", 20)));
                }
            } else {
                // If Upload Any File or Folder
                $_is_upload = false;

                // remove Please Wait
                WP_CLI_Helper::pl_wait_end();

                // Upload All Files From Folder
                $list_files = \WP_CLI_FileSystem::get_dir_contents($file_path, true);
                if (isset($list_files['status']) and $list_files['status'] === false) {
                    WP_CLI::error($list_files['message']);
                }

                // Set Max Upload file in One Request
                if (count($list_files) > 100) {
                    WP_CLI::confirm("There are " . WP_CLI_Helper::color(number_format(count($list_files)), "B") . " files in your folder.Are you sure you want to upload this file number?");
                }

                // Separate Dir From File
                $directory = array();
                $files     = array();
                foreach ($list_files as $file) {
                    if (is_dir($file)) {
                        $directory[] = $file;
                    } else {
                        $files[] = $file;
                    }
                }

                // Show Please wait
                WP_CLI_Helper::pl_wait_start();

                // First Create Folder List
                foreach ($directory as $file) {
                    // Get realpath
                    $real_path = str_ireplace($file_path, "", \WP_CLI_FileSystem::normalize_path($file));

                    // Path in Google Drive
                    $path_in_google_drive = \WP_CLI_FileSystem::path_join($upload_to_path, $real_path);

                    // Check Exist in Google Drive
                    $_exist = WP_CLI_Google_Drive::get_id_by_path($path_in_google_drive);
                    if ($_exist === false) {
                        WP_CLI_Helper::pl_wait_start();
                        $folder = WP_CLI_Google_Drive::make_folder_by_path($path_in_google_drive);
                        WP_CLI_Helper::pl_wait_end();
                        if (isset($folder['error'])) {
                            WP_CLI::error($folder['message']);
                        } else {
                            $_is_upload = true;
                            WP_CLI::line("- Created '$path_in_google_drive' folder.");
                        }
                    }
                }

                // Second Upload files
                foreach ($files as $file) {
                    // Get realpath
                    $real_path = str_ireplace($file_path, "", \WP_CLI_FileSystem::normalize_path($file));

                    // Path in Google Drive
                    $path_in_google_drive = \WP_CLI_FileSystem::path_join($upload_to_path, $real_path);

                    // Check Exist in Google Drive
                    $_exist = WP_CLI_Google_Drive::get_id_by_path($path_in_google_drive);
                    if ($_exist != false and ! isset($assoc['force'])) {
                        WP_CLI::line("- The '" . $path_in_google_drive . "' is now exist.");
                    } else {
                        // Get base File name
                        $filename = basename($file);

                        // Set Global Stream
                        $GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM'] = array(
                            'size' => filesize($file),
                            'type' => "Upload $filename"
                        );

                        // Get Parent ID
                        $Upload_To_Folder = str_ireplace($filename, "", $path_in_google_drive);
                        $_exist           = WP_CLI_Google_Drive::get_id_by_path($Upload_To_Folder);
                        if ($_exist != false) {
                            // Upload File
                            WP_CLI_Helper::pl_wait_end();
                            $upload_file = self::curl_upload(array(
                                'parentId'  => $_exist['id'],
                                'file_path' => $file,
                                'new_name'  => $filename
                            ));
                            if (isset($upload_file['error'])) {
                                WP_CLI::error($upload_file['message']);
                            } else {
                                $_is_upload = true;
                                WP_CLI::line("- Uploaded '$path_in_google_drive' file." . (isset($upload_file['data']['id']) ? WP_CLI_Helper::color(" [fileId: " . $upload_file['data']['id'] . "]", "B") : str_repeat(" ", 20)));
                            }
                        }
                    }
                }

                if ($_is_upload) {
                    WP_CLI::success("Upload completed.");
                }
            }
        }
    }

    public static function clear_cache()
    {
        $clean = WP_CLI_Google_Drive::clear_cache();
        if ( ! $clean['status']) {
            WP_CLI::error($clean['message']);
        }
    }

    private static function list_table($files = array(), $show_status = true)
    {
        // Remove Please Wait
        WP_CLI_Helper::pl_wait_end();

        // Show Table List
        $list = array();
        foreach ($files as $file) {
            // Check Last Modified
            if (isset($file['modifiedTime']) and ! empty($file['modifiedTime'])) {
                $lastModified = self::sanitize_date_time($file['modifiedTime']);
            } else {
                if (isset($file['createdTime']) and ! empty($file['createdTime'])) {
                    $lastModified = self::sanitize_date_time($file['createdTime']);
                } else {
                    $lastModified = "-";
                }
            }

            // Check File Status
            if ($show_status) {
                $status = 'private';
                if (isset($file['permissions'])) {
                    foreach ($file['permissions'] as $permission) {
                        if ($permission['id'] == WP_CLI_Google_Drive::$public_permission_id) {
                            $status = 'public';
                        }
                    }
                }
            }

            // Get Type of file
            if ($file['mimeType'] == WP_CLI_Google_Drive::$folder_mime_type) {
                $type = 'Folder';
            } else {
                if (isset($file['originalFilename']) and ! isset($file['exportLinks'])) {
                    $type = 'File';
                } else {
                    $type = 'Google Doc';
                }
            }

            // Add To List
            $arg_file = array(
                'name'         => WP_CLI_Util::substr($file['name'], 100),
                'type'         => $type,
                'size'         => (isset($file['size']) ? \WP_CLI_FileSystem::size_format($file['size']) : '-'),
                'lastModified' => $lastModified
            );
            if ($show_status and isset($status)) {
                $arg_file['status'] = $status;
            }
            $list[] = $arg_file;
        }

        WP_CLI_Helper::create_table($list);
    }

    private static function sanitize_date_time($time)
    {
        $exp          = explode("T", $time);
        $explode_time = explode(".", $exp[1]);
        return $exp[0] . " " . $explode_time[0];
    }

    public static function save_file($full_path, $assoc)
    {
        $path_info = pathinfo($full_path);
        if (isset($path_info['extension']) and $path_info['extension'] == "zip" and isset($assoc['e'])) {
            echo "Extracting file ...." . str_repeat(" ", 20) . "\r";
            $unzip = \WP_CLI_FileSystem::unzip($full_path);
            if ($unzip === true) {
                \WP_CLI_FileSystem::remove_file($full_path);
                WP_CLI::success("Completed download and extract file." . str_repeat(" ", 15));
            } else {
                WP_CLI::error("Error extracting zip file");
            }
        } else {
            WP_CLI::success("Download Completed." . str_repeat(" ", 10));
        }
    }

    public static function progress($data, $response_bytes, $response_byte_limit)
    {
        if (isset($GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM'])) {
            $p = ceil(round(($response_bytes / $GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM']['size']) * 100, 2));
            echo WP_CLI_Helper::color($GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM']['type'] . ": " . \WP_CLI_FileSystem::size_format($response_bytes, 2) . " / " . \WP_CLI_FileSystem::size_format($GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM']['size']), "Y") . " " . WP_CLI_Helper::color("[$p%]", "B") . "        \r";
        }
    }

    private static function curl_upload($args = array())
    {
        $default = array(
            'access_token' => WP_CLI_Google_Drive::access_token(),
            'parentId'     => '',
            'file_path'    => '',
            'new_name'     => ''
        );
        $arg     = WP_CLI_Util::parse_args($args, $default);

        // Create New Resume Upload Link
        $file = array(
            'name'    => basename($arg['file_path']),
            'parents' => array($arg['parentId'])
        );

        // Check new name for File
        if ( ! empty($arg['new_name'])) {
            $file['name'] = preg_replace(WP_CLI_Google_Drive::$preg_filename, '', $arg['new_name']);
        }

        $request = \WP_CLI\Utils\http_request("POST", WP_CLI_Google_Drive::$UploadUrl . '/files?uploadType=resumable', json_encode($file), array_merge(WP_CLI_Google_Drive::$json_content_type, WP_CLI_Google_Drive::$json_header_request, array('Authorization' => WP_CLI_Google_Drive::$auth_header . ' ' . $arg['access_token'])), array('timeout' => WP_CLI_Google_Drive::$request_timeout));
        if (200 === $request->status_code) {
            // Get Upload Link
            $upload_url = '';
            $per_line   = explode("\n", $request->raw);
            foreach ($per_line as $line) {
                if (substr(strtolower(trim($line)), 0, 8) == "location") {
                    $upload_url = str_ireplace("Location: ", "", $line);
                }
            }

            // Check Empty Upload Url
            if (empty($upload_url)) {
                return array('error' => true, 'message' => "Problem get upload url. please try again.");
            }

            // Sanitize Upload Url
            $upload_url = str_replace(array(' ', "\t", "\n", "\r"), '', $upload_url);

            // Upload File to Google Drive
            $headers   = array();
            $headers[] = "Content-Type: application/json";
            $headers[] = 'Authorization: ' . WP_CLI_Google_Drive::$auth_header . ' ' . $arg['access_token'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_URL, trim($upload_url));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($arg['file_path']));
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array(__CLASS__, "curl_progress"));
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return array('error' => true, 'message' => 'Request Error - ' . curl_error($ch));
            }
            curl_close($ch);
            $jsonData = json_decode($result, true);
            return array('status' => true, 'data' => $jsonData);
        }

        return WP_CLI_Google_Drive::$failed_connecting;
    }

    public static function curl_progress($resource, $download_size, $downloaded, $upload_size, $uploaded)
    {
        if (isset($GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM'])) {
            $p = ceil(round(($uploaded / $GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM']['size']) * 100, 2));
            echo WP_CLI_Helper::color($GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM']['type'] . ": " . \WP_CLI_FileSystem::size_format($uploaded, 2) . " / " . \WP_CLI_FileSystem::size_format($GLOBALS['WP_CLI_GOOGLE_DRIVE_STREAM']['size']), "Y") . " " . WP_CLI_Helper::color("[$p%]", "B") . "            \r";
        }
    }

}