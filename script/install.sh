#!/bin/bash
clear
echo
echo
echo
echo "=================================WWW.MAGNUSSOLUTION.COM===================================";
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


sleep 3

VERSION='4'

sed 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config > borra && mv -f borra /etc/selinux/config


echo
echo '----------- Install MagnusCallcenter dependences ----------'
echo
sleep 1
clear

echo '[mariadb]
name = MariaDB
baseurl = https://yum.mariadb.org/10.9/centos7-amd64
gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB
gpgcheck=1
sslverify=0' > /etc/yum.repos.d/MariaDB.repo 

yum clean all
yum -y install kernel-devel.`uname -m` epel-release
yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum -y install yum-utils gcc.`uname -m` gcc-c++.`uname -m` make.`uname -m` git.`uname -m` wget.`uname -m` bison.`uname -m` openssl-devel.`uname -m` ncurses-devel.`uname -m` doxygen.`uname -m` newt-devel.`uname -m` mlocate.`uname -m` lynx.`uname -m` tar.`uname -m` wget.`uname -m` nmap.`uname -m` bzip2.`uname -m` mod_ssl.`uname -m` speex.`uname -m` speex-devel.`uname -m` unixODBC.`uname -m` unixODBC-devel.`uname -m` libtool-ltdl.`uname -m` sox libtool-ltdl-devel.`uname -m` flex.`uname -m` screen.`uname -m` autoconf automake libxml2.`uname -m` libxml2-devel.`uname -m` sqlite* subversion
yum-config-manager --enable remi-php71
yum -y install php.`uname -m` php-cli.`uname -m` php-devel.`uname -m` php-gd.`uname -m` php-mbstring.`uname -m` php-pdo.`uname -m` php-xml.`uname -m` php-xmlrpc.`uname -m` php-process.`uname -m` php-posix libuuid uuid uuid-devel libuuid-devel.`uname -m`
yum -y install jansson.`uname -m` jansson-devel.`uname -m` unzip.`uname -m` ntpd
yum -y install mysql mariadb-server  mariadb-devel mariadb php-mysql mysql-connector-odbc
yum -y install xmlstarlet libsrtp libsrtp-devel dmidecode gtk2-devel binutils-devel svn libtermcap-devel libtiff-devel audiofile-devel cronie cronie-anacron
yum -y install perl perl-libwww-perl perl-LWP-Protocol-https perl-JSON cpan flac libcurl-devel nss
yum -y install libpcap-devel autoconf automake git ncurses-devel mpg123 sox cpan



systemctl enable httpd
systemctl enable mariadb
systemctl start mariadb
chkconfig ntpd on


genpasswd() 
{
    length=$1
    [ "$length" == "" ] && length=16
    tr -dc A-Za-z0-9_ < /dev/urandom | head -c ${length} | xargs
}
password=$(genpasswd)

if [ -e "/root/passwordMysql.log" ] && [ ! -z "/root/passwordMysql.log" ]
then
    password=$(awk '{print $1}' /root/passwordMysql.log)
fi

touch /root/passwordMysql.log
echo "$password" > /root/passwordMysql.log 


clear
echo
echo "----------- Creat password mysql: Your mysql root password is $password ----------"
echo

chmod -R 777 /tmp
sleep 2
systemctl start mariadb

mysql -uroot -e "SET PASSWORD FOR 'root'@localhost = PASSWORD('${password}'); FLUSH PRIVILEGES;"


clear
echo
echo '----------- Download MagnusCallcenter $VERSION  ----------'
echo
sleep 1
mkdir -p /var/www/html/callcenter
cd /var/www/html
git clone https://github.com/magnussolution/magnuscallcenter4.git callcenter


echo
echo "----------- Installing the new Database ----------"
echo
sleep 2
CallCenterMysqlPass=$(genpasswd)
mysql -uroot -p${password} -e "create database callcenter;"
mysql -uroot -p${password} -e "CREATE USER 'CallCenterUser'@'localhost' IDENTIFIED BY '${CallCenterMysqlPass}';"
mysql -uroot -p${password} -e "GRANT ALL PRIVILEGES ON \`callcenter\` . * TO 'CallCenterUser'@'localhost' WITH GRANT OPTION;FLUSH PRIVILEGES;"    
mysql -uroot -p${password} -e "GRANT FILE ON * . * TO  'CallCenterUser'@'localhost' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;"

mysql callcenter -uroot -p$password  < /var/www/html/callcenter/doc/script.sql


