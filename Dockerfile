FROM debain
LABEL  "author"="andyweiren<andyweiren@tencent.com>"

ADD ./docker/sources.list /etc/apt/
RUN apt-get update \
 && apt-get install --yes --force-yes \
    php7.0 \
    php7.0-cgi \
    php7.0-cli \
    php7.0-common \
    php7.0-curl \
    php7.0-dev \
    php7.0-fpm \
    php7.0-mysql \
    php7.0-sqlite3 \
    php7.0-json \
    php7.0-xml \
    php7.0-zip \
    php7.0-gd \
    php7.0-bcmath \
    php7.0-dba \
    php7.0-imap \
    php7.0-ldap \
    php7.0-mcrypt \
    php7.0-mbstring \
    php7.0-odbc \
    php7.0-opcache \
    php7.0-sqlite3 \
    php7.0-memcached \
    libmemcached-dev \
    libmemcached11 \
    pkg-config \
    php7.0-redis \
    libgmp10 \
    libgmp-dev \
    php7.0-gmp \
    supervisor \
    nginx \
  && useradd nginx

RUN mkdir -p /data/log/php-fpm/ \
 && mkdir -p /data/log/nginx/ \
 && mkdir -p /data/conf \
 && mkdir -p /data/app/ \
 && mkdir -p /var/log/php-fpm/ \
 && chown -R www-data.www-data /data/log /data/conf /data/app/

ADD ./docker/nutcracker2 /usr/local/bin/nutcracker
RUN chmod +x /usr/local/bin/nutcracker

COPY ./docker/supervisor/conf.d /etc/supervisor/conf.d
ADD ./docker/fpm /etc/php/7.0/fpm
ADD ./docker/html/check.html /data/www/
COPY ./docker/nginx /etc/nginx

ADD ./docker/background.sh /
RUN chmod +x /background.sh

EXPOSE 80
CMD ["/background.sh"]

ADD . /data/app/website_new