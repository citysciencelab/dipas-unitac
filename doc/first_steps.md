> Only some steps for the first online participation module

# First Steps

[TOC]

## system requirements #

The machine, where the DIPAS module shall be installed, needs the following prerequisits:

* **DNS**: a wildcard certificate for the server is essential to run DIPAS
    * if the server domain is e.g. named beteiligung.hamburg, then a certificate for \*.beteiligung.hamburg is necessary
    * all different proceedings, configured in DIPAS will then be available on an individual subdomain of the server (e.g. my-proceeding.beteiligung.hamburg)
* **PHP** with the configuration described for Drupal 9 on the [drupal site](https://www.drupal.org/docs/system-requirements/php-requirements)
* **Apache (or another webserver)** with a configuration described for Drupal 9 on the [drupal site](https://www.drupal.org/docs/system-requirements/web-server-requirements#s-apache)
    * currently we use [Apache 2.4](https://httpd.apache.org/docs/2.4/en/)
    * additionally to the above mentioned Apache settings a vhost must be configured for the wildcard domain (see DNS) and point to the document root
        * Servername beteiligung.hamburg
        * Serveralias \*.beteiligung.hamburg
        * DocumentRoot /var/www/dipas-domain (use the root folder of your DIPAS installation here!)
* **PostgreSQL** with the prerequisits described for Drupal 9 on the [drupal site](https://www.drupal.org/docs/system-requirements/database-server-requirements)
   * currently we use PostgreSQL 13.4

## download the example #
1. to set up a first example please download the latest version from the [repository](https://bitbucket.org/geowerkstatt-hamburg/dipas/downloads/)

2. to be able to publish the DIPAS module to the world, it must be provided on a webserver. To do so copy the zip file to the webserver (e.g. to the htdocs folder of an Apache webserver) and unpack it there.

3. the folder dipas contains the following structure

  - config/
    - export/
    - local/
    - sync/
    - de.po
    - drupal.database-settings.php
    - drupal.salt.inc.php
  - css/
    - style.css
  - drupal/
    - core/
    - files/
    - libraries/
    - modules/
    - sites/
  - fonts/
  - img/
  - js/
    - bundle.js
    - bundle.js.map
  - private/
  - tmp/
  - vendor/
  - index.html
  - 0-home-np.ico
  - themeconfig.json

  The folder *config* contains the drupal configuration settings and a German translation file which can be imported to drupal after the initial installation.
  The *drupal.database-settings.php* holds the database connection information.

  The folder *css* contains the frontend styling.

  The file *themeconfig.json* allows to change the main color and some logos for the frontend styling.

  The folder *drupal* contains the drupal part of the DIPAS project. Settings which are related to the special environment can be made in file *\drupal\sites\default\settings.php*.

  The folders *fonts* and *img* contain fonts and images, used for the frontend styling.

  The folder *js* contains a compressed javascript source code of the frontend.

  The folders *private* and *tmp* are used by the drupal backend to store data.

  The folder *vendor* contains dependencies used by the drupal part of the DIPAS project.



## install DIPAS for the first time #
To have an easier handling for the technical admin you can do a setting to be always logged in in all proceedings.
To do so, open *\drupal\sites\default\services.yml* and change the parameter for cookie_domain to your local domain.
Important is to have a leading dot in the name of the domain.


To then initially set up the DIPAS module, follow the steps in the [INSTALL.md](https://bitbucket.org/geowerkstatt-hamburg/dipas/src/dev/INSTALL.md)


## Translate the user interface #
Currently it is not planned to have a language switcher so that the user can decide which language they like.
But it is possible for the technical admin to translate all texts to the favourite language.

There are several parts where the frontend and the backend will be translated.

### Translating the frontend
Most of the text parts in the frontend are delivered by the configuration from the backend.
Those parts which are directly written in code in VueJS can fully be translated.

Have a look at the folder src/lang to see how the translation of frontend will be done.


### Translating the backend
Some parts of the backend can be translated in the Dipas configuration by e.g. typing the names of the menu entries.
Texts which are hardcoded in Drupal will be translated by importing \*.po files.

- If necessary you can import the german translation which comes with the download of DIPAS
- at /admin/config/regional/translate/import you can import the file de.po which is located in your htdocs folder at drupal/config/
- choose all three checkboxes and then press import


## Enter a first set of data #

Now you are finished to enter a first set of data.
You will find detailed information on the first steps to set up your new DIPAS system in the [Wiki](https://wiki.dipas.org/index.php/Verfahrensvorlage_erstellen)

## Configure Cron #

- as last point you need to configure cron
   - you find the external URL for the drupal cron at ***Configuration/System/Cron***
   - take the cryptic URL and add it to a scheduled task (windows) or a cron job (linux) to run the drupal cron regularly (~every 10 minutes)
- cron will do several things
   - clean your temporary files and vacuum the database
   - check whether your projects are still in the active phase (configured in the projects information settings)
   - when the project period is over, drupal will automatically close contributions and rating and change the start page for the public users
   - if NLP (natural language processing) is configured, cron will check if contributions have changed and will then recalculate the NLP data of your project

