# 从官方基础版本构建
FROM php:7.2-fpm
# 官方版本默认安装扩展: 
# Core, ctype, curl
# date, dom
# fileinfo, filter, ftp
# hash
# iconv
# json
# libxml
# mbstring, mysqlnd
# openssl
# pcre, PDO, pdo_sqlite, Phar, posix
# readline, Reflection, session, SimpleXML, sodium, SPL, sqlite3, standard
# tokenizer
# xml, xmlreader, xmlwriter
# zlib

# 更新为国内镜像
RUN mv /etc/apt/sources.list /etc/apt/sources.list.bak \
    && echo 'deb http://mirrors.163.com/debian/ stretch main non-free contrib' > /etc/apt/sources.list \
    && echo 'deb http://mirrors.163.com/debian/ stretch-updates main non-free contrib' >> /etc/apt/sources.list \
    && echo 'deb http://mirrors.163.com/debian-security/ stretch/updates main non-free contrib' >> /etc/apt/sources.list \
    && apt-get update

# bcmath, calendar, exif, gettext, sockets, dba, 
# mysqli, pcntl, pdo_mysql, shmop, sysvmsg, sysvsem, sysvshm 扩展
RUN docker-php-ext-install -j$(nproc) bcmath calendar exif gettext sockets dba mysqli pcntl pdo_mysql shmop sysvmsg sysvsem sysvshm iconv



# imagick 扩展
RUN export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && apt-get install -y --no-install-recommends libmagickwand-dev \
    && rm -r /var/lib/apt/lists/* \
    && pecl install imagick-3.4.4 \
    && docker-php-ext-enable imagick

# mcrypt 扩展 
RUN apt-get install -y --no-install-recommends libmcrypt-dev \
    && rm -r /var/lib/apt/lists/* \
    && pecl install mcrypt-1.0.2 \
    && docker-php-ext-enable mcrypt

# mcrypt 扩展 
RUN apt install php7.2-mysql

# redis 扩展
RUN apt install php-redis


COPY . /var/www/html/


# 镜像信息
LABEL Author="andyweiren"
LABEL Version="2022-0224"
LABEL Description="PHP 7.2 环境镜像" 