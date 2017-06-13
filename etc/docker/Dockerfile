
FROM webforgelabs/php-nodejs as nodebuild

WORKDIR /app

COPY package.json ./
RUN npm install --unsafe-perm && npm cache clean

# everything for build
COPY Gulpfile.js ./
COPY src/js src/js/
COPY src/scss src/scss/
COPY etc/cms etc/cms/
COPY Resources/fonts Resources/fonts/
COPY Resources/uikit Resources/uikit/
COPY Resources/img Resources/img/

RUN npm run build



# this could be an image, containing only php (with composer)
FROM webforgelabs/php-nodejs

WORKDIR /app

# php configuration taken from this repo
COPY etc/php/*.ini /usr/local/etc/php/conf.d/

COPY composer.* ./
RUN composer install -o --prefer-dist --no-interaction \
   && curl -L -o phpunit.phar https://phar.phpunit.de/phpunit-4.8.phar \
   && mkdir -p files/cache


# assets and node_modules build with node
COPY --from=nodebuild /app/www/assets www/assets/
COPY --from=nodebuild /app/node_modules node_modules/

RUN npm install wdio-phantomjs-service phantomjs-prebuilt

# all other code
COPY . ./

# remove not needed stuff (unfortunately we cannot .dockerignore this, because we need it in the nodebuild stage)
# note: Resources/img/dummy will be used in tests! dont delete
#rm -r Resources/js; \
RUN rm -r Resources/fonts; \
  mkdir -p /app/files/cache \
  && chown -R www-data.www-data /var/www /app \
  && chmod +x bin/cli.sh
