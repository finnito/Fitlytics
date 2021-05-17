#!/bin/bash

cd /srv/fitlytics.lesueur.nz/

sudo git pull origin main
sudo -u www-data composer install --profile

sudo -u www-data php artisan migrate --path=vendor/anomaly/streams-platform/migrations/application
sudo -u www-data php artisan migrate --all-addons
sudo -u www-data php artisan assets:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan httpcache:clear

sudo chown -R www-data:www-data ./
sudo chmod -R ug+rwx storage bootstrap/cache;
sudo -u www-data composer dump-autoload --profile
