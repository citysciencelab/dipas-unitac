> The DIPAS project is a combination of different frameworks. Lean here, how they interact

# Project structure
In DIPAS we use a headless drupal in combination with a VueJS frontend. The communication between both components works with a REST API interface.
Data are hold in a PostgreSQL database.

[TOC]

## headless drupal
Drupal Version 9 is used as backend to configure the DIPAS project.
Three new drupal modules were created during the development.

- module **dipas**
    - this module gives configuration possibilities to the project administrator
	  - it experimentally includes a keywords service to allow the automatical keyword generation for given texts
    - also natural language processing (NLP) is experimentally implemented
    - data for external applications will be provided via PDS API interface

- module **masterportal**
    - this module gives configuration possibilities for the masterportal
    - masterportal is an open source project of [Geowerkstatt Hamburg](https://www.hamburg.de/geowerkstatt/) which is included in DIPAS

- module **domain_dipas**
    - this module allows the usage of the drupal module **domain** together with DIPAS
    - the DIPAS implementation of the domain module allows to set up several proceedings parallelly with very little effort but granular access rights


## PostgreSQL database
DIPAS is used with a PostgreSQL database, currently version 13.4

## VueJS frontend
The DIPAS frontend is created with [VueJS](https://vuejs.org/) and uses vue-cli.

## Communication
The communication between the front- and the backend works with a REST API.
You will find a [documentation](DrupalRestAPI.md) here.
