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

namespace Xloit\Bridge\Doctrine\DBAL\Driver\PDOMySql;

use Doctrine\DBAL\Driver\PDOMySql\Driver as DoctrineDriver;
use Exception;
use Xloit\Bridge\Doctrine\DBAL\Driver\DriverInterface;

/**
 * A {@link Driver} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Driver\PDOMySql
 */
class Driver extends DoctrineDriver implements DriverInterface
{
    /**
     *
     *
     * @return array
     */
    public function getReconnectExceptions()
    {
        return [
            'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away',
            'SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: nodename nor servname provided, or not known'
        ];
    }

    /**
     *
     *
     * @param Exception $exception
     *
     * @return bool
     */
    public function shouldStall(Exception $exception)
    {
        return strpos($exception->getMessage(), 'php_network_getaddresses') !== false;
    }
}
