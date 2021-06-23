#!/bin/bash

cd /var/log

rm -rfv *20[1,2][0-9]*
rm -rfv asterisk/*20[1,2][0-9]*
rm -rfv httpd/*20[1,2][0-9]*
mkdir -p asterisk
mkdir -p httpd


rm -rf *20[1,2][0-9]*
rm -rf asterisk/*20[1,2][0-9]*
rm -rf asterisk/cdr-csv/*
rm -rf httpd/*20[1,2][0-9]*

rm -rf /var/www/html/callcenter/protected/runtime/*.log.*
echo '' > /var/www/html/callcenter/protected/runtime/socket.log
echo '' > /var/www/html/callcenter/protected/runtime/predictive.log
echo '' > /var/log/AsteriskSocket.log
echo '' > /var/log/fail2ban.log
echo '' > /var/log/messages
echo '' > /var/log/opensips
echo '' > /var/log/secure
echo '' > /var/log/maillog
echo '' > /var/log/mysqld.log
echo '' > /var/log/cron
echo '' > /var/log/asteriskSlave.log
echo '' > /var/log/asterisk/messages
echo '' > /var/log/asterisk/fail2ban
echo '' > /var/log/asterisk/queue_log
echo '' > /var/log/httpd/access_log
echo '' > /var/log/httpd/error_log
echo '' > /var/log/httpd/deflate_log
echo '' > /var/log/httpd/ssl_access_log
echo '' > /var/log/httpd/ssl_error_log
echo '' > /var/log/httpd/ssl_request_log
