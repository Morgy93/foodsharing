cp docker/conf/ci_combined/supervisor_apps.conf /etc/supervisor/conf.d/
cp .codeception.ci.env .codeception.env
sed -i "s#/app#$CI_PROJECT_DIR#g" /etc/supervisor/conf.d/supervisor_apps.conf
sed -i "s#/app#$CI_PROJECT_DIR#g" /usr/local/etc/php-fpm.d/fpm.conf
sed -i "s#/app#$CI_PROJECT_DIR#g" /etc/nginx/conf.d/default.conf
mysql -u root -proot -hdb --execute="drop database if exists foodsharing; create database foodsharing"
cat migrations/initial.sql migrations/static.sql migrations/27-profilchange.sql migrations/27-verify.sql migrations/incremental-* | mysql -uroot -proot -hdb foodsharing
chown -R www-data:www-data .
chown -R www-data:www-data /var/www
apt-get update && apt-get install sudo
/start.sh &
sudo -u www-data composer install --verbose --prefer-dist --no-progress --no-interaction --no-suggest --no-scripts --ignore-platform-reqs
sudo -u www-data vendor/bin/codecept run acceptance ForumPostCest
sudo -u www-data vendor/bin/codecept run functional