echo
echo '----------- Install PJPROJECT ----------'
echo
sleep 1
cd /usr/src
wget http://www.digip.org/jansson/releases/jansson-2.7.tar.gz
tar -zxvf jansson-2.7.tar.gz
cd jansson-2.7
./configure
make clean
make && make install
ldconfig


clear
echo
echo '----------- Install Asterisk 16 ----------'
echo
sleep 1



cd /usr/src
rm -rf asterisk*

cd /usr/src/
wget http://downloads.asterisk.org/pub/telephony/asterisk/asterisk-16-current.tar.gz
tar xvfz asterisk-16-current.tar.gz
rm -f asterisk-16-current.tar.gz
cd asterisk-*
groupadd asterisk
useradd -r -d /var/lib/asterisk -g asterisk asterisk
mkdir /var/run/asterisk
mkdir /var/log/asterisk
chown -R asterisk:asterisk /var/run/asterisk
chown -R asterisk:asterisk /var/log/asterisk
make clean
contrib/scripts/install_prereq install
./configure --with-jansson-bundled --with-pjproject-bundled
make menuselect.makeopts
menuselect/menuselect --enable res_config_mysql  menuselect.makeopts
menuselect/menuselect --enable format_mp3  menuselect.makeopts
menuselect/menuselect --enable codec_opus  menuselect.makeopts
menuselect/menuselect --enable codec_silk  menuselect.makeopts
menuselect/menuselect --enable codec_siren7  menuselect.makeopts
menuselect/menuselect --enable codec_siren14  menuselect.makeopts
contrib/scripts/get_mp3_source.sh
make
make install
make config
make samples
make config
ldconfig

clear

echo '
noload => chan_sip.so
' >> /etc/asterisk/modules.conf

usermod -aG audio,dialout asterisk
chown -R asterisk.asterisk /etc/asterisk
chown -R asterisk.asterisk /var/{lib,log,spool}/asterisk
chmod -R 777 /tmp

systemctl start asterisk







yum -y remove mysql-connector-odbc
yum -y localinstall --nogpgcheck http://dev.mysql.com/get/Downloads/Connector-ODBC/5.3/mysql-connector-odbc-5.3.4-1.x86_64.rpm
ln -s /usr/lib64/libmyodbc5w.so  /usr/lib64/libmyodbc5.so 

echo '[magnuscallcenter-connector]
Description = MySQL connection to mbilling database
Driver = MySQL
Database = callcenter
Server = localhost
Port = 3306
Socket = /var/lib/mysql/mysql.sock
Driver64=/usr/lib64/libmyodbc5w.so' > /etc/odbc.ini


echo "
[magnuscallcenter]
enabled = yes
dsn = magnuscallcenter-connector
username = root
password = $password
pre-connect = yes
" > /etc/asterisk/res_odbc.conf




echo "
[AMD]
prefix=MAGNUS
dsn=magnuscallcenter
readsql=UPDATE pkg_phonenumber SET status = 1, id_category = 1, last_trying_number = last_trying_number + 1  WHERE id=\'\${SQL_ESC(\${ARG1})}\' LIMIT 1
" > /etc/asterisk/func_odbc.conf

echo "
[general]
total_analysis_time = 5000
silence_threshold = 256
initial_silence = 4000
after_greeting_silence = 2250
greeting = 9000
min_word_length = 100
maximum_word_length = 5000
between_words_silence = 50
maximum_number_of_words = 3
" >  /etc/asterisk/amd.conf


echo "
<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/plain
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/xml
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE text/javascript
	AddOutputFilterByType DEFLATE image/svg+xml
	AddOutputFilterByType DEFLATE image/x-icon
	AddOutputFilterByType DEFLATE application/xml
	AddOutputFilterByType DEFLATE application/xhtml+xml
	AddOutputFilterByType DEFLATE application/rss+xml
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
	DeflateCompressionLevel 9
	BrowserMatch ^Mozilla/4 gzip-only-text/html
	BrowserMatch ^Mozilla/4\.0[678] no-gzip
	BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
	BrowserMatch \bOpera !no-gzip
	DeflateFilterNote Input instream
	DeflateFilterNote Output outstream
	DeflateFilterNote Ratio ratio
	LogFormat '\"%r\" %{outstream}n/%{instream}n (%{ratio}n%%)' deflate
	CustomLog logs/deflate_log DEFLATE
