# DrupalCenter Migrate

This project contains several migrations used to do major upgrades of
DrupalCenter.

## Installation

Clone the project into `modules/custom` of your Drupal installation and run
`composer install` within the modules directory.

Additionally you can add the repository as a package to your projects
_composer.json_ and run `composer require drupalcenter/dc-migrate:"dev-8.x-4.x"`.

## Configuration

To use the source database within the migrations you have to run the following
command: `drush upm-database-add dc_migrate {db-name} --username={name} --password={pass}`.
For further information about the command run `drush help upm-database-add`.

## Running migrations

1. Before running a migration make sure to set the source database as described
above.
2. Add the following lines to _settings.local.php_ to add the base migration
directory:
        ```php
        /**
         * Set migration source directory (relative from Drupal root).
         */
        $settings['file_migration_source_path'] = '../migration';
        ```
3. Run the migrations in the following order:
    * `drush mi dc_user_accounts --feedback=1000`
    * `drush mi ...`