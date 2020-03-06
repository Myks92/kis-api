up: docker-up
down: docker-down
restart: docker-down docker-up
init: docker-down-clear api-clear docker-pull docker-build docker-up api-init
test: api-test
test-coverage: api-test-coverage

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

api-init: api-composer-install api-oauth-keys api-wait-db api-migrations api-fixtures

api-env:
	docker-compose exec api-php-cli rm -f .env
	docker-compose exec api-php-cli ln -sr .env.example .env

api-clear:
	docker run --rm -v ${PWD}:/app -w /app alpine sh -c 'rm -rf var/*'

api-permissions:
	docker run --rm -v ${PWD}:/app -w /app alpine chmod 777 var

api-composer-install:
	docker-compose run --rm api-php-cli composer install

api-oauth-keys:
	docker-compose run --rm api-php-cli mkdir -p var/oauth
	docker-compose run --rm api-php-cli openssl genrsa -out var/oauth/private.key 2048
	docker-compose run --rm api-php-cli openssl rsa -in var/oauth/private.key -pubout -out var/oauth/public.key
	docker-compose run --rm api-php-cli chmod 644 var/oauth/private.key var/oauth/public.key

api-wait-db:
	until docker-compose exec -T api-postgres pg_isready --timeout=0 --dbname=app ; do sleep 1 ; done

api-migrations:
	docker-compose run --rm api-php-cli php bin/console doctrine:migrations:migrate --no-interaction

api-fixtures:
	docker-compose run --rm api-php-cli php bin/console doctrine:fixtures:load --no-interaction

api-test:
	docker-compose run --rm api-php-cli php bin/phpunit

api-test-coverage:
	docker-compose run --rm api-php-cli php bin/phpunit --coverage-clover var/clover.xml --coverage-html var/coverage

api-test-functional:
	docker-compose run --rm api-php-cli php bin/phpunit --testsuite=functional

api-test-functional-coverage:
	docker-compose run --rm api-php-cli php bin/phpunit --testsuite=functional --coverage-clover var/clover.xml
	--coverage-html var/coverage