# Masterportal makes the map

[TOC]


## Configure layers for your masterportal

The single layers which can be selected in the configuration interface when you define a masterportal instance are defined in a JSON file.
This file can be delivered from a different server or located directly in your DIPAS folder.

You will find an example for a `layerdefinitions.json` file in the masterportal module (\drupal\modules\custom\masterportal\libraries\masterportal\\).

Each layer is defined with an ID, a basis URL and some extra parameters. It is then available in the masterportal configuration interface be selecting the ID or the name from a drop-down list.

The single parameters used in the layerdefinition.json and if they are mandatory or not can be found in the documentation of the Masterportal's [services.json](https://bitbucket.org/geowerkstatt-hamburg/masterportal/src/dev/doc/services.json.md).


## Workflow to incorporate the masterportal map

In order to incorporate a new version of Masterportal into DIPAS a [built version](https://bitbucket.org/geowerkstatt-hamburg/masterportal/src/dev/doc/setupDev.md) of the Masterportal needs to be created.

- The resulting masterportal.js needs to be to be placed in: \drupal\modules\custom\masterportal\libraries\masterportal\js\masterportal.js
- The css and font files need to be placed in:
\drupal\modules\custom\masterportal\libraries\masterportal\css
- Any image files used in the Masterportal need to be placed in:
\drupal\modules\custom\masterportal\libraries\masterportal\img
- Any language files need to be placed in
\drupal\modules\custom\masterportal\libraries\masterportal\locales
- E.g. the German language file is placed in:
\drupal\modules\custom\masterportal\libraries\masterportal\locales\de\common.json
- E.g. the English language file is placed in:
\drupal\modules\custom\masterportal\libraries\masterportal\locales\en\common.json

If you need to include masterportal addons to your system, have a look at the short [description](#addons).


### Necessary hack to make the masterportal work

Currently you need a dirty hack to make the compiled masterportal.js file work in DIPAS environment.

In addition to placing the required files in the correct location within the DIPAS project a path needs to be adjusted in the masterportal.js. This path can be found by searching for ‘mastercode’ and should look similar to "../mastercode/2_5_1_DEV_2020-07-30__17-05-04/". This path should be replaced with the following:
../../../modules/custom/masterportal/libraries/masterportal/

Any further occurrences which may be found by the search by ‘mastercode’ search which may include “/locales/{{lng}}/{{ns}}.json” can be ignored as the path to the language files can be defined in the base Javascript configurations of the Masterportal.


## Addons

If there are any addons required to be added to the Masterportal those addons need to be cloned from the Addon repository to the ‘addons’ folder of the Masterportal before running the build process so that the addons will be incorporated into the build of the Masterportal.

The corresponding files which will be created during the build process for each addon need to be placed in:
*~\drupal\libraries\MasterportalAddons\\*.


