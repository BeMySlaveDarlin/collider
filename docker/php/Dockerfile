FROM bemyslavedarlin/collider:1.0 AS php_base

ENV PATH=/opt/www/vendor/bin:/usr/local/bin:${PATH}

WORKDIR /opt/www

COPY docker/php/conf.d/hyperf.ini /usr/local/etc/php/conf.d/991-hyperf.ini

EXPOSE 9501

FROM php_base AS php_dev

ENV APP_ENV=dev
ENV SCAN_CACHEABLE=false

COPY docker/php/conf.d/hyperf.dev.ini /usr/local/etc/php/conf.d/992-hyperf-dev.ini

FROM php_base AS php_production

ENV APP_ENV=production
ENV SCAN_CACHEABLE=true

COPY docker/php/conf.d/hyperf.prod.ini /usr/local/etc/php/conf.d/992-hyperf-prod.ini

FROM php_base AS composer

CMD ["composer", "install"]
