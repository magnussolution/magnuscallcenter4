#!/bin/bash
clear
echo
echo "----------- Send backup to another SSH Server: ----------"
echo "-------------- www.magnuscallcenter.com ---------------"
echo "------------- info@magnussolution.com ---------------"
echo
sleep 1 

echo "Connecting ssh server PARAGUAY"
DIA=`date "1 days ago" +"%d/%m/%Y"`
MES=`date +%m`
ANO=`date +%Y`


ssh -t  $1 "mkdir -p /backup/audios/$MES-$ANO && mkdir -p /backup/database/$MES-$ANO"

ONTEM=`date -d "-1 days" +"%d-%m-%Y"`
ONTEM2=`date -d "-1 days" +"%d%m%Y"`

cd /usr/local/src

echo "ENVIANDO BACKUP DO BANCO DE DADOS"
scp backup_voip_Magnus.$ONTEM.tgz $1:/backup/database/$MES-$ANO

echo "ENVIANDO BACKUP DOS AUDIOS"
cd /var/www/html/llamadas/$ONTEM2
tar czf $ONTEM2.tar.gz *
scp $ONTEM2.tar.gz $1:/backup/audios/$MES-$ANO

echo "Backup send correctly"