</IfModule>
" >> /etc/httpd/conf.d/deflate.conf

echo "
<IfModule mod_expires.c>
 ExpiresActive On
 ExpiresByType image/jpg \"access plus 60 days\"
 ExpiresByType image/png \"access plus 60 days\"
 ExpiresByType image/gif \"access plus 60 days\"
 ExpiresByType image/jpeg \"access plus 60 days\"
 ExpiresByType text/css \"access plus 1 days\"
 ExpiresByType image/x-icon \"access plus 1 month\"
 ExpiresByType application/pdf \"access plus 1 month\"
 ExpiresByType audio/x-wav \"access plus 1 month\"
 ExpiresByType audio/mpeg \"access plus 1 month\"
 ExpiresByType video/mpeg \"access plus 1 month\"
 ExpiresByType video/mp4 \"access plus 1 month\"
 ExpiresByType video/quicktime \"access plus 1 month\"
 ExpiresByType video/x-ms-wmv \"access plus 1 month\"
 ExpiresByType application/x-shockwave-flash \"access 1 month\"
 ExpiresByType text/javascript \"access plus 1 week\"
 ExpiresByType application/x-javascript \"access plus 1 week\"
 ExpiresByType application/javascript \"access plus 1 week\"
</IfModule>
" >> /etc/httpd/conf.d/expire.conf

echo '<IfModule mime_module>
AddType application/octet-stream .csv
</IfModule>

<Directory "/var/www/html">
    DirectoryIndex index.htm index.html index.php index.php3 default.html index.cgi
</Directory>


<Directory "/var/www/html/callcenter/protected">
    deny from all
</Directory>

<Directory "/var/www/html/callcenter/yii">
    deny from all
</Directory>

<Directory "/var/www/html/callcenter/doc">
    deny from all
</Directory>

<Directory "/var/www/html/callcenter/resources/*log">
    deny from all
</Directory>

<Files "*.sql">
  deny from all
</Files>

<Files "*.log">
  deny from all
</Files>

MaxKeepAliveRequests 1000

' >> /etc/httpd/conf/httpd.conf


sed -i "s/memory_limit = 16M/memory_limit = 512M /" /etc/php.ini
sed -i "s/memory_limit = 128M/memory_limit = 512M /" /etc/php.ini 
sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 3M /" /etc/php.ini 
sed -i "s/post_max_size = 8M/post_max_size = 20M/" /etc/php.ini
sed -i "s/max_execution_time = 30/max_execution_time = 90/" /etc/php.ini
sed -i "s/max_input_time = 60/max_input_time = 120/" /etc/php.ini
sed -i "s/User apache/User asterisk/" /etc/httpd/conf/httpd.conf
sed -i "s/Group apache/Group asterisk/" /etc/httpd/conf/httpd.conf
sed -i "s/\;date.timezone =/date.timezone = America\/Sao_Paulo/" /etc/php.ini


echo "                 
[mysqld]
join_buffer_size = 128M
sort_buffer_size = 2M
read_rnd_buffer_size = 2M
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
secure-file-priv = ''
innodb_strict_mode  = 0
symbolic-links=0
sql_mode=NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES
max_connections = 500
[mysqld_safe]
log-error=/var/log/mariadb/mariadb.log
pid-file=/var/run/mariadb/mariadb.pid
" > /etc/my.cnf



rm -f /etc/localtime
ln -s /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime

systemctl restart  httpd



clear
echo
echo '----------- Installing the Web Interface ----------'
echo
sleep 2

cd /var/www/html/callcenter
chown -R asterisk:asterisk /var/www/html/callcenter
touch /etc/asterisk/extensions_magnus.conf
touch /etc/asterisk/pjsip_magnus.conf
touch /etc/asterisk/pjsip_magnus_user.conf
touch /etc/asterisk/queue_magnus.conf
mkdir /var/run/magnus/
chown -R asterisk:asterisk /var/run/magnus/
cp -rf /var/www/html/callcenter/resources/sounds/br /var/lib/asterisk/sounds

language='br'
cd /var/lib/asterisk
wget https://sourceforge.net/projects/disc-os/files/Disc-OS%20Sounds/1.0-RELEASE/Disc-OS-Sounds-1.0-pt_BR.tar.gz
tar xzf Disc-OS-Sounds-1.0-pt_BR.tar.gz
rm -rf Disc-OS-Sounds-1.0-pt_BR.tar.gz

