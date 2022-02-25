# 从官方基础版本构建
FROM php:7.1-fpm-alpine
RUN docker-php-install pdo pdo-mysql
COPY . /var/www/html


# 镜像信息
LABEL Author="andyweiren"
LABEL Version="2022-0224"
LABEL Description="PHP 7.2 环境镜像" 