
FROM webforgelabs/php-nodejs:php7.2-node8 as nodebuild

WORKDIR /app

ENV npm_config_devdir=/var/www/.node-builds

COPY package.json ./
RUN npm install --unsafe-perm \
  && npm install wdio-phantomjs-service phantomjs-prebuilt \
  && npm cache clean --force

# everything for build
COPY Gulpfile.js ./
COPY index.js ./
COPY src/js src/js/
COPY src/scss src/scss/
COPY etc/cms etc/cms/
COPY Resources/fonts Resources/fonts/
COPY Resources/uikit Resources/uikit/
COPY Resources/img Resources/img/
COPY src/php/Webforge/CmsBundle/Resources/js-translations src/php/Webforge/CmsBundle/Resources/js-translations/

RUN npm run build




FROM webforgelabs/php-nodejs:php7.2-node8

WORKDIR /app

# php configuration taken from this repo
COPY etc/php/*.ini /usr/local/etc/php/conf.d/

COPY composer.* ./
RUN composer install -o --prefer-dist --no-interaction \
   && mkdir -p files/cache \
   && (vendor/bin/simple-phpunit || true ) \
   && ln -s /app/vendor/bin/simple-phpunit /usr/local/sbin/phpunit \
   && composer clear-cache

ENV npm_config_devdir=/var/www/.node-builds

# assets and node_modules build with node
COPY --from=nodebuild /app/www/assets www/assets/
COPY --from=nodebuild /app/node_modules node_modules/
COPY --from=nodebuild /var/www/.node-builds /var/www/.node-builds

# all other code
COPY . ./

# remove not needed stuff (unfortunately we cannot .dockerignore this, because we need it in the nodebuild stage)
# note: Resources/img/dummy will be used in tests! dont delete
#rm -r Resources/js; \
RUN rm -r Resources/fonts; \
  mkdir -p /app/files/cache && mkdir -p /app/files/media \
  && chown -R www-data.www-data /app/app /app/files /app/www \
  && chmod +x bin/cli.sh
