/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your application. See https://github.com/JeffreyWay/laravel-mix.
 |
 */
const proxy = 'http://ks.test';
const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Configuration
 |--------------------------------------------------------------------------
 */
mix
  .setPublicPath('assets')
  .disableNotifications()
  .options({
    processCssUrls: false
  });

/*
 |--------------------------------------------------------------------------
 | Browsersync
 |--------------------------------------------------------------------------
 */
mix.browserSync({
  proxy: proxy,
  files: ['assets/js/**/*.js', 'assets/css/**/*.css'],
  stream: true,
});

/*
 |--------------------------------------------------------------------------
 | SASS
 |--------------------------------------------------------------------------
 */
mix
  .sass('src/sass/apigee-kickstart.style.scss', 'css')
  .sass('src/sass/apigee-kickstart.smartdocs.scss', 'css')
  .sass('src/sass/apigee-kickstart.monetization.scss', 'css')
  .sass('src/sass/apigee-kickstart.monetization.add-credit.scss', 'css');

/*
 |--------------------------------------------------------------------------
 | JS
 |--------------------------------------------------------------------------
 */
mix
  .js('src/js/apigee-kickstart.script.js', 'js')
  .js('src/js/apigee-kickstart.commerce.authnet.js', 'js')
  .js('src/js/apigee-kickstart.commerce.stripe.js', 'js')
  .js('src/js/modernizr.js', 'js')
;
