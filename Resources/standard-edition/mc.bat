@echo off

webforge-doctrine-compiler orm:compile --extension=Serializer etc/doctrine/model.json src/php/ && php app/console doctrine:schema:update --force