php -v
exit
ONVAULT composer update -v
apt-get install iproute2
exit
ONVAULT composer update -vv
exit

phpunit
exit
curl -OL https://github.com/sensiolabs-de/deprecation-detector/releases/download/0.1.0-alpha4/deprecation-detector.phar
php deprecation-detector.phar
exit
ln -s /app/vendor/bin/simple-phpunit phpunit
phpunit 
ls -la /app/vendor/bin/simple-phpunit 
phpunit
ls -la
ls -la phpunit
rm phpunit
ln -s /app/vendor/bin/simple-phpunit /usr/local/sbin/phpunit
exti
exit
phpunit --help
exit
php app/console doctrine:schema:update --force
exit
