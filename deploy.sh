#!/bin/bash

cd /srv/fitlytics.lesueur.nz/

sudo git pull origin main
sudo -u www-data composer install --profile

php artisan migrate --path=vendor/anomaly/streams-platform/migrations/application
php artisan migrate --all-addons
php artisan assets:clear
php artisan view:clear
php artisan httpcache:clear

sudo chown -R www-data:www-data ./
sudo chmod -R ug+rwx storage bootstrap/cache;
sudo -u www-data composer dump-autoload --profile
