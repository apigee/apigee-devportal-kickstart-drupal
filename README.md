# Apigee Devportal Kickstart

Apigee Developer Kickstart is a Drupal distribution to quickly try out or get started using Drupal to create
an Apigee developer portal.

This project is a [Drupal installation profile](https://www.drupal.org/docs/8/distributions) which needs
to be added to Drupal core and installed using [Composer](https://getcomposer.org).  The installation instructions
below use the [Apigee Devportal Kickstart composer project](https://github.com/apigee/apigee-devportal-kickstart-drupal)
to install Drupal core and this installation profile to create a site.

# Prerequisites

* [Git](https://git-scm.com)
* [Composer](https://getcomposer.org)

# Installation

The following command will download Drupal core and the Apigee Developer Portal Kickstart profile into the
MY_PROJECT directory:

```
composer create-project --stability dev --no-interaction apigee/devportal-kickstart-project MY_PROJECT
```

The actual webroot will be MY_PROJECT/web. You will need to point your web server to serve up that directory and
run the installer like any Drupal site installation.

If you want to quickly evaluate the system you  can alternatively run the following command to run Drupal using
PHP's built in web server and a SQLite database:

```
cd MY_PROJECT
composer quick-start
```

# Issues, Questions and Feedback
We encourage anyone with feedback, questions or issues to put in an issue into
our [Github issue queue](https://github.com/apigee/apigee-devportal-kickstart-drupal/issues).

# Contribute
We'd love to accept your patches and contributions to this project. Make sure to read [CONTRIBUTING.md](CONTRIBUTING.md) for details.
Development is happening in our [GitHub repository](https://github.com/apigee/apigee-devportal-kickstart-drupal). The drupal.org issue
queue is disabled, we use the [Github issue queue](https://github.com/apigee/apigee-devportal-kickstart-drupal/issues) instead.

# Disclaimer

This is not an officially supported Google product.
