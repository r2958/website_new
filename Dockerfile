# 使用官方 Ubuntu 16.04 镜像
FROM ubuntu:16.04

# 更新软件包列表
RUN apt-get update

# 安装 Apache 和 PHP 7.0
RUN apt-get install -y apache2 php7.0 libapache2-mod-php7.0 git

# 将 Apache 的默认站点目录设置为 /var/www/html
RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-available/default-ssl.conf

# 创建项目目录
RUN mkdir -p /var/www/html/public

# 克隆 GitHub 仓库
RUN git clone https://github.com/r2958/website_new.git /var/www/html/public

# 暴露 Apache 端口
EXPOSE 80

# 启动 Apache 服务
CMD ["apache2ctl", "-D", "FOREGROUND"]
