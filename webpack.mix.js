let mix = require('laravel-mix');

mix.postCss(
    "addons/fitlytics/finnito/fitlytics-theme/resources/css/style.css",
    'public/css/style.css');