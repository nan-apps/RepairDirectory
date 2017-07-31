#!/usr/bin/env bash
case "$1" in
    'unit')
        docker/bin/php vendor/bin/phpunit --testsuite=Unit
        ;;
    'feature')
        docker/bin/php vendor/bin/phpunit --testsuite=Feature
        ;;
    'browser')
        cd docker
        docker-compose -f docker-compose.yml -f docker-compose.dusk.yml up -d web
        docker-compose -f docker-compose.yml -f docker-compose.dusk.yml run --rm  dusk
        docker-compose -f docker-compose.yml -f docker-compose.dusk.yml stop selenium
        cd ..
        docker/bin/dev start
        ;;
    *)
        echo $"Usage: $0 {start|stop}"
        ;;
esac
