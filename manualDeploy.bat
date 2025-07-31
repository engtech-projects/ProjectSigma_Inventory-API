composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
./vendor/bin/pint --test
@REM php artisan key:generate
php artisan migrate --step --force
php artisan db:seed
php artisan optimize
