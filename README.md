# WP-CLI Google Drive Command

Use Google Drive Storage in WordPress WP-CLI Command line for Backup/Restore Files.

<p align="center">
<img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/logo.png" alt="WP-CLI Google Drive">
</p>

## Installation

you can install this package with `wp package install wp-packagist/wp-cli-gdrive-command`
Installing this package requires WP-CLI v2 or greater. Update to the latest stable release with `wp cli update`.

### Authenticate Users

Step 1 : Go to [Google Developers console](https://console.developers.google.com/) and Create a new project

<p align="center">
<img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-1.jpg" alt="Create new Project in Google Console">
 <br>
</p>
 
 Step 2 : Create OAuth client ID
 
 <p align="center">
 <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-2.jpg" alt="OAuth client ID in Google developer">
 <br>
 </p>
 
 you can select Other type for project.
 
  <p align="center">
  <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-3.jpg" alt="OAuth client ID in Google developer">
 <br>
  </p>
  
  then copy your Client Id and Client secret.
  
   <p align="center">
    <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-4.jpg" alt="Get Client Id and Client Secret">
 <br>
    </p>
  
  Step 3 : run command and enter your Client id and Client secret.
  
  ```
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

```
wp gdrive ls
```

for show list of files from custom path e.g /wordpress/backup 

```
wp gdrive ls /wordpress/backup
```

### Create folder in Google Drive

For create a folder use :

```
wp gdrive mkdir <path>
```

create `backup` folder in root directory:

```
wp gdrive mkdir backup
```

#### Nested Directory

you can also create nested dir. for example:

```
wp gdrive mkdir wordpress/new-project/backup
```

### Download File

```
wp gdrive get <path> <save-to> [--name=new_name] [--e]
```

Download backup.zip file from root dir in Google Drive:

```
wp gdrive get backup.zip
```

Download backup.zip file and save to custom dir with `package.zip` name:

```
wp gdrive get backup.zip /folder/ --name=package.zip
```

#### Auto Extract after download

Automatic unzip file after download: (use --e flag)
Download Backup.zip file and extract to /folder/.

```
wp gdrive get backup.zip /folder/ --e
```

### Copy files or Folder

```
wp gdrive copy <path> <new-path>
```

> `new-path` is only new dir path.

for example copy file:

```
wp gdrive copy /backup/wp.zip /folder/custom/
```

or copy folder:

```
wp gdrive copy /folder/name/ /custom
```

### Move files or Folder

```
wp gdrive mv <path> <new-path>
```

or

```
wp gdrive move <path> <new-path>
```

for example:

```
wp gdrive move /folder/wordpress.zip /folder/custom/
```

### Remove Files or folder

```
wp gdrive rm <path> [--trash] [--force]
```

or

```
wp gdrive remove <path> [--trash] [--force]
```

Path    : files or folder path e.g. /backup/wp.zip
--trash : Move file to trash.
--force : Force removing file and folder without question.

for example remove wordpress.zip file in root directory and move to trash:

```
wp gdrive rm wordpress.zip --trash
```

### Rename a file or folder

```
wp gdrive ren <path> <new-name>
```

or

```
wp gdrive rename <path> <new-name>
```

for example, rename wp.zip files that stored in backup folder to wordpress.zip:

```
wp gdrive ren /backup/wp.zip wordpress.zip
```

rename a folder:

```
wp gdrive ren /folder/folder/ new_folder_name
```

### Get Share Link For a files or folder

if you want share a files or folder , and get public link use:

```
wp gdrive share <path>
```

for example , get download link /backup/wp.zip file:

```
wp gdrive share /backup/.zip
```

### Private a files or folder

after download a files or folder by others, you can private again file or folder:

```
wp gdrive private <path>
```

for example , disable download link /backup/wp.zip file:

```
wp gdrive private /backup/.zip
```

### List of files and folder in trash

For showing list of all files and folders in Google Drive Trash:

```
wp gdrive trash
```

#### Clear all files in trash

```
wp gdrive trash --clear
```

### Restore Files or folder

use this command:

```
wp gdrive restore <path>
```

for example, restore `backup` folder from Google drive trash:

```
wp gdrive restore /backup/
```

### Get Your Storage

for get your Storage:

```
wp gdrive storage
```

or

```
wp gdrive about
```

### Upload Files or Folder

```
 wp gdrive upload <path> [<UploadTo>] [--name=<file_name>] [--zip] [--force]
```

<path>
 : The path of file or folder for Upload.
 
[<UploadTo>]
: The path dir where the file will be saved in Google Drive.

[--name=<file_name>]
: New file name to save.

[--zip]
: Create Zip file before uploading.

[--force]
: Force upload even if it already exists.


Upload backup.zip file to root dir in Google Drive:

```
wp gdrive upload backup.zip
```

Automatic create zip archive from the /wp-content/ folder and upload to custom dir:

```
wp gdrive upload /wp-content/ /wordpress/backup --zip
```

Upload with custom name.

```
wp gdrive upload backup.zip --name=wordpress.zip
```

Get Backup From WordPress Database and Upload to Google Drive:

```
wp db export backup.sql
wp gdrive upload backup.sql /backup/wordpress
```

## Author







## Contributing

We appreciate you taking the initiative to contribute to this project.
Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.
