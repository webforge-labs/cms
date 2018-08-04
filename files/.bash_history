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
