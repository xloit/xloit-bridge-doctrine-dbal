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
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Types\Type;
use ReflectionClass;
use Xloit\Bridge\Doctrine\DBAL\Exception;

/**
 * An {@link AbstractEnumType} abstract class provides support of MySQL ENUM type for Doctrine.
 *
 * @abstract
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
abstract class AbstractEnumType extends Type
{
    /**
     *
     *
     * @var string
     */
    const YES = 'y';

    /**
     *
     *
     * @var string
     */
    const NO = 'n';

    /**
     *
     *
     * @var string
     */
    const YES_LONG = 'yes';

    /**
     *
     *
     * @var string
     */
    const NO_LONG = 'no';

    /**
     * Name of this type.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Array of ENUM Values, where ENUM values are keys and their readable versions are values.
     *
     * @var array
     */
    protected static $choices = [];

    /**
     * Converts a value from its PHP representation to its database representation of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return mixed The database representation of the value.
     * @throws Exception\InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!static::isValueExist($value)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid value "%s" for ENUM "%s".',
                    $value,
                    $this->getName()
                )
            );
        }

        return $value;
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array            $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform         The currently used database platform.
     *
     * @return string
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = implode(
            ', ',
            array_map(
                function($value) {
                    if (is_string($value)) {
                        return sprintf("'%s'", $value);
                    }

                    return (int) $value;
                },
                static::getValues()
            )
        );

        // TODO: Add more supported platforms
        if ($platform instanceof SqlitePlatform) {
            return sprintf('TEXT CHECK(%s IN (%s))', $fieldDeclaration['name'], $values);
        }

        if ($platform instanceof PostgreSqlPlatform || $platform instanceof SQLServerPlatform) {
            return sprintf('VARCHAR(255) CHECK(%s IN (%s))', $fieldDeclaration['name'], $values);
        }

        return sprintf('ENUM(%s)', $values);
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
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?: (new ReflectionClass(get_class($this)))->getShortName();
    }

    /**
     * Get readable choices for the ENUM field.
     *
     * @return array Values for the ENUM field.
     */
    public static function getChoices()
    {
        return array_flip(static::$choices);
    }

    /**
     * Get values for the ENUM field.
     *
     * @return array Values for the ENUM field.
     */
    public static function getValues()
    {
        return array_keys(static::$choices);
    }

    /**
     * Get value in readable format.
     *
     * @param string $value ENUM value.
     *
     * @return string|null $value Value in readable format.
     * @throws Exception\InvalidArgumentException
     */
    public static function getReadableValue($value)
    {
        if (!static::isValueExist($value)) {
            throw new Exception\InvalidArgumentException(
                sprintf('Invalid value "%s" for ENUM type "%s".', $value, get_called_class())
            );
        }

        return static::$choices[$value];
    }

    /**
     * Check if some string value exists in the array of ENUM values.
     *
     * @param string $value ENUM value.
     *
     * @return bool
     */
    public static function isValueExist($value)
    {
        /** @noinspection UnSafeIsSetOverArrayInspection */
        return isset(static::$choices[$value]);
    }
}
