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
</p>
 
 Step 2 : Create OAuth client ID
 
 <p align="center">
 <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-2.jpg" alt="OAuth client ID in Google developer">
 </p>
 
 you can select Other type for project.
 
  <p align="center">
  <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-3.jpg" alt="OAuth client ID in Google developer">
  </p>
  
  then copy your Client Id and Client secret.
  
   <p align="center">
    <img src="https://raw.githubusercontent.com/wp-packagist/wp-cli-gdrive-command/master/screenshot/step-4.jpg" alt="Get Client Id and Client Secret">
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

### 


if your search results from more than one item.
for example :

````
wp reference wp_insert_post
````

You will see a list to choose from.

![](https://raw.githubusercontent.com/mehrshaddarzi/wp-cli-reference-command/master/screenshot-2.jpg)

### Custom Search

by default WP_CLI reference package search between all WordPress class and functions.

if you want the custom search :

````
wp reference --class=wp_user
````

or

````
wp reference --funcion=wp_insert_post
````

or

````
wp reference --method=get_row
````

or

````
wp reference --hook=admin_footer
````


### Show in Web Browser

you can show WordPress code reference in Web browser after search with :

````
wp reference --browser
````

### Cache system

by default, WP-CLI cached 100 last searches for speed result. if you want to remove reference cache :

````
wp cli cache clear
````

if you want only remove reference cache :

````
wp reference --clear
````

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.
