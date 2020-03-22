<?php
/**
 * Classe de com funcionalidades globais
 *
 * MagnusCallCenter <info@magnussolution.com>
 * 08/07/2017
 */

class LinuxAccess
{

    public static function exec($command)
    {
        return exec($command);
    }

    public static function system($command)
    {
        return exec($command);
    }

    public static function getDirectoryDiskSpaceUsed($filter = '*', $directory = '/var/spool/asterisk/monitor/')
    {
        $command = 'ls -lR  ' . $directory . $filter . ' | grep -v \'^d\' | awk \'{total += $5} END {print total}\'';
        return @LinuxAccess::exec($command);
    }

    public static function getLastFileInDirectory($filter = '*', $directory = '/var/spool/asterisk/monitor/')
    {
        $command = 'ls -tr ' . $directory . $filter . ' | head -n 1';
        return @LinuxAccess::exec($command);
    }

    public static function deleteFile($file, $directory = '/var/spool/asterisk/monitor/')
    {
        $command = 'rm -f ' . $directory . $file;
        @LinuxAccess::exec($command);
    }
}
