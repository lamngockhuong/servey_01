<?php

namespace App\Traits;

use ErrorException;

trait ClientInformation
{
    public function getClientIP()
    {
        try {
            $wanIp = file_get_contents('http://bot.whatismyipaddress.com');
        } catch (ErrorException $e) {
            $wanIp = 'UNKNOWN';
        }

        $ipaddress = $wanIp . '+';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress .= $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress .= $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress .= $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipaddress .= $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress .= $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress .= $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress .= $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress .= 'UNKNOWN';
        }

        return $ipaddress;
    }
}
