#!/bin/bash
# 在运行中的 Docker 容器中启用 mbstring 扩展

echo "正在安装 mbstring 扩展..."

# 进入容器并安装扩展
docker exec -it website bash -c "apt-get update && apt-get install -y php7.0-mbstring && phpenmod mbstring && service apache2 restart"

echo "mbstring 扩展安装完成"
