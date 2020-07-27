<?php
/**
 * Copyright (c) 2020
 * Alexandru Negrilã (alex-codes@arntech.ro) - ARN TECHNOLOGY
 */

namespace ARNTech\DoctrineTimeout;

use ARNTech\DoctrineTimeout\Traits\ConnectionTrait;
use Facile\DoctrineMySQLComeBack\Doctrine\DBAL\Connection as BaseConnection;

class Connection extends BaseConnection
{
    use ConnectionTrait;
}