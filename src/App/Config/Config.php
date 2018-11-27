<?php

namespace App\Config;

class Config
{
    public static function getConfigs(): array
    {
        return [
            'dbhost' => 'localhost',
            'dbname' => 'personal-finance',
            'dbuser' => 'root',
            'dbpass' => '654321',
            'logs_path' => __DIR__ . '/../../../var/application.log'
        ];
    }
}