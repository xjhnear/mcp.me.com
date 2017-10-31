#!/bin/bash
PHP="/usr/bin/php"
PHPSCRIPT="/mnt/www/yxd_club/yxd_minbbs/apps/api/artisan command:clearcache"

$PHP $PHPSCRIPT >> /var/www/receve.out &
chpid="$!"
echo "$chpid">>/var/www/receve.sid
echo "child pid is $chpid"
echo "status is $?"
while [ 1 ]
do
wait $chpid
exitstatus="$?"
echo "child pid=$chpid is gone, $exitstatus" >>/var/www/receve.php_error.log
echo `date` >>/var/www/receve.php_error.log
echo "**************************" >>/var/www/receve.php_error.log
sleep 10
$PHP $PHPSCRIPT >> /var/www/receve.out &
chpid="$!"
echo "$chpid">>/var/www/receve.sid
echo "child pid is $chpid"
echo "status is $?"
done