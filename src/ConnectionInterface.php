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

use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Exception;

/**
 * A {@link ConnectionInterface} interface.
 *
 * @package Xloit\Bridge\Doctrine\DBAL
 */
interface ConnectionInterface extends DriverConnection
{
    /**
     * Closes the connection.
     *
     * @return void
     */
    public function close();

    /**
     * Indicates whether the current transaction must reconnected.
     *
     * @param Exception $e
     * @param int       $attempt
     *
     * @return boolean
     */
    public function validateReconnectAttempt(Exception $e, $attempt);
}
