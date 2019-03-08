# Configuration

The **Apigee Devportal Kickstart Profile** builds upon Drupal's Standard installation profile located at `core/profiles/standard/config`. This directory contains a copy of that configuration, with the following changes:

1. `block.block.bartik_*.yml` configuration files have been relocated to `optional` in order to meet dependencies when installing a custom theme by default, while still allowing the Bartik theme to be installed properly at a later time, if desired.
