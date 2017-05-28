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
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * A {@link PointType} class to mapping spatial POINT objects.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
class PointType extends Type
{
    /**
     *
     *
     * @var string
     */
    const POINT = 'point';

    /**
     * Returns the SQL declaration snippet for a field of this type.
     *
     * @param array            $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform         The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'POINT';
    }

    /**
     * Converts SQL value to the PHP representation.
     *
     * @param string           $value    value in DB format
     * @param AbstractPlatform $platform DB platform
     *
     * @return Mapping\Point
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }

        if ($platform instanceof MySqlPlatform) {
            /** @noinspection PrintfScanfArgumentsInspection */
            $data = sscanf($value, 'POINT(%f %f)');
        } else {
            /** @noinspection PrintfScanfArgumentsInspection */
            $data = sscanf($value, '(%f,%f)');
        }

        return new Mapping\Point($data[0], $data[1]);
    }

    /**
     * Converts PHP representation to the SQL value.
     *
     * @param Mapping\Point    $value    specific point
     * @param AbstractPlatform $platform DB platform
     *
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }

        $format = '(%f, %f)';

        if ($platform instanceof MySqlPlatform) {
            $format = 'POINT(%f %f)';
        }

        return sprintf($format, $value->getLatitude(), $value->getLongitude());
    }

    /**
     * Does working with this column require SQL conversion functions?
     *
     * This is a metadata function that is required for example in the ORM. Usage of
     * {@link convertToDatabaseValueSQL} and {@link convertToPHPValueSQL} works for any type and mostly does nothing.
     * This method can additionally be used for optimization purposes.
     *
     * @return bool
     */
    public function canRequireSQLConversion()
    {
        return true;
    }

    /**
     * Modifies the SQL expression (identifier, parameter) to convert to a database value.
     *
     * @param string           $sqlExpr
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        if ($platform instanceof MySqlPlatform) {
            return sprintf('PointFromText(%s)', $sqlExpr);
        }

        return parent::convertToDatabaseValueSQL($sqlExpr, $platform);
    }

    /**
     *
     *
     * @param string           $sqlExpr
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        if ($platform instanceof MySqlPlatform) {
            return sprintf('AsText(%s)', $sqlExpr);
        }

        return parent::convertToPHPValueSQL($sqlExpr, $platform);
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
        return static::POINT;
    }
}
