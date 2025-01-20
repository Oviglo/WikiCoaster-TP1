call php bin/console doctrine:database:drop --force --env=test -q
call php bin/console doctrine:database:create --env=test -q
call php bin/console doctrine:schema:update --force --env=test -q
call php bin/console doctrine:fixtures:load --env=test -q
call php bin/phpunit