<?php

use App\Helpers\IntlDateTime;

function basePath()
{
    return '/store/public';
}

function filePath($file)
{
    return '/store/public/file/' . $file;
}

function miladiToShamsi($date)
{
    try {
        $date = new IntlDateTime($date, 'Asia/Tehran', 'persian', 'fa');
    } catch (Exception $e) {
        return $date;
    }
//    return $date->format('yyyy/MM/dd'); // ۱۳۸۹/۰۲/۱۰
    return $date->format('yyyy/MM/dd');
}

function dateFormatter($date)
{
    return date('jS F Y g:ia', strtotime($date));
}

function trim_characters($string, $length)
{
    if (strlen($string) > $length) {
        return substr($string, 0, strpos($string, ' ', $length));
    }
    return $string;
}

function envY($filePath, $key, $default = null)
{
    return (new App\Helpers\EnvHelper())($filePath, $key, $default);
}

function loader(string $dir): array
{
    if (!is_dir($dir)) {
        return false;
    }
    $config_dir = scandir($dir);
    $ex_config_folders = array('..', '.');
    $filesInConfig = array_diff($config_dir, $ex_config_folders);

    $configs = [];
    foreach ($filesInConfig as $config_file) {
        $file = include $dir .DIRECTORY_SEPARATOR. $config_file;
        if (is_array($file)) {
//                $configs = array_merge($file, $configs);
            $configs += $file;
        }
    }
    return $configs;
}