# Set up development environment

## Parts of the development environment
- [xampp](https://www.apachefriends.org/index.html) to get
    - Apache 2.4 or higher
    - PHP 7+
- [PostgreSQL](https://www.postgresql.org/)
    - do not forget to start the service...
- [GIT](https://git-scm.com/) for version control
- [Node.js](http://nodejs.org/), the currently used version is 10.15.3 LTS
- [npm](https://www.npmjs.com/) as Javascript package manager
- [composer](https://getcomposer.org/) as PHP package manager
- [drush](https://github.com/drush-ops/drush-launcher/releases) as Drupal Shell (not neccessary but recommended)

## Prerequisits

In this documentation it is assumed that the system contains an up and running webserver environment with PHP 7+ and PostgreSQL 9.4 or higher.
Based on this the setup of the development environment is described.
The description is made for a Microsoft Windows environment, as this is used by the [Geowerkstatt Hamburg](https://www.hamburg.de/geowerkstatt/).

## Setup the webserver environment locally (Windows)
### Setup XAMPP with PHP 7+

1. Follow these [installation](https://pureinfotech.com/install-xampp-windows-10/#install_xampp_windows10) steps.
2. Configure an Apache 2.4 with PHP 7 or higher.


### Setup PostgreSQL 9.4

1. Download [PostgreSQL](https://www.enterprisedb.com/downloads/postgres-postgresql-downloads) (61.2MB) to your local machine.
2. Run and finish the windows installer as administrator.

At troubleshooting.md you can find a workflow if you have installion trouble with system permissions.


## The basics
Since dipas currently uses Drupal 8, at least one additional software is neccessary to get the local development environment running: Composer.
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

Please do not use a default "cmd"-shell of the windows system nor the so called "powershell".
With both console systems problems are known with the automated application of software-patches by composer.

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

#### Useful drush commands
```
drush status (short: drush st)
```
shows a short status overview if drush can connect with Drupal

```
drush config-export (short: drush cex)
```
exports the currently exisiting configuration of the system to config files

```
drush config-import (short: drush cim)
```
imports the configuration, available in file system, to the database

```
drush cache-rebuild (short: drush cr)
```
flushes all drupal caches

```
drush updatedb (short: drush updb)
```
executes pending update hooks


## Masterportal makes the map
In order to incorporate the Masterportal into dipas a built version of the Masterportal needs to be created.
[See the guide](/masterportal.md)

## Addons
If there are any addons required to be added to the Masterportal those addons need to be cloned from the Addon repository to the ‘addons’ folder of the Masterportal before running the build process so that the addons will be incorporated into the build of the Masterportal.
[See the guide](/addons.md)

# Get the source code

## Repository
The repository contains the drupal part as well as the VueJS part of the project.


## branches
The main development branch is ***dev***.
The stable branch is ***production***.

To contribute to this project, take a branch from dev and do your development. Then a pull request is needed to merge back to dev.

The production branch is filled from dev when new features are ready to ship.
We use [SemVer](http://semver.org/) for versioning. For the versions available see the [tags on our repository](https://bitbucket.org/geowerkstatt-hamburg/beteiligungsmodul2/commits).

## pre-push hooks
To ensure the quality of dipas code, pre-push hooks are used to run some checks before pushing locally edited source code back to the repository.
The following checks are configured as pre-push checks:

- running ESLint
- running Javascript Unittests
- running PHP Unittests


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

# Configure Settings

## Connection to the database
Drupal needs to know the connection to the database to store configurations.

1. Set up a PostgreSQL-Database in your system
2. Edit the file drupal.database-settings.php in folder config to let Drupal know the database

## Connection from frontend to backend
The VueJS Frontend needs to know about the Drupal backend.

1. Copy the file `example.vue.config.local.js` and rename it to `vue.config.local.js`
2. Edit the file `vue.config.local.js` and to let VueJS know the path to your local Drupal backend

## Translation
Currently it is not planned to have a language switcher so that the user can decide which language they like.
But it is possible for the technical admin to translate all texts to the favourite language.

There are several parts where the frontend and the backend will be translated.

### Translating the frontend
Most of the text parts in the frontend are delivred by the configuration from the backend.
Those parts which are directly written in code in VueJS can fully be translated.
Take care of this when you write new code, so that you prepare texts directly for possible translation.

Have a look at the folder src/lang to see how the translation of frontend will be done.


### Translating the backend
Some parts of the backend can be translated in the Dipas configuration by e.g. typing the names of the menu entrys.
Texts which are hardcoded in Drupal will be translated in the modules where they occur.
Each module has a folder files/translations where the \*.po files, holding the translations, are located.
