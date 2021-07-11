<?php

namespace App\Helpers;

use Dotenv\Dotenv;

class EnvHelper
{
    public function __invoke($filePath, $key, $default = null)
    {
        if (file_exists($filePath . '.env')) {
            $dotenv = Dotenv::createImmutable($filePath);
            $dotenv->load();
            unset($dotenv);
        } else {
            return '';
        }
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}