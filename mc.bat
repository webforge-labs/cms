@echo off

call webforge-doctrine-compiler orm:compile --extension=Serializer src/php/Webforge/CmsBundle/Resources/doctrine/model.json src/php/
call webforge-doctrine-compiler orm:compile --extension=Serializer src/php/AppBundle/Resources/doctrine/model.json src/php/ 
call php app/console doctrine:schema:update --force