cp -n /var/lib/asterisk/sounds/pt_BR/*  /var/lib/asterisk/sounds/br
rm -rf /var/lib/asterisk/sounds/pt_BR
mkdir -p /var/lib/asterisk/sounds/br/digits
cp -rf /var/lib/asterisk/sounds/digits/pt_BR/* /var/lib/asterisk/sounds/br/digits
cp -n /var/www/html/mbilling/resources/sounds/br/* /var/lib/asterisk/sounds



echo "[magnuscallcenter]
exten => _X.,1,Set(CHANNEL(accountcode)=\${CHANNEL(endpoint)})
	same => n,AGI(/var/www/html/callcenter/agi.php)

exten => _*22X.,1,Goto(spycall,\${EXTEN},1)

" > /etc/asterisk/extensions_magnus.conf

echo $'
context magnuscallcenterpredictive {
    _X. => {
        if ("${AMD}" == "1")
        {
            Answer();
            Background(en/silence/1);
            AMD();
            if("${AMDSTATUS}"=="MACHINE"){
                Verbose("DESLIGAR CHAMADA ${EXTEN} PORQUE AMD DETECTOU ${AMDSTATUS}");
                MAGNUS_AMD(${PHONENUMBER_ID}); 
                Hangup();
            }
        }
        AGI(/var/www/html/callcenter/agi.php);
        Hangup();
	}
}

//Somente Espiar  221
//Falar com o espiado discar 222
//Falar com o espiado mas sem escutar a chamada 223
context spycall {
    _*22X. => {
        NoOp(Escuta remota);
        Set(CHANNEL(accountcode)=${CHANNEL(endpoint)});
        Answer();
        Authenticate(3003);
        Wait(1);
        if ("${EXTEN:3:1}"="1")
        {
            ChanSpy(PJSIP/${EXTEN:4},qb);
        }
        else if ("${EXTEN:3:1}"="2")
        {
            ChanSpy(PJSIP/${EXTEN:4},qw);
        }
        else if ("${EXTEN:3:1}"="3")
        {
            ChanSpy(PJSIP/${EXTEN:4},qW);
        }
        Hangup();
    }
}
' > /etc/asterisk/extensions.ael

echo '
[general]
autofill=yes
shared_lastcall=yes
persistentmembers=yes
updatecdr=yes

#include queue_magnus.conf

' > /etc/asterisk/queues.conf


echo '
[general]
bindaddr = 0.0.0.0

[transport-udp]
type = transport
protocol = udp
bind = 0.0.0.0:5060

#include pjsip_magnus.conf
#include pjsip_magnus_user.conf
' > /etc/asterisk/pjsip.conf

echo "
[general]
enabled = yes

port = 5038
bindaddr = 0.0.0.0

[magnus]
secret = magnussolution
deny=0.0.0.0/0.0.0.0
permit=127.0.0.1/255.255.255.0
read = system,call,log,verbose,agent,user,config,dtmf,reporting,cdr,dialplan
write = system,call,agent,user,config,command,reporting,originate
" > /etc/asterisk/manager.conf


echo "#include extensions_magnus.conf" >> /etc/asterisk/extensions.conf






ln -s /var/www/html/callcenter/resources/scripts/AsteriskSoket/AsteriskSocket /etc/init.d/

cd /var/www/html/callcenter/resources/scripts/AsteriskSoket/
tar zxvf apps-sys-utils-start-stop-daemon-IR1_9_18-2.tar.gz
cd apps/sys-utils/start-stop-daemon-IR1_9_18-2
gcc start-stop-daemon.c -o start-stop-daemon
cp start-stop-daemon /usr/sbin/


cd /etc/init.d/
mv /etc/init.d/asterisk /tmp/asterisk_old
rm -rf /etc/init.d/asterisk
wget http://magnussolution.com/scriptsSh/asteriskCallCenter
mv asteriskCallCenter asterisk
chmod +x /etc/init.d/asterisk
systemctl daemon-reload

echo "
4 4 * * * php /var/www/html/callcenter/cron.php CallArchive
55 3 * * * php /var/www/html/callcenter/cron.php payments
50 23 * * * php /var/www/html/callcenter/cron.php asistenciackeck
* * * * * php /var/www/html/callcenter/cron.php TurnosCkeck
* * * * * php /var/www/html/callcenter/cron.php massivecall
* * * * * php /var/www/html/callcenter/cron.php Category
* * * * * php /var/www/html/callcenter/cron.php predictive
30 23 * * * php /var/www/html/callcenter/cron.php backup
1 1 * * * /usr/sbin/ntpdate ntp.ubuntu.com pool.ntp.org
0 3 * * * /var/www/html/callcenter/protected/commands/update.sh
0 4 * * * /var/www/html/callcenter/protected/commands/verificamemoria
0 0,12 * * * python -c 'import random; import time; time.sleep(random.random() * 3600)' && /usr/src/certbot-auto renew 
1 1 * * * /usr/sbin/ntpdate ntp.ubuntu.com pool.ntp.org
*/10 * * * * /etc/init.d/AsteriskSocket restart
0 3 * * * /var/www/html/callcenter/protected/commands/clean_asterisk_logs.sh
" > /var/spool/cron/root



