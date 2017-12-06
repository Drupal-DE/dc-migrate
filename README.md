# DrupalCenter Migrate

This project contains several migrations used to do major upgrades of
DrupalCenter.

## Installation

Clone the project into `modules/custom` of your Drupal installation and run
`composer install` within the modules directory.
Alternative you can add the repository as a package to your projects
_composer.json_ and run `composer require drupalcenter/dc-migrate:"dev-8.x-4.x"`.

Install _dc_migrate_ as any other module (using the UI or `drush en dc_migrate`.

## Configuration

To use the source database within the migrations you have to run the following
command: `drush upm-database-add dc_migrate {db-name} --username={name} --password={pass}`.
For further information about the command run `drush help upm-database-add`.

## Running migrations

1. Before running a migration make sure to set the source database as described
above.
2. Add the following lines to _settings.local.php_ to add the base migration
directory:

        /**
         * Set migration source directory (relative from Drupal root).
         */
        $settings['file_migration_source_path'] = '../migration';

3. Run the migrations:

  * User accounts:
    * `drush mi dc_user_accounts --feedback=1000`

  * Terms:
    * `drush mi dc_term__drupal_version`
    * `drush mi dc_term__showroom_category`
    * `drush mi dc_term__news_category`
    * `drush mi dc_term__discussion_category`

  * Handbook:
    * `drush mi dc_node__handbook`
    * `drush mi dc_book__handbook`
    * `drush mi dc_comment__handbook`

  * News:
    * `drush mi dc_node__news`
    * `drush mi dc_comment__news`

  * Files:
    * `drush mi dc_file__user_picture`
    * `drush mi dc_file_upload__handbook`
    * `drush mi dc_file_upload__news`
    * `drush mi dc_file_upload__forum`

  * Media:
    * `drush mi dc_media__user_picture`

  * Fields:
    * `drush mi dc_user_accounts__fields`
