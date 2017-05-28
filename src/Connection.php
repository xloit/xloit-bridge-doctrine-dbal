<?php
/**
 * This source file is part of Xloit project.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * <http://www.opensource.org/licenses/mit-license.php>
 * If you did not receive a copy of the license and are unable to obtain it through the world-wide-web,
 * please send an email to <license@xloit.com> so we can send you a copy immediately.
 *
 * @license   MIT
 * @link      http://xloit.com
 * @copyright Copyright (c) 2016, Xloit. All rights reserved.
 */

namespace Xloit\Bridge\Doctrine\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver as DoctrineDriver;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Exception;
use PDO;

/**
 * A {@link Connection} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL
 */
class Connection extends DoctrineConnection implements ConnectionInterface
{
    /**
     * How long we should wait before restart locked transaction.
     *
     * @var int
     */
    protected $transactionRestartDelay = 5;

    /**
     * How long we should reconnect transaction.
     *
     * @var int
     */
    protected $reconnectAttempts = 3;

    /**
     * Constructor to prevent {@link Connection} from being loaded more than once.
     *
     * @param array              $params
     * @param DoctrineDriver     $driver
     * @param Configuration|null $config
     * @param EventManager|null  $eventManager
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        array $params,
        DoctrineDriver $driver,
        /** @noinspection PhpInternalEntityUsedInspection */
        Configuration $config = null,
        EventManager $eventManager = null
    ) {
        parent::__construct($params, $driver, $config, $eventManager);

        if (array_key_exists('reconnect_attempts', $params['driverOptions'])
            && $this->isDriverSupportReconnectExceptions()
        ) {
            // sanity check: 0 if no exceptions are available
            /** @noinspection PhpUndefinedMethodInspection */
            $reconnectExceptions = $this->_driver->getReconnectExceptions();

            $this->reconnectAttempts = (count($reconnectExceptions) > 0) ?
                (int) $params['driverOptions']['reconnect_attempts'] : 0;
        }
    }

    /**
     * Executes an, optionally parametrized, SQL query. If the query is parametrized, a prepared statement is used.
     * If an SQLLogger is configured, the execution is logged.
     *
     * @param string                 $query
     * @param array                  $params
     * @param array                  $types
     * @param QueryCacheProfile|null $qcp
     *
     * @return StatementInterface
     * @throws \Exception
     */
    public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null)
    {
        $statement = null;
        $attempt   = 0;

        do {
            $retry = false;

            try {
                /** @noinspection PhpInternalEntityUsedInspection */
                $statement = parent::executeQuery($query, $params, $types, $qcp);
            } catch (Exception $e) {
                error_log('');
                error_log('DBAL EXCEPTION THROWN');
                error_log('└ transaction nesting level: ' . $this->getTransactionNestingLevel());
                error_log(' └ error: ' . $e->getMessage());
                error_log('');

                if ($this->validateReconnectAttempt($e, $attempt)) {
                    error_log('  └ OK - successfully validated to reconnect');

                    $this->close();

                    /** @noinspection PhpUndefinedMethodInspection */
                    if ($this->_driver->shouldStall($e)) {
                        error_log('   ├ wait state deemed beneficial, sleeping 5 seconds...');

                        $this->delay();
                    }

                    $retry = true;

                    $attempt++;
                } else {
                    error_log('  └ FAIL - could not be validated to reconnect');

                    throw $e;
                }
            }
        } while ($retry);

        return $statement;
    }

    /**
     * Executes an SQL statement, returning a result set as a Statement object.
     *
     * @return StatementInterface
     * @throws \Exception
     */
    public function query()
    {
        $statement = null;
        $args      = func_get_args();
        $attempt   = 0;
        $retry     = false;
        $logger    = $this->_config->getSQLLogger();

        do {
            /** @noinspection BadExceptionsProcessingInspection */
            try {
                $this->connect();

                if ($logger) {
                    $logger->startQuery($args[0]);
                }

                try {
                    $statement = call_user_func_array(
                        [
                            $this->_conn,
                            'query'
                        ], $args
                    );

                    /** @var StatementInterface $statement */
                    $statement->setFetchMode(PDO::FETCH_ASSOC);
                } catch (Exception $ex) {
                    throw DBALException::driverExceptionDuringQuery($this->_driver, $ex, $args[0]);
                }

                if ($logger) {
                    $logger->stopQuery();
                }
            } catch (Exception $e) {
                if ($this->validateReconnectAttempt($e, $attempt)) {
                    $this->close();

                    $retry = true;

                    $attempt++;
                } else {
                    throw $e;
                }
            }
        } while ($retry);

        return $statement;
    }

    /**
     * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters and returns the number of affected rows.
     * This method supports PDO binding types as well as DBAL mapping types.
     *
     * @param string $query
     * @param array  $params
     * @param array  $types
     *
     * @return bool
     * @throws \Exception
     */
    public function executeUpdate($query, array $params = [], array $types = [])
    {
        $statement = null;
        $attempt   = 0;
        $retry     = false;

        do {
            try {
                /** @noinspection PhpInternalEntityUsedInspection */
                $statement = parent::executeUpdate($query, $params, $types);
            } catch (Exception $e) {
                if ($this->validateReconnectAttempt($e, $attempt)) {
                    $this->close();

                    $retry = true;

                    $attempt++;
                } else {
                    throw $e;
                }
            }
        } while ($retry);

        return $statement;
    }

    /**
     * Indicates whether the current transaction must reconnected.
     *
     * @param Exception $e
     * @param int       $attempt
     *
     * @return bool
     */
    public function validateReconnectAttempt(Exception $e, $attempt)
    {
        if ($attempt < $this->reconnectAttempts
            && $this->getTransactionNestingLevel() < 1
            && $this->isCanReconnectExceptions()
        ) {
            /** @noinspection PhpUndefinedMethodInspection */
            /** @var array $reconnectExceptions */
            $reconnectExceptions = $this->_driver->getReconnectExceptions();

            foreach ($reconnectExceptions as $reconnectException) {
                if (stripos($e->getMessage(), $reconnectException) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Execute update query. Return number of affected rows.
     *
     * @param string $query
     * @param array  $params
     * @param int    $maxAttempts
     *
     * @return bool
     * @throws \Exception
     */
    public function locksSafeUpdate($query, array $params = [], $maxAttempts = 3)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $this->executeUpdate($query, $params);
            } catch (Exception $e) {
                /**
                 * We need try execute query again in case of following MySQL errors:
                 * Error: 1205 SQLSTATE: HY000 (ER_LOCK_WAIT_TIMEOUT) Message:
                 *     Lock wait timeout exceeded; try restarting transaction
                 * Error: 1213 SQLSTATE: 40001 (ER_LOCK_DEADLOCK) Message:
                 *     Deadlock found when trying to get lock; try restarting transaction
                 */
                if ($attempt === $maxAttempts
                    || stripos(strtolower($e->getMessage()), 'try restarting transaction') === false
                ) {
                    throw $e;
                }

                $this->delay();
            }
        }

        return 0;
    }

    /**
     * Prepares an SQL statement.
     *
     * @param string $statement The SQL statement to prepare.
     *
     * @return StatementInterface The prepared statement.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function prepare($statement)
    {
        try {
            $stmt = new Statement($statement, $this);
        } catch (Exception $ex) {
            throw DBALException::driverExceptionDuringQuery($this->_driver, $ex, $statement);
        }

        $stmt->setFetchMode($this->defaultFetchMode);

        return $stmt;
    }

    /**
     *
     *
     * @return bool
     */
    protected function isCanReconnectExceptions()
    {
        return $this->reconnectAttempts && $this->isDriverSupportReconnectExceptions();
    }

    /**
     *
     *
     * @return bool
     */
    protected function isDriverSupportReconnectExceptions()
    {
        return $this->_driver instanceof Driver\DriverInterface
               || method_exists($this->_driver, 'getReconnectExceptions');
    }

    /**
     * Wait before restart locked transaction.
     *
     * @return void
     */
    protected function delay()
    {
        sleep($this->transactionRestartDelay);
    }
}
