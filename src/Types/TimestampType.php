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

namespace Xloit\Bridge\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Xloit\DateTime\DateTime;

/**
 * A {@link TimestampType} class to maps an SQL TIME to a PHP DateTime object.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
class TimestampType extends UTCDateTimeType
{
    /**
     *
     *
     * @var string
     */
    const TIMESTAMP = 'timestamp';

    /**
     * Modifies the SQL expression (identifier, parameter) to convert to a database value.
     *
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return string
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Xloit\DateTime\Exception\InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_string($value) && $value === 'CURRENT_TIMESTAMP') {
            $value = new DateTime();
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     *
     *
     * @param mixed            $fieldDeclaration
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'timestamp';
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return static::TIMESTAMP;
    }
}
