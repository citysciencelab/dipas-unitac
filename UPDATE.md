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
6. Navigate to `YOURDOMAIN.TLD/drupal/update.php`& follow the wizard
7. Navigate to `YOURDOMAIN.TLD/drupal/admin/config/development/configuration`
8. Scroll to the bottom and click *Import all*


### From 1.0.0 to 2.0.0

The update of an exisiting DIPAS installation with version 1.0.0 is still in an experimental state.
It has not yet been completely tested!

Please have in mind the drupal update from drupal 8 to 9!
This comes along with changed requirements for PHP and PostgreSQL!

Follow these steps BEFORE starting the general update procedure
 
0. **Backup** all files and the database to prevent data loss
1. Navigate to `YOURDOMAIN.TLD/drupal/admin/modules/uninstall` and uninstall module "nimbus" 
2. Navigate to `YOURDOMAIN.TLD/drupal/admin/appearance` and uninstall theme "classy"
3. Move your database from PostgreSQL 9 to PostgreSQL 13
4. then go on and start the general update procedure
