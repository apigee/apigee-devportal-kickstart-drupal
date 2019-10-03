# Introduction
The apigee_kickstart_migrate module provides a migration path for Drupal 7 
Devportal to Drupal 8 Kickstart.

## Migrations
The module ships with migrations for all entity types that are available out of
box in Drupal 7 Devportal, including content types, taxonomy terms and comment types.

Note: User accounts are not migrated but are pulled from Apigee Edge during synchronization.

|               | Drupal 7 (Devportal)    | Drupal 8 (Kickstart)    |
|---------------|-------------------------|-------------------------|
| Content Types | **Article (article)**   | **Article (article)**   |
|               | title                   | title                   |
|               | body                    | body                    |
|               | field_keywords          | field_tags              |
|               |                         |                         |
|               | **Basic page (page)**   | **Basic page (page)**   |
|               | title                   | title                   |
|               | body                    | body                    |
|               |                         |                         |
|               | **FAQ (faq)**           | **FAQ (faq)**           |
|               | title                   | title                   |
|               | body                    | field_answer            |
|               |                         |                         |
|               | **Forum topic (forum)** | **Forum topic (forum)** |
|               | title                   | title                   |
|               | body                    | body                    |
|               | taxonomy_forums         | taxonomy_forums         |
|               |                         |                         |
| Comment Types | **Comment (comment)**   | **Comment (comment)**   |
|               | author                  | author                  |
|               | subject                 | subject                 |
|               | comment_body            | comment_body            |
|               |                         |                         |
| Taxonomy      | **Forums (forums)**     | **Forum (forums)**      |
|               | name                    | name                    |
|               |                         |                         |
|               | **Tags (tags)**         | **Tags (tags)**         |
|               | name                    | name                    |

## Requirements
1. A valid connection to Apigee Edge. See the [documentation](https://www.drupal.org/docs/8/modules/apigee-edge/configure-the-connection-to-apigee-edge) 
on how to configure an Apigee Edge Connection.
2. All developer accounts on Edge synchronized with your Drupal site. See the [documentation](https://www.drupal.org/docs/8/modules/apigee-edge/synchronize-developers-with-apigee-edge) on how to synchronize 
developer accounts.

## Installation
1. Configure the source (Drupal 7 devportal) database connection in your `settings.php` file.

```
$databases['migrate']['default'] = array (
  'database' => 'D7_DATABASE_NAME',
  'username' => 'USERNAME',
  'password' => 'PASSWORD',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
```
2. Enable the `apigee_kickstart_migrate` module: `drush en apigee_kickstart_migrate -y`

You should now be ready to run the migrations.

## Running migrations
#### To see a list of migrations, run the following command: 

`$ drush ms --group=devportal`

#### To run a migration:

`$ drush mim devportal_article --execute-dependencies` 

#### To revert a migration:

`$ drush mr devportal_article`

#### To run all devportal migrations:

`$ drush mim --group=devportal`

## Custom migrations

In most cases, the entity types on your Drupal 7 Devportal have been customized 
with additional fields. Since this is different for every Devportal, the 
default migrations do not handle these fields. You will need to write your 
own migration.

The **apigee_kickstart_migrate_example** module provides examples on how you 
can extend the default migrations to add your own custom fields. 
