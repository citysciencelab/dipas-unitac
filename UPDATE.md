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
8. Navigate to `YOURDOMAIN.TLD/drupal/update.php` & follow the wizard
