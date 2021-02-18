> Only some steps for the first online participation module

# First Steps

[TOC]

## system requirements #

The machine, where the DIPAS module shall be installed, needs the following prerequisits:

* **PHP** with the configuration descriped on the [drupal site](https://www.drupal.org/docs/8/system-requirements/php-requirements)
* **Apache (or another webserver)** with a configuration described on the [drupal site](https://www.drupal.org/docs/8/system-requirements/web-server#s-apache)
   * currently we use [Apache 2.4](https://httpd.apache.org/docs/2.4/en/)
* **PostgreSQL** with the prerequisits described on the [drupal site](https://www.drupal.org/docs/8/system-requirements/database-server)
   * currently we use PostgreSQL 9.6.18

## download the example #
1. to set up a first example please download the example zip file from the [repository](https://bitbucket.org/geowerkstatt-hamburg/beteiligungsmodul2/downloads/examples_beta_20191209.zip)

2. to be able to publish the DIPAS module to the world, it must be provided on a webserver. To do so copy the zip file to the webserver (e.g. to the htdocs folder of an Apache webserver) and unpack it there.

3. the folder examples contains the following structure

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

	The folder *config* contains the drupal configuration settings and a German translation file which can be imported to drupal after the initial installation.
	The *drupal.database-settings.php* holds the database connection information.

	The folder *css* contains the frontend styling.

	The folder *drupal* contains the drupal part of the DIPAS project. Settings which are related to the special environment can be made in file *\drupal\sites\default\settings.php*.

	The folders *fonts* and *img* contain fonts and images, used for the frontend styling.

	The folder *js* contains a compressed javascript source code of the frontend.

	The folders *private* and *tmp* are used by the drupal backend to store data.

	The folder *vendor* contains dependencies used by the drupal part of the DIPAS project.



## initial set up #
To have an easier handling for the technical admin you can do a setting to be always logged in in all proceedings.
To do so, open *\drupal\sites\default\services.yml* and change the parameter for cookie_domain to your local domain.
Important is to have a leading dot in the name of the domain.


To then initially set up the DIPAS module, follow the steps in the [INSTALL.md](https://bitbucket.org/geowerkstatt-hamburg/beteiligungsmodul2/src/dev/INSTALL.md)




