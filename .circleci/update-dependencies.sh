#!/bin/bash -ex

# Make sure the robofile is in the correct location.
cp profiles/apigee_devportal_kickstart/.circleci/RoboFile.php ./

robo setup:skeleton
robo add:dependencies-from profiles/$1/composer.json
robo drupal:version $2
robo configure:module-dependencies
robo update:dependencies
robo do:extra $2

# Touch a flag so we know dependencies have been set. Otherwise, there is no
# easy way to know this step needs to be done when running circleci locally since
# it does not support workflows.
touch dependencies_updated
