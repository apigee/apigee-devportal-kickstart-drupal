# Apigee Developer Portal Kickstart

Apigee Developer Portal Kickstart is a Drupal distribution that allows to quickly try out or get started using Drupal to create an Apigee developer portal.

This project is a [Drupal installation profile](https://www.drupal.org/docs/8/distributions) which needs to be added to Drupal core and installed using [Composer](https://getcomposer.org). The installation instructions below use the [Apigee Developer Portal Kickstart Composer project](https://github.com/apigee/devportal-kickstart-project-composer) to install Drupal core and this installation profile to create a site.

## Profile status

The core functionality of this profile is in active development. The features and functionality will evolve as the project grows. We encourage you to download and evaluate the profile, and to use our [GitHub issue queue](https://github.com/apigee/apigee-devportal-kickstart-drupal/issues) to give feedback, ask questions, or log issues.

## Planned features

* **Monetization:** The ability to configure the site with [Apigee Monetization](https://www.drupal.org/project/apigee_m10n) features during installation.

## Prerequisites

* [Git](https://git-scm.com)
* [Composer](https://getcomposer.org)

## Installation

The following command will download Drupal core, the Apigee Developer Portal Kickstart profile, and their dependencies, into the `MY_PROJECT` directory:

```sh
composer create-project apigee/devportal-kickstart-project:8.x-dev MY_PROJECT --stability dev --no-interaction
```

The actual web root will be `MY_PROJECT/web`. You will need to point your [web server](https://www.drupal.org/docs/develop/local-server-setup) to serve that directory. Then, visit the site in a web browser, and you'll be redirected to `core/install.php`, where you can run the installer like any Drupal site installation. Running the installation via web browser is recommended for the best experience.

Alternatively, you may run the composer `quick-start` script to run the [Drupal 8 Quick Start Command](https://www.drupal.org/docs/8/install/drupal-8-quick-start-command). This will install Drupal using PHP's built in web server and an SQLite database.*

```sh
cd MY_PROJECT
composer quick-start
```

**Note: While `quick-start` is the quickest means of installing this profile, it is not the most performant, or complete. It does not present the configuration options available when installing via web browser. As a result, you'll need to configure the [Apigee Edge connection](https://www.drupal.org/docs/8/modules/apigee-edge/configure-the-connection-to-apigee-edge) manually, post installation. See [Apigee Edge documentation](https://www.drupal.org/docs/8/modules/apigee-edge) for more details.*

## Issues, questions and feedback

We encourage anyone with feedback, questions or issues to put in an issue into
our [Github issue queue](https://github.com/apigee/apigee-devportal-kickstart-drupal/issues).

## Known issues

* Using `drush si` to install the site with Apigee Edge credentials is not working. See [#109](https://github.com/apigee/apigee-devportal-kickstart-drupal/issues/109) for details.
* When "Enable demo content" is not selected during installation, associated custom blocks are still placed. See [#72](https://github.com/apigee/apigee-devportal-kickstart-drupal/issues/72) for details.

## Contribute

We'd love to accept your patches and contributions to this project. Make sure to read [CONTRIBUTING.md](CONTRIBUTING.md) for details.
Development is happening in our [GitHub repository](https://github.com/apigee/apigee-devportal-kickstart-drupal). The drupal.org issue
queue is disabled, we use the [Github issue queue](https://github.com/apigee/apigee-devportal-kickstart-drupal/issues) instead.

## Disclaimer

This is not an officially supported Google product.