echo "[general]
dbhost = 127.0.0.1
dbname = callcenter
dbuser = CallCenterUser
dbpass = ${CallCenterMysqlPass}
" > /etc/asterisk/res_config_mysql.conf






echo "<?php 
header('Location: ./callcenter');
?>
" > /var/www/html/index.php

echo "
User-agent: *
Disallow: /callcenter/
" > /var/www/html/robots.txt


yum install -y epel-release
yum install -y iptables-services

yum install -y iptables-services
rm -rf /etc/fail2ban
cd /tmp
git clone https://github.com/fail2ban/fail2ban.git
cd /tmp/fail2ban
python setup.py install

systemctl mask firewalld.service
systemctl enable iptables.service
systemctl enable ip6tables.service
systemctl stop firewalld.service
systemctl start iptables.service
systemctl start ip6tables.service

systemctl enable iptables

chkconfig --levels 123456 firewalld off


iptables -F
iptables -A INPUT -p icmp --icmp-type echo-request -j ACCEPT
iptables -A OUTPUT -p icmp --icmp-type echo-reply -j ACCEPT
iptables -A INPUT -i lo -j ACCEPT
iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
iptables -A INPUT -p tcp --dport 22 -j ACCEPT
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT
iptables -A INPUT -p udp -m udp --dport 5060 -j ACCEPT
iptables -A INPUT -p udp -m udp --dport 10000:20000 -j ACCEPT
iptables -A INPUT -p tcp -m tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp -m tcp --dport 443 -j ACCEPT
iptables -A INPUT -p tcp -m tcp --dport 8089 -j ACCEPT
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "friendly-scanner" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "sundayddr" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "sipsak" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "sipvicious" --algo bm
iptables -I INPUT -j DROP -p udp --dport 5060 -m string --string "iWar" --algo bm
iptables -A INPUT -j DROP -p udp --dport 5060 -m string --string "sipcli/" --algo bm
iptables -A INPUT -j DROP -p udp --dport 5060 -m string --string "VaxSIPUserAgent/" --algo bm

service iptables save
service iptables restart


echo
echo "Fail2ban configuration!"
echo

echo '
Defaults!/usr/bin/fail2ban-client !requiretty
asterisk ALL=(ALL) NOPASSWD: /usr/bin/fail2ban-client
' >> /etc/sudoers


echo '
[INCLUDES]
[Definition]
failregex = NOTICE.* .*: Useragent: sipcli.*\[<HOST>\] 
ignoreregex =
' > /etc/fail2ban/filter.d/asterisk_cli.conf

echo '
[INCLUDES]
[Definition]
failregex = .*NOTICE.* <HOST> tried to authenticate with nonexistent user.*
ignoreregex =
' > /etc/fail2ban/filter.d/asterisk_manager.conf

echo '
[INCLUDES]
[Definition]
failregex = NOTICE.* .*hangupcause to DB: 200, \[<HOST>\]
ignoreregex =
' > /etc/fail2ban/filter.d/asterisk_hgc_200.conf



echo "
[DEFAULT]
ignoreip = 127.0.0.1
bantime  = 600
findtime  = 600
maxretry = 3
backend = auto
usedns = warn


[asterisk-iptables]   
enabled  = true           
filter   = asterisk       
action   = iptables-allports[name=ASTERISK, port=5060, protocol=all]   
logpath  = /var/log/asterisk/messages 
maxretry = 5  
bantime = 600

[ast-cli-attck]   
enabled  = true           
filter   = asterisk_cli     
action   = iptables-allports[name=AST_CLI_Attack, port=5060, protocol=all]
logpath  = /var/log/asterisk/messages 
maxretry = 1  
bantime = -1

