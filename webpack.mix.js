let mix = require('laravel-mix');
// mix.css('addons/fitlytics/finnito/fitlytics-theme/resources/css/style.css', 'addons/fitlytics/finnito/fitlytics-theme/resources/css/');
mix.postCss('addons/fitlytics/finnito/fitlytics-theme/resources/css/style.css', 'public/css/style.css');