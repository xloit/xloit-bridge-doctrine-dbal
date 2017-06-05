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
 * @copyright Copyright (c) 2017, Xloit. All rights reserved.
 */

namespace Xloit\Bridge\Doctrine\DBAL\Types\Mapping;

use Xloit\Bridge\Doctrine\DBAL\Exception;
use Zend\Validator\Ip as IpValidator;

/**
 * An {@link Ip} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types\Mapping
 */
class Ip
{
    /**
     *
     *
     * @var string
     */
    protected $ip;

    /**
     * Constructor to prevent {@link Ip} from being loaded more than once.
     *
     * @param string $ip
     *
     * @throws \Xloit\Bridge\Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function __construct($ip)
    {
        $this->setIp($ip);
    }

    /**
     * Gets the ip value.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Sets the ip value.
     *
     * @param string $ip
     *
     * @return $this
     * @throws \Xloit\Bridge\Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function setIp($ip)
    {
        /** @noinspection IsEmptyFunctionUsageInspection */
        if (!is_string($ip) || empty($ip)) {
            throw new Exception\InvalidArgumentException(
                'IP is not a valid type'
            );
        }

        $validator = new IpValidator();

        if (!$validator->isValid($ip)) {
            $messages = $validator->getMessages();

            throw new Exception\InvalidArgumentException(
                array_shift($messages)
            );
        }

        $this->ip = $ip;

        return $this;
    }

    /**
     * Get the string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getIp();
    }
}
