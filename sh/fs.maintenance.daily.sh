#!/bin/bash
# cleanup tmp objects
/usr/bin/find /var/www/production/shared/tmp/ -mtime +2 -type f -delete
/usr/bin/find /var/www/production/shared/data/mailattach/tmp/ -mtime +2 -type f -delete
# daily maintenance
cd /var/www/production/current/
FS_ENV=prod php-production run.php Maintenance daily
