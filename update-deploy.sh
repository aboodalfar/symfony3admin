#!/bin/bash
rm -rf var/cache/prod/*
chmod -R 777 var/cache
php bin/console assets:install --symlink web
