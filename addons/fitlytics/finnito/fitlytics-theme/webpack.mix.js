let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/scss/theme/theme.scss', 'public/app/fitlytics/addons/flylytics/finnito/fitlytics-theme/resources/css/theme.css')
    .options({
        processCssUrls: false
    });
