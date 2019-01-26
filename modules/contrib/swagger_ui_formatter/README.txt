CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

This module provides a field formatter for file fields with allowed file types
of JSON (.json) and/or YAML (.yml or .yaml), which renders the uploaded file
using Swagger UI if the file is a valid Swagger file. This module uses the
Swagger UI library available at https://github.com/swagger-api/swagger-ui

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/swagger_ui_formatter

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/swagger_ui_formatter


REQUIREMENTS
------------

This module requires the following outside of Drupal core.

For version 8.x-2.x the Swagger UI library needs to be installed separately.
Download the appropriate Swagger UI version, extract the archive and rename the
folder to "swagger-ui" or to "swagger_ui". Place the renamed folder in the
[DRUPAL ROOT]/libraries directory so its path will be
[DRUPAL ROOT]/libraries/swagger-ui for example.

 * Swagger UI - https://swagger.io/tools/swagger-ui/
 * Swagger UI library - https://github.com/swagger-api/swagger-ui/releases


INSTALLATION
------------

Install the Swagger UI Field Formatter module as you would normally install
a contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
further information.

Alternately,

    1. Download the Swagger UI library, extract the file and rename the folder
       to "swagger-ui" or "swagger_ui". Now, place the renamed folder in the
       [DRUPAL ROOT]/libraries directory, so its path will be
       [DRUPAL ROOT]/libraries/swagger-ui for example.
    2. Download the module from https://drupal.org and extract it into your
       [DRUPAL ROOT]/modules/contrib directory. Login as administrator, visit
       the admin/modules page with your web browser and install the
       Swagger UI Field Formatter module.


CONFIGURATION
-------------

    1. Navigate to Structure > Content types > TYPE > Manage fields where
       TYPE is the content type to which you want to add the new field, such as
       a Basic page.
    2. Click on the "Add field" button to add a new field.
    3. Set the field type to "File" and enter a label name.
    4. Click "Save and continue".
    5. On the "Edit" tab, in the "Allowed file extensions" field enter the
       following: yaml,yml,json
    6. Click "Save settings".
    7. Click on the "Manage display" tab.
    8. Select "Swagger UI" in the Format drop-down for the new field and
       optionally configure the formatter settings.
    9. Click Save.
    10. Add a new "TYPE" type content and upload a valid Swagger file.

When viewing the content page the uploaded Swagger file will be rendered by
Swagger UI.

Note: If the content of the Swagger file does not render correctly try clearing
the cache by navigating to Configuration > Development > Performance and
clicking on the "Clear all caches" button.


MAINTAINERS
-----------

The 8.x branches:

 * Balazs Wittmann (balazswmann) - https://www.drupal.org/u/balazswmann
 
Supporting organizations:

 * Pronovix - https://www.drupal.org/pronovix

---

The 7.x branches:
 
 * Dezső BICZÓ (mxr576) - https://www.drupal.org/u/mxr576
 * dsudheesh - https://www.drupal.org/u/dsudheesh

Supporting organizations:

 * DigitalAPICraft - https://www.drupal.org/digitalapicraft