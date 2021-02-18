<?php

// Provide local database credentials.
$databases['default']['default'] = [
  'database' => 'dipas',
  'username' => 'dipas',
  'password' => 'dipas',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '5432',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\pgsql',
  'driver' => 'pgsql',
];

// Configuration settings for the local environment indicator.
$config['environment_indicator.indicator']['name'] = 'DEVELOPMENT';
$config['environment_indicator.indicator']['bg_color'] = 'rgb(0, 120, 0)';
$config['environment_indicator.indicator']['fg_color'] = 'rgb(255, 255, 255)';

// Automatically copy uploaded images and files from the live server as needed.
// NO trailing slash!
$config['stage_file_proxy.settings']['origin'] = 'http://www.liveserver.de';

// Disable CSS and JS aggregation in development environments.
$config['system.performance']['cache']['page']['max_age'] = 0;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

// Show ALL PHP notices and errors in the frontend (not only within the logs).
$config['system.logging']['error_level'] = 'all';

// Remove the "X-Frame-Options" header for the local DEV environment
$config['x_frame_options'] = FALSE;
