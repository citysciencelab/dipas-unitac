# Set up development environment

## Parts of the development environment
- [xampp](https://www.apachefriends.org/index.html) to get
    - Apache 2.4 or higher
    - PHP 8+
- [PostgreSQL](https://www.postgresql.org/)
    - do not forget to start the service...
- [GIT](https://git-scm.com/) for version control
- [Node.js](http://nodejs.org/), the currently used version is 10.15.3 LTS
- [npm](https://www.npmjs.com/) as Javascript package manager
- [composer](https://getcomposer.org/) as PHP package manager
- [drush](https://github.com/drush-ops/drush-launcher/releases) as Drupal Shell (not neccessary but recommended)



## Prerequisits

In this documentation it is assumed that the system contains an up and running webserver environment with PHP 8+ and PostgreSQL 13.0 or higher.
Based on this the setup of the development environment is described.
The description is made for a Microsoft Windows environment, as this is used by the [Geowerkstatt Hamburg](https://www.hamburg.de/geowerkstatt/).

## Setup the webserver environment locally (Windows)
### Setup XAMPP with PHP 8+

1. Follow these [installation](https://pureinfotech.com/install-xampp-windows-10/#install_xampp_windows10) steps.
2. Configure an Apache 2.4 with PHP 8 or higher.


### Setup PostgreSQL 13.4

1. Download [PostgreSQL](https://www.enterprisedb.com/downloads/postgres-postgresql-downloads) (61.2MB) to your local machine.
2. Run and finish the windows installer as administrator.

At troubleshooting.md you can find a workflow if you have installion trouble with system permissions.


## The basics
Since dipas currently uses Drupal 9, at least one additional software is neccessary to get the local development environment running: Composer.
The Drupal shell "drush" is highly recommended since it improves the ease of working with Drupal by giving shorthand commands for several tasks.

### Setup drush & composer

### PHP-executable available in PATH
To get tools like composer and drush working it is essential to make a PHP-executable available in PATH variable.
Therefore add the path to PHP (possibly installed with Xampp) to your environment variable PATH.

Check if it was successful by typing in a console window:
```
php -v
```


### Installation of Composer
The download section of [composer](https://getcomposer.org/) contains an easy to use Windows installer.
To run it administrator permissions are probably required.

Check if it was successful by typing in a console window:
```
composer -version
```

We use git bash for our local development environment.
If you prefer to use a different shell, check if the automated application of software-patches by composer is working correctly.
Probably patch.exe must be available in the PATH environment variable.

### Installation of drush library

For drush not the command itself is installed but a "launcher". This will then call the drush-library of the current project.

- Create a subfolder "drush" in the user-folder.
- Download the files **drush.phar** and **drush.version** from [https://github.com/drush-ops/drush-launcher/releases](https://github.com/drush-ops/drush-launcher/releases)
- Copy both files to the new drush-folder.
- Rename drush.phar to drush (remove the file ending)
- Create a file drush.bat in the drush-folder
- add following content to the file drush.bat
```
@echo off
php "%~dp0\drush" %*
```
- add the path to drush folder to the PATH environment variable to make it available in all console windows

Check if it was successful by typing in a console window:
```
drush version
```

# Get the source code

## Repository
The repository contains the drupal part as well as the VueJS part of the project: https://bitbucket.org/geowerkstatt-hamburg/dipas/


## branches
The main development branch is ***dev***.
The stable branch is ***production***.

To contribute to this project, take a branch from dev and do your development. Then a pull request is needed to merge back to dev.

The production branch is filled from dev when new features are ready to ship.
We use [SemVer](http://semver.org/) for versioning. For the versions available see the [tags on our repository](https://bitbucket.org/geowerkstatt-hamburg/dipas/commits).


# Configure local settings

## Connection to the database
Drupal needs to know the connection to the database to store configurations.
Edit the file drupal.database-settings.php in folder config to let Drupal know the database

## Connection from frontend to backend
The VueJS Frontend needs to know about the Drupal backend.

1. Copy the file `example.vue.config.local.js` and rename it to `vue.config.local.js`
2. Edit the file `vue.config.local.js` to let VueJS know the path to your local Drupal backend


# Set up the project

## for the ***drupal*** part do
```
composer install
```


## for the ***VueJS*** part do
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Run your tests
```
npm run test
```

### Lints and fixes files
```
npm run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).


