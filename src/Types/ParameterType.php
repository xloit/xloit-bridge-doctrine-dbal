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

use ArrayObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Xloit\Std\ArrayUtils;

/**
 * A {@link ParameterType} class to save parameters as pretty json object.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
class ParameterType extends JsonType
{
    /**
     *
     *
     * @var string
     */
    const PARAMETER = 'parameter';

    /**
     *
     *
     * @param mixed|ArrayObject $value
     * @param AbstractPlatform  $platform
     *
     * @return string
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Zend\Json\Exception\RuntimeException
     * @throws \Zend\Stdlib\Exception\InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }

        return parent::convertToDatabaseValue(ArrayUtils::iteratorToArray($value), $platform);
    }

    /**
     *
     *
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return ArrayObject
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Zend\Json\Exception\RuntimeException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return new ArrayObject();
        }

        return new ArrayObject(parent::convertToPHPValue($value, $platform));
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return static::PARAMETER;
    }
}
