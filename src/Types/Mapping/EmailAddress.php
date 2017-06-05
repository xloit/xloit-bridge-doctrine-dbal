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
use Zend\Validator\EmailAddress as EmailAddressValidator;
use Zend\Validator\Hostname;

/**
 * An {@link EmailAddress} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types\Mapping
 */
class EmailAddress
{
    /**
     *
     *
     * @var string
     */
    protected $localPart;

    /**
     *
     *
     * @var string
     */
    protected $domainName;

    /**
     * Constructor to prevent {@link EmailAddress} from being loaded more than once.
     *
     * @param string $localPart
     * @param string $domainName
     *
     * @throws \Xloit\Bridge\Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function __construct($localPart, $domainName = null)
    {
        $email = $localPart;

        if ($domainName) {
            $email = sprintf('%s@%s', $localPart, $domainName);
        }

        /** @noinspection IsEmptyFunctionUsageInspection */
        if (!is_string($email) || empty($email)) {
            throw new Exception\InvalidArgumentException(
                'Email must be a valid email address'
            );
        }

        if (preg_match("/[\r\n]/", $email)) {
            throw new Exception\InvalidArgumentException(
                'CRLF injection detected'
            );
        }

        // Split email address up and disallow '..'
        if (strpos($email, '..') !== false
            || !preg_match(
                '/^(.+)@([^@]+)$/', $email, $matches
            )
        ) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'The input "%s" is not a valid email address. Use the basic format local-part@hostname',
                    $email
                )
            );
        }

        $validator = new EmailAddressValidator(
            Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL
        );

        if (!$validator->isValid($email)) {
            $messages = $validator->getMessages();

            throw new Exception\InvalidArgumentException(
                array_shift($messages)
            );
        }

        /** @noinspection MultiAssignmentUsageInspection */
        $this->localPart = $matches[1];
        /** @noinspection MultiAssignmentUsageInspection */
        $this->domainName = $matches[2];
    }

    /**
     * Gets the localPart value.
     *
     * @return string
     */
    public function getLocalPart()
    {
        return $this->localPart;
    }

    /**
     * Sets the localPart value.
     *
     * @param string $localPart
     *
     * @return $this
     */
    public function setLocalPart($localPart)
    {
        $this->localPart = $localPart;

        return $this;
    }

    /**
     * Gets the domainName value.
     *
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * Sets the domainName value.
     *
     * @param string $domainName
     *
     * @return $this
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;

        return $this;
    }

    /**
     * Get the string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) sprintf('%s@%s', $this->getLocalPart(), $this->getDomainName());
    }
}
