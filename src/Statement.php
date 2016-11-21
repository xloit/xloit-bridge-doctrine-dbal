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

use Doctrine\DBAL\Statement as DoctrineStatement;
use Exception;
use PDO;

/**
 * A {@link Statement} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL
 */
class Statement extends DoctrineStatement
{
    /**
     * Holds the parameter values.
     *
     * @var array
     */
    protected $_values = [];

    /**
     * Holds the parameters.
     *
     * @var array
     */
    protected $_params = [];

    /**
     * Constructor to prevent {@link Statement} from being loaded more than once.
     *
     * @param string     $sql
     * @param Connection $connection
     */
    public function __construct($sql, Connection $connection)
    {
        parent::__construct($sql, $connection);

        $this->createStatement();
    }

    /**
     *
     *
     * @return void
     */
    protected function createStatement()
    {
        foreach ($this->_values as $name => $value) {
            $this->bindValue($name, $value['value'], array_key_exists('type', $value) ? $value['type'] : null);
        }

        foreach ($this->_params as $name => $value) {
            $this->bindParam(
                $name, $value['value'], array_key_exists('type', $value) ? $value['type'] : null,
                array_key_exists('length ', $value) ? $value['length '] : null
            );
        }
    }

    /**
     * Executes the statement with the currently bound parameters.
     *
     * @param array|null $params
     *
     * @return boolean
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($params = null)
    {
        $connection = $this->conn;

        if (!($connection instanceof ConnectionInterface)) {
            return parent::execute($params);
        }

        $statement = null;
        $attempt   = 0;

        do {
            $retry = false;

            try {
                $statement = parent::execute($params);
            } catch (Exception $e) {
                if ($connection->validateReconnectAttempt($e, $attempt)) {
                    $retry = true;

                    $connection->close();
                    $this->createStatement();

                    $attempt++;
                } else {
                    throw $e;
                }
            }
        } while ($retry);

        return $statement;
    }

    /**
     * Binds a parameter value to the statement.
     *
     * @param string $name
     * @param mixed  $value
     * @param mixed  $type
     *
     * @return boolean
     */
    public function bindValue($name, $value, $type = null)
    {
        $this->_values[$name] = [
            'value' => $value,
            'type'  => $type
        ];

        return parent::bindValue($name, $value, $type);
    }

    /**
     * Binds a parameter to a value by reference.
     * Binding a parameter by reference does not support DBAL mapping types.
     *
     * @param string       $name
     * @param mixed        $var
     * @param integer      $type
     * @param integer|null $length
     *
     * @return boolean
     */
    public function bindParam($name, &$var, $type = PDO::PARAM_STR, $length = null)
    {
        $this->_params[$name] = [
            'value'  => &$var,
            'type'   => $type,
            'length' => $length
        ];

        return parent::bindParam($name, $var, $type, $length);
    }

    /**
     * Fetches the next row from a result set.
     *
     * @param integer|null $fetchMode
     *
     * @return mixed
     */
    public function fetch($fetchMode = PDO::FETCH_BOTH)
    {
        return $this->stmt->fetch($fetchMode);
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param integer|null $fetchMode
     * @param mixed        $fetchArgument
     *
     * @return array
     */
    public function fetchAll($fetchMode = PDO::FETCH_BOTH, $fetchArgument = 0)
    {
        return $this->stmt->fetchAll($fetchMode);
    }
}
