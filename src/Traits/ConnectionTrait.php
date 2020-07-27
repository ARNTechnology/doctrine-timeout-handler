<?php
/**
 * Copyright (c) 2020
 * Alexandru Negrilã (alex-codes@arntech.ro) - ARN TECHNOLOGY
 */

namespace ARNTech\DoctrineTimeout\Traits;


use ARNTech\DoctrineTimeout\ConnetionCheck;

trait ConnectionTrait
{
    /** @var bool */
    protected $checkConnectionBeforehand = false;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        array $params,
        Driver $driver,
        Configuration $config = null,
        EventManager $eventManager = null
    )
    {
        if (isset($params['driverOptions']['check_connection_beforehand'])) {
            $this->checkConnectionBeforehand = filter_var($params['driverOptions']['check_connection_beforehand'], FILTER_VALIDATE_BOOLEAN);
            $params['driverOptions']['check_connection_beforehand'] = null;
            unset($params['driverOptions']['check_connection_beforehand']);
        }
        parent::__construct($params, $driver, $config, $eventManager);
    }

    /**
     * {@inheritDoc}
     */
    public function executeQuery()
    {
        $this->handleConnectionBeforehand();
        call_user_func_array(array('parent', __FUNCTION__), func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $this->handleConnectionBeforehand();
        call_user_func_array(array('parent', __FUNCTION__), func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function executeUpdate()
    {
        $this->handleConnectionBeforehand();
        call_user_func_array(array('parent', __FUNCTION__), func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        $this->handleConnectionBeforehand();
        call_user_func_array(array('parent', __FUNCTION__), func_get_args());
    }

    private function handleConnectionBeforehand()
    {
        if ($this->checkConnectionBeforehand) {
            ConnetionCheck::reconnectIfNeeded($this);
        }
    }
}