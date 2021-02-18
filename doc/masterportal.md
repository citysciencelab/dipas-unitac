# Masterportal makes the map
## Workflow to incorporate the masterportal map

If you need to include masterportal addons to your system, have a look at the short [description](addons.md).

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


## Necessary hack to make the masterportal work

Currently you need a dirty hack to make the compiled masterportal.js file work in DIPAS environment.

In addition to placing the required files in the correct location within the DIPAS project a path needs to be adjusted in the masterportal.js. This path can be found by searching for ‘mastercode’ and should look similar to "../mastercode/2_5_1_DEV_2020-07-30__17-05-04/". This path should be replaced with the following:
../../../modules/custom/masterportal/libraries/masterportal/

Any further occurrences which may be found by the search by ‘mastercode’ search which may include “/locales/{{lng}}/{{ns}}.json” can be ignored as the path to the language files can be defined in the base Javascript configurations of the Masterportal.


## Configure layers for your masterportal

The single layers which can be selected in the configuration interface when you define a masterportal instance are defined in a JSON file.
This file can be delivered from a different server or located directly in your DIPAS folder.

You will find an example for a `layerdefinitions.json` file in the masterportal module (\drupal\modules\custom\masterportal\libraries\masterportal\\).

Each layer is defined with an ID, a basis URL and some extra parameters. It is then available in the masterportal configuration interface be selecting the ID or the name from a drop-down list.

The single parameters used in the layerdefinition.json and if they are mandatory or not can be found in the documentation of the Masterportal's [services.json](https://bitbucket.org/geowerkstatt-hamburg/masterportal/src/dev/doc/services.json.md).
