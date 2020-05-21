#!/bin/bash

export FS_ENV=ci

set -o errexit
set -x

dir=$(dirname "$0")

# shellcheck source=./inc.sh
source "$dir"/inc.sh
echo $SECONDS seconds elapsed


cp docker/conf/ci_combined/supervisor_apps.conf /etc/supervisor/conf.d/
rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
sed -i "s#/app#$CI_PROJECT_DIR#g" /etc/supervisor/conf.d/supervisor_apps.conf
sed -i "s#/app#$CI_PROJECT_DIR#g" /usr/local/etc/php-fpm.d/fpm.conf
sed -i "s#/app#$CI_PROJECT_DIR#g" /etc/nginx/conf.d/default.conf
mysql -u root -proot -hdb --execute="drop database if exists foodsharing; create database foodsharing"
cat migrations/initial.sql migrations/static.sql migrations/27-profilchange.sql migrations/27-verify.sql migrations/incremental-* | mysql -uroot -proot -hdb foodsharing
chown -R www-data:www-data .
chown -R www-data:www-data /var/www
/start.sh &
sudo -u www-data composer install --verbose --prefer-dist --no-progress --no-interaction --no-suggest --no-scripts --ignore-platform-reqs
log-header "Running tests"
failed=0
SECONDS=0
sudo -E -u www-data vendor/bin/codecept run --xml --html || failed=1
echo $SECONDS seconds elapsed

if [ $failed -eq 1 ]; then
  log-header "Check for codeception errors"
  # check if codeception generated a report file that contains failed tests
  # Otherwise, codeception probably failed itself and the whole job should fail
  grep -E '<error|<failure' tests/_output/report.xml || (echo "report.xml is incomplete, aborting" && false)

  log-header "Rerunning failed tests"
  SECONDS=0
  sudo -E -u www-data vendor/bin/codecept run --xml --html -g failed || failed=2
  echo $SECONDS seconds elapsed
fi

if [ $failed -eq 2 ]; then
  log-header "Check for codeception errors"
  # check if codeception generated a report file that contains failed tests
  # Otherwise, codeception probably failed itself and the whole job should fail
  grep -E '<error|<failure' tests/_output/report.xml || (echo "report.xml is incomplete, aborting" && false)

  log-header "Rerunning failed tests"
  SECONDS=0
  sudo -E -u www-data vendor/bin/codecept run --xml --html -g failed || failed=3
  echo $SECONDS seconds elapsed
fi

if [ $failed -eq 3 ]; then
  log-header "Check for codeception errors"
  # check if codeception generated a report file that contains failed tests
  # Otherwise, codeception probably failed itself and the whole job should fail
  grep -E '<error|<failure' tests/_output/report.xml || (echo "report.xml is incomplete, aborting" && false)

  log-header "Rerunning failed tests"
  SECONDS=0
  sudo -E -u www-data vendor/bin/codecept run -g failed
  echo $SECONDS seconds elapsed
fi

log-header "Running chat tests"
SECONDS=0
cd chat
yarn lint && yarn test
echo $SECONDS seconds elapsed

log-header "Done!"
