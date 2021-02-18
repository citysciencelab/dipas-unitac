---
title: Contribute

autonav:
  enable: true
  order: -1
---

> Feel free to contribute in the open source project! Here you will find some hints.

# Contribute

[TOC]

## drupal REST api documentation #
For the communication between the drupal backend and the vueJS frontend you will find some information in the [DrupalRestAPI documentation](DrupalRestAPI.md)

## conventions #
Developing together in an open source project cannot work well without conventions. Please take a look at the [conventions](conventions.md) valid for this project!


## Hint to Masterportal #
The Masterportal is a project of [Geowerkstatt Hamburg](https://www.hamburg.de/geowerkstatt/).

The Masterportal is the map part of dipas. In this project the configuration of the Masterportal is done in the drupal backend within the drupal module 'masterportal'.
Drupal will then provide all necessary files to render the map application with the given configuration.

Dipas uses the Masterportal in two ways.

1.Masterportal as part of the frontend

In the vueJS frontend the map application is implemented as iframe.
The Masterportal is used to show the contributions and to locate new contribution on creation.

2. Masterportal in on-site events

To use the Masterportal in on-site events it has a special configuration to be run on a touch table.
This is a big touchable screen on a table where the visitors can stand around to gather information on the proceeding.

Find more information on the configuration of the Masterportal in [this documentation](masterportal.md) or on site of the [Masterportal](https://www.masterportal.org/).


