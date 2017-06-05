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

namespace Xloit\Bridge\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

/**
 * An {@link EmailAddressType} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
class EmailAddressType extends StringType
{
    /**
     *
     *
     * @var string
     */
    const NAME = 'emailAddress';

    /**
     * Converts SQL value to the PHP representation.
     *
     * @param string           $value
     * @param AbstractPlatform $platform
     *
     * @return Mapping\EmailAddress
     * @throws \Xloit\Bridge\Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }

        return new Mapping\EmailAddress($value);
    }

    /**
     * Converts PHP representation to the SQL value.
     *
     * @param Mapping\EmailAddress|string $value
     * @param AbstractPlatform            $platform
     *
     * @return string
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof Mapping\EmailAddress) {
            return (string) $value;
        }

        throw ConversionException::conversionFailed(
            is_scalar($value) ? $value : gettype($value), $this->getName()
        );
    }

    /**
     * If this Doctrine Type maps to an already mapped database type, reverse schema engineering can't take them apart.
     * You need to mark one of those types as commented, which will have Doctrine use an SQL comment to type hint the
     * actual Doctrine Type.
     *
     * @param AbstractPlatform $platform
     *
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * Gets the default length of this type.
     *
     * @param AbstractPlatform $platform
     *
     * @return integer|null
     */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return 255;
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }
}
