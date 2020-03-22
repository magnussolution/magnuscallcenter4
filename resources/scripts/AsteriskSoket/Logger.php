<?php
/**
 *
 */
class Logger
{
    public static function write($line)
    {
        $filename = '/var/www/html/callcenter/protected/runtime/socket.log';
        $data     = print_r(date('Y-m-d h:i:s') . "->" . $line . "\n", true);
        error_log($data, 3, $filename);
        //file_put_contents(self::$filename, $data, FILE_APPEND);
    }

}
