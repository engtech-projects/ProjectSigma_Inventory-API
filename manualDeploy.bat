call composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
call ./vendor/bin/pint --test
@REM call php artisan key:generate
call php artisan migrate --step --force
call php artisan db:seed
call php artisan optimize
