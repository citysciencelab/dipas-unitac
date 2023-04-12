# Update Guide
This document contains a general guide how to perform updates of the project as well as a guide per version with extra steps
needed to go from version to version. Please note that updates are only supported between minor versions, or the latest minor
to the next major version!

## General
The general procedure works as follows:

0. **Backup** all files and the database to prevent data loss
1. Copy `config/drupal.database-settings.php`, `config/drupal.salt.inc.php` & `drupal/sites/default/files` to a temporary location for later use
2. Delete the complete folder used as web root containing the project
3. Recreate the web root folder
4. Extract the contents of the new ZIP into the created folder
5. Copy `config/drupal.database-settings.php`, `config/drupal.salt.inc.php` & `drupal/sites/default/files` back to its original locations
6. Navigate to `YOURDOMAIN.TLD/drupal/admin/config/development/configuration`
7. Scroll to the bottom and click *Import all*
8. Repeat step 7 (Reason: config_ignore settings may have been updated and necessary configuration updates may have been skipped in the first run)
9. Navigate to `YOURDOMAIN.TLD/drupal/update.php`& follow the wizard

### From 2.0.0 or higher to 2.2.0
**To update to this version some important steps are neccessary:**

1. before you follow step 6. in the update procedure description navigate to `YOURDOMAIN.TLD/drupal/admin/config/development/configuration/full/import` 
  1.1. open tab *"single item"*
  1.2. choose Configuration type *"Simple configuration"*
  1.3. type Configuration name *"config_ignore.settings"*
  1.4. paste the complete content of the file `config\sync\config_ignore.settings.yml` in the text field
  1.5. import the single configuration by pressing the button von bottom of the page
2. then go on with the update procedure as described [above](#general)
3. after having finished the update procedure some settings in Masterportal configuration need to be edited manually
  3.1. for each proceeding the Masterportal instance *"Create Appointment"* needs to be edited
  3.2. go to *"Portal Settings"* and remove the checkmarks for *"Use setMarker"* and *"Center the map around a marker set"*
  3.3. go to *"Tool plugins"* and enable *"Draw"*-tool, then uncheck *"Draw-Tool is visible in Masterportal menu"*
4. flush all caches

There are new **required fields** *project owners* and *topics* which you will find in the dipas configuration form. You have to create data for these taxonomies. Details can be found here https://wiki.dipas.org/.

For proceedings that need to edit appointments, go to the masterportal instance "Create-Appointment" and click edit then select the tab "Portal Settings". The checkmarks for "Center map at marker" and "'use setMarker' should be set to inactive (no checkmark)." You also need to do the following, under the Tool Plugins tab, set the "Drawing" tool to active (checkmark) and the "Drawing tool visible in the masterportal menu" setting to not active (no checkmark).
