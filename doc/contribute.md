---
title: Contribute

autonav:
  enable: true
  order: -1
---

> Feel free to contribute in the open source project! Here you will find some hints.

# Contribute

[TOC]

## Setting up dev environment #
To initially set up the dev environment to contribute to DIPAS follow the instructions in [setup_dev_environment.md](setup_dev_environment.md).

## Get to know our conventions #
Developing together in an open source project cannot work well without conventions. Please take a look at the [conventions](conventions.md) valid for this project!

## pre-push hooks
To ensure the quality of DIPAS code, pre-push hooks are used to run some checks before pushing locally edited source code back to the repository.
The following checks are configured as pre-push checks:

- running ESLint
- running Javascript Unittests
- running PHP Unittests

## drupal REST api documentation #
For the communication between the drupal backend and the vueJS frontend you will find some information in the [DrupalRestAPI documentation](DrupalRestAPI.md)

## configuration export from drupal #
**Changing the drupal configurations with drush**

Configuration changes in DIPAS can be exported using [drush](setup_dev_environment.md#useful-drush-commands) to hold them in the repository.
Make sure that the admin interface is used in english before exporting configuration files! Otherwise it may result in language chaos.

- import the current configurations **!!BEFORE!!** you change the configuration. (drush cim)
- ensure language is set to **english**
- make your changes
- export the new configuration (drush cex)
- Move all files from /config/export to /config/sync
- use "git add && git commit" to apply changes


## Hint to Masterportal #
The Masterportal is a project of [Geowerkstatt Hamburg](https://www.hamburg.de/geowerkstatt/).

The Masterportal is the map part of dipas. In this project the configuration of the Masterportal is done in the drupal backend within the drupal module 'masterportal'.
Drupal will then provide all necessary files to render the map application with the given configuration.

Dipas uses the Masterportal in two ways.

1. Masterportal as part of the frontend

In the vueJS frontend the map application is implemented as iframe.
The Masterportal is used to show the contributions and to locate new contribution on creation.

2. Masterportal in on-site events

To use the Masterportal in on-site events it has a special configuration to be run on a touch table.
This is a big touchable screen on a table where the visitors can stand around to gather information on the proceeding.
Read more about DIPAS for on-site events in the [Wiki](https://wiki.dipas.org/index.php/Hauptseite#DIPAS_Touchtable)

Find more information on the configuration of the Masterportal in [this documentation](masterportal.md) or on site of the [Masterportal](https://www.masterportal.org/).


## Translate the user interface #
Currently it is not planned to have a language switcher so that the user can decide which language they like.
But it is possible for the technical admin to translate all texts to the favourite language.

There are several parts where the frontend and the backend will be translated.

### Translating the frontend
Most of the text parts in the frontend are delivered by the configuration from the backend.
Those parts which are directly written in code in VueJS can fully be translated.
Take care of this when you write new code, so that you prepare texts directly for possible translation.

Have a look at the folder src/lang to see how the translation of frontend will be done.


### Translating the backend
Some parts of the backend can be translated in the Dipas configuration by e.g. typing the names of the menu entries.
Texts which are hardcoded in Drupal will be translated in the modules where they occur.
Each module has a folder files/translations where the \*.po files, holding the translations, are located.


### Useful drush commands
```
drush status (short: drush st)
```
shows a short status overview if drush can connect with Drupal

```
drush config-export (short: drush cex)
```
exports the currently exisiting configuration of the system to config files
**WARNING!** Make sure that the admin interface is used in **english** before exporting configuration files. Otherwise it may result in language chaos!

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


