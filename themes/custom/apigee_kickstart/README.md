# Apigee Kickstart theme

The Apigee Kickstart theme uses the [Radix](https://drupal.org/project/radix) as its base theme. There are two different ways you can customize the look and feel of this theme:

1. **Customize the color scheme in the user interface**: It's possible to customize the color scheme used in this theme without writing any code. When logged in as a privileged user with `apigee_kickstart` enabled as the default theme, you'll see a **Customize** link in the Toolbar on the front end of the site. Clicking the **Customize** link opens the Settings Tray, which contains 7 color fields, which you can edit using a color wheel. Use these fields to customize the colors for the site header, footer, and accents, such as buttons and icons.
2. **Customizing in code by creating a sub-theme**: If you'd like to add or make adjustments to templates, CSS or JavaScript, it is recommended to create a sub-theme.

## Creating an Apigee Kickstart Sub-theme

This theme provides a very lightweight starter kit at `src/kits/apigee_custom`. The kit is used by Drush when generating a subtheme (see instructions below), but may also be used as a reference or copied/edited manually to create a subtheme as described in the following sections.

### Using Drush to Generate your Sub-theme

Drush can be used to generate a sub-theme of `apigee_kickstart` for you.

1. First, ensure you have a working **Drush 9** installation. See [Drush documentation](https://docs.drush.org/en/master/install/) for details.

2. Navigate to the site root in your terminal, substituting `my-portal` with your directory, i.e. `cd ~/Sites/my-portal`.

3. Run the Drush command, substituting `subtheme` with what you'd like your sub-theme's machine name to be:

    `drush --include="web/themes/contrib/radix" radix:create "subtheme" --kit=apigee_custom`

Upon completion of the Drush command, you will have a newly created theme at `web/themes/custom/subtheme`.

### Creating a Sub-theme Manually

The starter kit can be copied and manually edited to achieve the same result as what the Drush script does using the following steps:

1. Copy the starter kit into the custom themes directory: `web/themes/custom/subtheme`.
2. Change all occurrences of `apigee_custom` in file names, including those in `config/` to reflect your theme's machine name.
3. Change all occurrences of `RADIX_SUBTHEME_MACHINE_NAME` to reflect your theme's machine name.
4. Open `subtheme.info.yml` and remove the line that reads `hidden: true`.

### Enable your New Sub-theme

When your new sub-theme is ready, you will need to enable it:

1. Visit `/admin/appearance`
2. Scroll down to your sub-theme.
3. Click "Install and set as default" link.

### Theming

Add your custom CSS styles in `subtheme/css/subtheme.style.css` and custom scripts in `subtheme/js/subtheme.script.js`.

#### Learn More

- Check out the [Drupal 8 Theming Guide](https://www.drupal.org/docs/8/theming) to learn more about how to work with Drupal 8 themes.
- Learn how to [Disable Drupal 8 caching during development](https://www.drupal.org/node/2598914).

## Running the Build Script

This theme theme uses [Webpack](https://webpack.js.org) to compile and bundle SASS and JS. **Node.js** and **NPM** are required for using the theme's build script. See NPM's [Downloading and installing Node.js and npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) guide for instructions.

1. Navigate to the root of this theme in your terminal, and install NPM dependencies: `npm install`.
2. Change the `proxy` variable in `webpack.mix.js`.
3. Start the build script, which will compile Sass and watch for changes: `npm run watch`.`