[asterisk-manager]   
enabled  = true           
filter   = asterisk_manager     
action   = iptables-allports[name=AST_MANAGER, port=5038, protocol=all]
logpath  = /var/log/asterisk/messages 
maxretry = 1  
bantime = -1

[ast-hgc-200]
enabled  = true           
filter   = asterisk_hgc_200     
action   = iptables-allports[name=AST_HGC_200, port=5060, protocol=all]
logpath  = /var/log/asterisk/messages
maxretry = 20
bantime = -1

[ssh-iptables]
enabled  = true
filter   = sshd
action   = iptables-allports[name=SSH, port=all, protocol=all]
logpath  = /var/log/secure
maxretry = 3
bantime = 600

" > /etc/fail2ban/jail.local



echo "
[general]
dateformat=%F %T       ; ISO 8601 date format
[logfiles]

;debug => debug
;security => security
console => warning,error
;console => notice,warning,error,debug
messages => notice,warning,error
;full => notice,warning,error,debug,verbose,dtmf,fax

fail2ban => notice
" > /etc/asterisk/logger.conf

mkdir /var/run/fail2ban/
asterisk -rx "module reload logger"
systemctl enable fail2ban 
systemctl restart fail2ban 
iptables -L -v


cd /usr/local/sbin
wget magnussolution.com/download/sip
chmod 777 /usr/local/sbin/*

yum install -y ngrep htop ntp



php /var/www/html/callcenter/cron.php updatemysql

chmod +x /var/www/html/callcenter/resources/asterisk/magnus.php
chown -R asterisk:asterisk /var/spool/asterisk/monitor
chmod -R 750 /var/spool/asterisk/monitor
chown -R asterisk:asterisk /var/spool/asterisk/
chown -R asterisk:asterisk /var/lib/php/session/
chown -R asterisk:asterisk /var/spool/asterisk/outgoing/
chown -R asterisk:asterisk /etc/asterisk
chown -R asterisk:asterisk /var/www/html/callcenter
chmod +x /var/www/html/callcenter/agi.php
chmod -R 777 /tmp
chmod -R 555 /var/www/html/callcenter/
chmod -R 750 /var/www/html/callcenter/resources/reports 
chmod -R 774 /var/www/html/callcenter/protected/runtime/
mkdir -p /var/www/tmpmagnus
chmod -R 777 /var/www/tmpmagnus
mkdir -p /usr/local/src/magnus/monitor
mkdir -p /usr/local/src/magnus/sounds
mkdir -p /usr/local/src/magnus/backup
mv /usr/local/src/backup* /usr/local/src/magnus/backup
chown -R asterisk:asterisk /usr/local/src/magnus/
chmod -R 755 /usr/local/src/magnus/
chmod +x /var/www/html/callcenter/protected/commands/clean_asterisk_logs.sh
chown -R asterisk:asterisk /var/spool/asterisk/outgoing/
chmod 750 /var/www/html/callcenter/tmp
chmod 750 /var/www/html/callcenter/resources/sounds
chmod 770 /var/www/html/callcenter/resources/images
rm -rf /var/www/html/callcenter/doc

clear





p4_proc()
{
    set $(grep "model name" /proc/cpuinfo);

    if [ "$4" == "Celeron" ]; then

        wget http://asterisk.hosting.lv/bin/codec_g723-ast160-gcc4-glibc-pentium.so   
        wget http://asterisk.hosting.lv/bin/codec_g729-ast160-gcc4-glibc-pentium.so
        cp /usr/src/codec_g723-ast160-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g723.so
        cp /usr/src/codec_g729-ast160-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g729.so
         
        return 0;
    fi

    wget http://asterisk.hosting.lv/bin/codec_g723-ast160-gcc4-glibc-pentium4.so   
    wget http://asterisk.hosting.lv/bin/codec_g729-ast160-gcc4-glibc-pentium4.so
    mv /usr/src/codec_g723-ast160-gcc4-glibc-pentium4.so  /usr/lib/asterisk/modules/codec_g723.so
    mv codec_g729-ast160-gcc4-glibc-pentium4.so /usr/lib/asterisk/modules/codec_g729.so            

}
p4_x64_proc()
{         
    wget http://asterisk.hosting.lv/bin/codec_g723-ast160-gcc4-glibc-x86_64-pentium4.so
    wget http://asterisk.hosting.lv/bin/codec_g729-ast160-gcc4-glibc-x86_64-pentium4.so
    mv /usr/src/codec_g723-ast160-gcc4-glibc-x86_64-pentium4.so /usr/lib/asterisk/modules/codec_g723.so
    mv /usr/src/codec_g729-ast160-gcc4-glibc-x86_64-pentium4.so /usr/lib/asterisk/modules/codec_g729.so
      
}
p3_proc()
{       
    set $(grep "model name" /proc/cpuinfo);
    if [ "$4" == "Intel(R)" &&  "$5" == "Pentium(R)" && "$6"== "III" ];then
        wget http://asterisk.hosting.lv/bin/codec_g723-ast160-gcc4-glibc-pentium.so   
        wget http://asterisk.hosting.lv/bin/codec_g729-ast160-gcc4-glibc-pentium.so
        mv /usr/src/codec_g723-ast160-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g723.so
        mv /usr/src/codec_g729-ast160-gcc4-glibc-pentium.so /usr/lib/asterisk/modules/codec_g729.so
        return 0;
    fi
    wget http://asterisk.hosting.lv/bin/codec_g723-ast160-gcc4-glibc-pentium3.so
    wget http://asterisk.hosting.lv/bin/codec_g729-ast160-gcc4-glibc-pentium3.so
    mv /usr/src/codec_g723-ast160-gcc4-glibc-pentium3.so /usr/lib/asterisk/modules/codec_g723.so
    mv /usr/src/codec_g729-ast160-gcc4-glibc-pentium3.so /usr/lib/asterisk/modules/codec_g729.so

}
AMD_proc()
{
    wget http://asterisk.hosting.lv/bin/codec_g729-ast160-gcc4-glibc-athlon-sse.so
    wget http://asterisk.hosting.lv/bin/codec_g723-ast160-gcc4-glibc-athlon-sse.so
    mv /usr/src/codec_g723-ast160-gcc4-glibc-athlon-sse.so /usr/lib/asterisk/modules/codec_g723.so
    mv /usr/src/codec_g729-ast160-gcc4-glibc-athlon-sse.so /usr/lib/asterisk/modules/codec_g729.so

}

processor_type()
{
    _UNAME=`uname -a`;
    _IS_64_BIT=`echo "$_UNAME"  | grep x86_64`
    if [ -n "$_IS_64_BIT" ];
        then _64BIT=1;
        else _64BIT=0;
    fi;
}
clear 
echo "INSTALLING G723 and G729 CODECS......... FROM http://asterisk.hosting.lv";   
cd /usr/src
rm -rf codec_*
processor_type;
    _IS_AMD=`cat /proc/cpuinfo | grep AMD`;
    _P3=`cat /proc/cpuinfo | grep "Pentium III"`;
    _P3_R=`cat /proc/cpuinfo | grep "Pentium(R) III"`;
    _INTEL=`cat /proc/cpuinfo | grep Intel`;
    if [ -n "$_IS_AMD" ];
      then 
          echo "Processor type detected: AMD";
          if  [ "$_64BIT" == 1 ]; then 
            echo "It is a x64 proc";
               p4_x64_proc;
          else 
            echo "AMD processor detected"; 
            AMD_proc;
          fi
       
    elif [ -n "$_P3_R" ]; then echo "Pentium(R) III processor detected"; p3_proc;           
    elif [ "$_64BIT" == 1 ]; then echo "Processor type detected: INTEL x64"; p4_x64_proc;       
    elif [ -n "$_INTEL" ]; then echo "Pentium IV processor detected"; p4_proc;
    elif [ -n "$_P3" ]; then echo "Pentium III processor detected"; p3_proc;
    else
        echo -e "Automatic detection of required codec installation script failed\nYou must manually select and install the required codec according to this output:";
        cat /proc/cpuinfo
        uname -a
        echo "you can find codecs installation scripts in http://asterisk.hosting.lv";
    fi;

asterisk -rx 'module load codec_g729.so'
asterisk -rx 'module load codec_g723.so'
sleep 4
asterisk -rx 'core show translation'


whiptail --title "CallCenter Instalation Result" --msgbox "Congratulations! You have installed MagnusCallCenter in your Server.\n\nAccess your MagnusCallCenter in http://your_ip/ \n  Username = admin \n  Password = magnus \n\nYour mysql root password is $password\n\n\nPRESS ANY KEY TO REBOOT YOUR SERVER" --fb 20 70



reboot