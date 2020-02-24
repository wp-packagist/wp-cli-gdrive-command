# WP-CLI Google Drive Command

Use Google Drive Storage in WordPress WP-CLI Command line for Backup/Restore Files.

[![Build Status](https://travis-ci.com/wp-packagist/wp-cli-gdrive-command.svg?branch=master)](https://travis-ci.com/wp-packagist/wp-cli-gdrive-command)
![GitHub](https://img.shields.io/github/license/wp-packagist/wp-cli-gdrive-command.svg)
![GitHub repo size](https://img.shields.io/github/repo-size/wp-packagist/wp-cli-gdrive-command.svg)
![GitHub release](https://img.shields.io/github/release/wp-packagist/wp-cli-gdrive-command.svg?style=social)

<br>
<p align="center">
<img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/logo.png" alt="WP-CLI Google Drive">
</p>

- [WP-CLI Google Drive Command](#wp-cli-google-drive-command)
  * [Installation](#installation)
    + [Authenticate Users](#authenticate-users)
  * [Structure](#structure)
  * [Commands](#commands)
    + [List of files and folder](#list-of-files-and-folder)
    + [Create folder in Google Drive](#create-folder-in-google-drive)
      - [Nested Directory](#nested-directory)
    + [Download File](#download-file)
      - [Auto Extract after download](#auto-extract-after-download)
    + [Copy files or Folder](#copy-files-or-folder)
    + [Move files or Folder](#move-files-or-folder)
    + [Remove Files or folder](#remove-files-or-folder)
    + [Rename a file or folder](#rename-a-file-or-folder)
    + [Get Share Link For a files or folder](#get-share-link-for-a-files-or-folder)
    + [Private a files or folder](#private-a-files-or-folder)
    + [List of files and folder in trash](#list-of-files-and-folder-in-trash)
      - [Clear all files in trash](#clear-all-files-in-trash)
    + [Restore Files or folder](#restore-files-or-folder)
    + [Get Your Storage](#get-your-storage)
    + [Upload Files or Folder](#upload-files-or-folder)
  * [Author](#author)
  * [Contributing](#contributing)
  
  
## Installation

you can install this package with:

```console
wp package install wp-packagist/wp-cli-gdrive-command
```

> Installing this package requires WP-CLI v2 or greater. Update to the latest stable release with `wp cli update`.

### Authenticate Users

Step 1: Go to [Google Developers console](https://console.developers.google.com/) and Create a new project

<p align="center">
<img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-1.jpg" alt="Create new Project in Google Console">
 <br>
</p>
 
 Step 2: Create an OAuth client ID
 
 <p align="center">
 <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-2.jpg" alt="OAuth client ID in Google developer">
 <br>
 </p>
 
You can select Other types for the project.
 
  <p align="center">
  <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-3.jpg" alt="OAuth client ID in Google developer">
 <br>
  </p>
  
  Then copy your Client Id and Client secret.
  
   <p align="center">
    <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-4.jpg" alt="Get Client Id and Client Secret">
 <br>
    </p>
  
  Step3 : run command and enter your Client id and Client secret.
  
  ```console
  wp gdrive auth
  ```
  
  > if you want change gmail account that saved before. use 'wp gdrive auth --force'.

## Structure

```
NAME

  wp gdrive

DESCRIPTION

  Google Drive Cloud Storage.

SYNOPSIS

  wp gdrive <command>

SUBCOMMANDS

  auth         Verify user identity on Google.
  copy         Copy a file or folder.
  get          Download a file.
  ls           List of files and folder.
  mkdir        Create folder in Google Drive.
  move         Move a file or folder.
  private      Private a file or folder.
  rename       Rename a file or folder.
  restore      Restore a file and folder from trash.
  rm           Remove File or folder By Path.
  share        Get Download Link a file or folder.
  storage      Verify user identity on Google.
  trash        List of files and folder in trash.
  upload       Upload a file.
```

## Commands

List of WP-CLI gdrive Commands :

### List of files and folder

For show list all files and folder in root dir :

```console
wp gdrive ls
```

For show list of files from custom path e.g /wordpress/backup 

```console
wp gdrive ls /wordpress/backup
```

### Create a folder in Google Drive

For creating  a folder use :

```console
wp gdrive mkdir <path>
```

create `backup` folder in the root directory:

```console
wp gdrive mkdir backup
```

#### Nested Directory

you can also create nested dir. for example:

```console
wp gdrive mkdir wordpress/new-project/backup
```

### Download File

```console
wp gdrive get <path> <save-to> [--name=new_name] [--e]
```

Download backup.zip file from root dir in Google Drive:

```console
wp gdrive get backup.zip
```

Download backup.zip file and save to custom dir with `package.zip` name:

```console
wp gdrive get backup.zip /folder/ --name=package.zip
```

#### Auto Extract after download

Automatic unzip file after download: (use --e flag)
Download Backup.zip file and extract to /folder/.

```console
wp gdrive get backup.zip /folder/ --e
```

### Copy files or Folder

```console
wp gdrive copy <path> <new-path>
```

> `new-path` is only new dir path.

for example copy file:

```console
wp gdrive copy /backup/wp.zip /folder/custom/
```

or copy folder:

```console
wp gdrive copy /folder/name/ /custom
```

### Move files or Folder

```console
wp gdrive mv <path> <new-path>
```

or

```console
wp gdrive move <path> <new-path>
```

For example:

```console
wp gdrive move /folder/wordpress.zip /folder/custom/
```

### Remove Files or folder

```console
wp gdrive rm <path> [--trash] [--force]
```

or

```console
wp gdrive remove <path> [--trash] [--force]
```

Path: files or folder path e.g. /backup/wp.zip
--trash: Move file to trash.
--force: Force removing file and folder without question.

For example remove wordpress.zip file in root directory and move to trash:

```console
wp gdrive rm wordpress.zip --trash
```

### Rename a file or folder

```console
wp gdrive ren <path> <new-name>
```

or

```console
wp gdrive rename <path> <new-name>
```

For example, rename wp.zip files that stored in backup folder to wordpress.zip:

```console
wp gdrive ren /backup/wp.zip wordpress.zip
```

Rename a folder:

```console
wp gdrive ren /folder/folder/ new_folder_name
```

### Get Share Link For a files or folder

If you want to share files or folder and get public link use:

```console
wp gdrive share <path>
```

for example, get download link /backup/wp.zip file:

```console
wp gdrive share /backup/wp.zip
```

### Private a files or folder

after download files or folder by others, you can private again file or folder:

```console
wp gdrive private <path>
```

for example, disable download link /backup/wp.zip file:

```console
wp gdrive private /backup/wp.zip
```

### List of files and folder in the trash

For showing list of all files and folders in Google Drive Trash:

```console
wp gdrive trash
```

#### Clear all files in the trash

```console
wp gdrive trash --clear
```

### Restore Files or folder

Use this command:

```console
wp gdrive restore <path>
```

for example, restore `backup` folder from Google drive trash:

```console
wp gdrive restore /backup/
```

### Get Your Storage

For getting your Storage:

```console
wp gdrive storage
```

or

```console
wp gdrive about
```

### Upload Files or Folder

```console
 wp gdrive upload [<path>] [<UploadTo>] [--name=<file_name>] [--zip] [--force]
```

path
 : The path of file or folder for Upload. `default is current path`
 
UploadTo
: The path dir where the file will be saved in Google Drive.

--name
: New file name to save.

--zip
: Create Zip file before uploading.

--force
: Force upload even if it already exists.

---

Automatic create zip file archive file from all root directory and files and upload to GDrive:

```console
wp gdrive upload --zip
```

Upload backup.zip file to root dir in Google Drive:

```console
wp gdrive upload backup.zip
```

Automatic create zip archive from the /wp-content/ folder and upload to custom dir:

```console
wp gdrive upload /wp-content/ /wordpress/backup --zip
```

Upload All files from `/wp-content/plugins/my-plugin/docs` to `wordpress/plugin` directory in Google Drive

```console
wp gdrive upload /wp-content/plugins/my-plugin/docs/ /wordpress/plugin --zip
```

> Number Max files in One Request is 100 files


Upload with a custom name.

```console
wp gdrive upload backup.zip --name=wordpress.zip
```

Get Backup From WordPress Database and Upload to Google Drive:

```console
wp db export backup.sql
wp gdrive upload backup.sql /backup/wordpress
```

## Author

- [Mehrshad Darzi](https://www.linkedin.com/in/mehrshaddarzi/) | PHP Full Stack and WordPress Expert

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.
Before you create a new issue, you should [search existing issues](https://github.com/wp-packagist/wp-cli-gdrive-command/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/wp-packagist/wp-cli-gdrive-command/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, please follow our guidelines for creating a pull request to make sure it's a pleasant experience:

1. Create a feature branch for each contribution.
2. Submit your pull request early for feedback.
3. Follow [PSR-2 Coding Standards](http://www.php-fig.org/psr/psr-2/).
