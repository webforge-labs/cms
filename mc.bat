@echo off

webforge-doctrine-compiler orm:compile --extension=Serializer src/php/Webforge/CmsBundle/Resources/doctrine/model.json src/php/ && php app/console doctrine:schema:update --force