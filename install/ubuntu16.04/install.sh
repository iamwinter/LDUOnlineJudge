#!/bin/sh

APP_HOME=$(dirname $(dirname $(dirname $(readlink -f "$0"))))

set -ex
cd "${APP_HOME}" || { echo No such project: ${APP_HOME};exit 1; }

# php environment
apt-get update && apt-get -y upgrade
apt-get -y install software-properties-common
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt-get update
apt-get -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml php7.2-mbstring
service php7.2-fpm start

# composer
apt -y install composer zip unzip
composer install --ignore-platform-reqs

# laravel initialization
cp -rf .env.example .env
mkdir -p storage/app/public
chmod -R 755 storage bootstrap/cache
chown www-data:www-data -R storage bootstrap/cache
php artisan storage:link
php artisan key:generate
php artisan optimize

# nignx
apt -y install nginx
rm -rf /etc/nginx/sites-enabled/default
rm -rf /etc/nginx/conf.d/lduoj.conf
sed -i "s/\/home\/LDUOnlineJudge/${APP_HOME//\//\\\/}/" "${APP_HOME}"/install/nginx/lduoj.conf
ln -s "${APP_HOME}"/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf
service nginx restart

# mysql
apt -y install mysql-server
if [ -f /.dockerenv ]; then
    usermod -d /var/lib/mysql/ mysql
fi
service mysql restart
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE If Not Exists lduoj;"
mysql -u${USER} -p${PASSWORD} -e"CREATE USER If Not Exists 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';"
mysql -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"
mysql -u${USER} -p${PASSWORD} -Dlduoj < ./install/mysql/lduoj.sql

# Allow php user www-data to use 'sudo' to get privilege of APP_HOME
# If you don't grant the right to user www-data, then you will not be able to start or stop the judge in administration.
if [ -f /.dockerenv ]; then
    apt -y install sudo
fi
echo 'www-data ALL=(ALL) NOPASSWD: /bin/bash,/usr/bin/git,/usr/bin/g++,/bin/sed' >> /etc/sudoers
# 用户名 主机名(ALL所有主机)=(用户名,以该用户运行命令,ALL表示任意用户) NOPASSWD不需要输入密码: 命令的绝对路径(逗号分隔)ALL表示所有命令

# install judge environment & sim config & start to judge
apt -y install g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex
cp -p "${APP_HOME}"/judge/sim/sim.1 /usr/share/man/man1/
cd "${APP_HOME}"/judge/sim/ && make install
bash "${APP_HOME}"/judge/startup.sh

# If in docker, initialize startup.sh used to start LDUOnlineJudge in docker container
if [ -f /.dockerenv ]; then
    rm -rf /startup.sh
    chmod +x "${APP_HOME}"/install/docker/startup.sh
    ln -s "${APP_HOME}"/install/docker/startup.sh /startup.sh
fi

echo "You have successfully installed LDU Online Judge! Location: ${APP_HOME}"
