#!/usr/bin/env bash
php bin/console lexik:jwt:generate-keypair
composer install

exec "$@"
