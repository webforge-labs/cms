@echo off

call dc exec --user=www-data php bash -c "vendor/bin/simple-phpunit %*"