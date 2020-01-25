baxia_site
===============

A Symfony project created on 2018.

1- run composer install
2- run command line php bin/console ckeditor:install
3- php bin/console lexik:translations:import WebitForexSiteBundle --cache-clear --locales en 
4- php bin/console doctrine:fixtures:load --fixtures=src/Webit/ForexSiteBundle/DataFixtures/ORM/Emails --append
