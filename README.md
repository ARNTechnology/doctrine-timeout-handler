# arntech/doctrine-timeout-handler
[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Total Downloads][badge-downloads]][downloads]

## What is this?
It is a library that tries to handle DB connection timeouts and "The MySQL server has gone away (error 2006)" errors.

## Installation

The preferred method of installation is via [Composer][]. Run the following
command to install the package and add it as a requirement to your project's
`composer.json`:

```bash
composer require arntech/doctrine-timeout-handler
```
## Dependency
The library uses [psr/log][https://packagist.org/packages/psr/log] and [facile-it/doctrine-mysql-come-back][https://packagist.org/packages/facile-it/doctrine-mysql-come-back]

## Configuration
It a similar configuration as facile-it solution (for a quick migration).
```php
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

$config = new Configuration();

$connectionParams = array(
    'dbname' => 'mydb',
    'user' => 'user',
    'password' => 'secret',
    'host' => 'localhost',
    // [doctrine-mysql-come-back] settings
    'wrapperClass' => 'ARNTech\DoctrineTimeout\Connection',
    'driverClass' => 'ARNTech\DoctrineTimeout\Driver\PDOMySql\Driver',
    'driverOptions' => array(
        'x_reconnect_attempts' => 3,
		'check_connection_beforehand' => true //this specifies to check if connection is still alive before executing anything
    )
);

$conn = DriverManager::getConnection($connectionParams, $config);
```

An example of yaml configuration on Symfony 2 projects:

```yaml
# Doctrine example Configuration
doctrine:
    dbal:
        default_connection: %connection_name%
        connections:
            %connection_name%:
                host:     %database_host%
                port:     %database_port%
                dbname:   %database_name%
                user:     %database_user%
                password: %database_password%
                charset:  UTF8
                wrapper_class: 'ARNTech\DoctrineTimeout\Connection'
                driver_class: 'ARNTech\DoctrineTimeout\Driver\PDOMySql\Driver'
                options:
                    x_reconnect_attempts: 3
					check_connection_beforehand: true #this specifies to check if connection is still alive before executing anything
```

If you are setting up your database connection using a DSN/`database_url` env variable (like the Doctrine Symfony Flex recipe suggests) **you need to remove the protocol** from your database url.
Otherwise, Doctrine is going to ignore your `driver_class` configuration and use the default protocol driver, which will lead you to an error.

```yaml
doctrine:
    dbal:
        connections:
            default:
                # DATABASE_URL needs to be without driver protocol.  
                # use "//db_user:db_password@127.0.0.1:3306/db_name"
                # instead of "mysql://db_user:db_password@127.0.0.1:3306/db_name" 
                url: '%env(resolve:DATABASE_URL)%'
                wrapper_class: 'ARNTech\DoctrineTimeout\Connection'
                driver_class: 'ARNTech\DoctrineTimeout\Driver\PDOMySql\Driver'
                options:
                    x_reconnect_attempts: 3
					check_connection_beforehand: true #this specifies to check if connection is still alive before executing anything

``` 

An example of configuration on Zend Framework 2/3 projects:

```php
return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \ARNTech\DoctrineTimeout\Driver\PDOMySql\Driver::class,
                'wrapperClass' => \ARNTech\DoctrineTimeout\Connection::class,
                'params' => [
                    'host' => 'localhost',
                    'port' => '3307',
                    'user' => '##user##',
                    'password' => '##password##',
                    'dbname' => '##database##',
                    'charset' => 'UTF8',
                    'driverOptions' => [
                        'x_reconnect_attempts' => 9,
						'check_connection_beforehand' => true //this specifies to check if connection is still alive before executing anything
                    ]
                ],
            ],
        ],
    ],
];
```


[badge-source]: https://img.shields.io/static/v1?label=source&message=arntech/doctrine-timeout-handler&color=blue&style=flat-square
[badge-release]: https://img.shields.io/packagist/v/arntech/doctrine-timeout-handler.svg?style=flat-square&label=release
[badge-license]: https://img.shields.io/packagist/l/arntech/doctrine-timeout-handler.svg?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/arntech/doctrine-timeout-handler.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/arntech/doctrine-timeout-handler.svg?style=flat-square&colorB=mediumvioletred

[source]: https://github.com/ARNTechnology/doctrine-timeout-handler
[release]: https://packagist.org/packages/arntech/doctrine-timeout-handler
[license]: https://github.com/ARNTechnology/doctrine-timeout-handler/blob/master/LICENSE
[php]: https://php.net
[downloads]: https://packagist.org/packages/arntech/doctrine-timeout-handler