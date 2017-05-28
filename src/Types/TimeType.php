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
use Doctrine\DBAL\Types\TimeType as BaseTimeType;
use Xloit\DateTime\DateTime;

/**
 * A {@link TimeType} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
class TimeType extends BaseTimeType
{
    /**
     *
     *
     * @param string           $value
     * @param AbstractPlatform $platform
     *
     * @return DateTime
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return DateTime::instance(parent::convertToPHPValue($value, $platform));
    }
}
