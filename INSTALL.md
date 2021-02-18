# Installation of DIPAS

## Requirements
- php
- apache
- postgres

## Procedure
1. Unpack the zip
2. Enter the database credentials in `config/drupal.database-settings.php`
3. Configure your webserver to use the root directory of the extracted zip as doc root
4. Navigate to `YOURDOMAIN.TLD/drupal/install.php`
5. Choose your language (currently only used for the wizzard because the config defaults to german)
6. Choose `Installation from existing configuration`
7. Finish the wizard
8. Enter account details for the main administration account
9. After the installation completes login into drupal and navigate to `YOURDOMAIN.TLD/drupal/admin/config/development/configuration/ignore`
10. Clear the textarea and save
11. Navigate to `YOURDOMAIN.TLD/drupal/admin/config/development/configuration`
12. Scroll to the bottom and click *Import all*
13. Flush all caches
14. Create the default domain by navigating to `YOURDOMAIN.TLD/drupal/de/admin/config/user-interface/proceedings/add`
15. Configure DIPAS too your needs
