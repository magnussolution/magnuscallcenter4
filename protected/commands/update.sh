#!/bin/bash
clear
echo
echo
echo
echo "=================================WWW.MAGNUSCALLCENTER.COM===================================";
echo "_      _                                ____      			                              ";
echo "|\    /|                               | ___|      _   _ 	                                  ";
echo "| \  / | ___  ____  _ __  _   _  _____ | |    ___ | | | |	  ___  ____ _ __  _____ ____ ___  ";
echo "|  \/  |/   \/  _ \| '_ \| | | \| ___| | |   /   \| | | |  / _ \| __ | '_ \|_  _|| __ | | \ ";
echo "| |\/| |  | |  (_| | | | | |_| ||____  | |__|  | || |_| |_| |_  |	__ | | | | | | | __ | _ / ";
echo "|_|  |_|\___|\___  |_| | |_____|_____|  \___|\___||___|___|\___||____|_| | | | | |____|  \  ";
echo "                _/ |                                           	                          ";
echo "               |__/                                            	                          ";
echo "																		                      ";
echo "============================ OPENSOURCE SYSTEM TO CALLCENTER ===============================";
echo

sleep 2

if [[ -e /var/www/html/callcenter/callcenter/commands/update2.sh ]]; then
	/var/www/html/callcenter/protected/commands/update2.sh
	exit;
fi

cd /var/www/html/callcenter
rm -rf master.tar.gz
wget https://github.com/magnussolution/magnuscallcenter4/archive/master.tar.gz
tar xzf master.tar.gz --strip-components=1


##update database
php /var/www/html/callcenter/cron.php UpdateMysql

## remove unnecessary directories
rm -rf /var/www/html/callcenter/doc
rm -rf /var/www/html/callcenter/script

## set default permissions 
chown -R asterisk:asterisk /var/lib/php/session/
chown -R asterisk:asterisk /var/spool/asterisk/outgoing/
chown -R asterisk:asterisk /etc/asterisk
chown -R asterisk:asterisk /var/www/html/callcenter
chmod -R 777 /tmp
chmod -R 555 /var/www/html/callcenter/
chmod -R 750 /var/www/html/callcenter/resources/reports 
chmod -R 774 /var/www/html/callcenter/protected/runtime/
chmod +x /var/www/html/callcenter/agi.php
mkdir -p /usr/local/src/magnus
chmod -R 777 /usr/local/src/magnus/
chmod -R 750 /var/www/html/callcenter/resources/sounds
chmod -R 750 /var/www/html/callcenter/resources/images
mkdir -p /var/www/tmpmagnus
chmod -R 777 /var/www/tmpmagnus
chmod +x /var/www/html/callcenter/protected/commands/check_service.sh

if [[ -e /var/www/html/callcenter/protected/commands/update3.sh ]]; then
	/var/www/html/callcenter/protected/commands/update3.sh
fi