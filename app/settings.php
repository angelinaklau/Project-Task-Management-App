<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;
use Medoo\Medoo;
use Dotenv\Dotenv;

return function (ContainerBuilder $containerBuilder) {

    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            $database = new Medoo([
                'type' => $_ENV['DB_TYPE'],
                'host' => $_ENV['DB_HOST'],
                'database' => $_ENV['DB_NAME'],
                'username' => $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASS'],
                'charset' => $_ENV['DB_CHARSET'],
            ]);

            return new Settings([
                'displayErrorDetails' => true,
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'database' => $database,
            ]);
        }
    ]);
};
