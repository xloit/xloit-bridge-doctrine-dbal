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

use DateTime as PhpDateTime;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;
use Xloit\DateTime\DateFormatter;
use Xloit\DateTime\DateTime;

/**
 * An {@link AbstractTimeZoneDateTimeType} abstract class.
 *
 * @abstract
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
abstract class AbstractTimeZoneDateTimeType extends DateTimeType
{
    /**
     *
     *
     * @var DateTimeZone
     */
    public static $utc;

    /**
     *
     *
     * @param PhpDateTime      $value
     * @param AbstractPlatform $platform
     *
     * @return string
     * @throws \Xloit\DateTime\Exception\InvalidArgumentException
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!($value instanceof PhpDateTime)) {
            throw ConversionException::conversionFailed(is_scalar($value) ? $value : gettype($value), $this->getName());
        }

        $value->setTimezone($this->getDateTimeZone());

        return $value->format($this->getFormat($platform));
    }

    /**
     *
     *
     * @param  string           $value
     * @param  AbstractPlatform $platform
     *
     * @return PhpDateTime|mixed|null
     * @throws \Xloit\DateTime\Exception\InvalidArgumentException
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof PhpDateTime) {
            return $value;
        }

        $format   = $this->getFormat($platform);
        $timezone = $this->getDateTimeZone();

        $dateTime = PhpDateTime::createFromFormat($format, $value, $timezone);

        if (!$dateTime) {
            $dateTime = date_create($value);
        }

        if (!$dateTime) {
            throw ConversionException::conversionFailedFormat(
                $value, $this->getName(), $this->getFormat($platform)
            );
        }

        $dateTime = DateTime::instance($dateTime);

        $dateTime->setFormat($format);

        return $dateTime;
    }

    /**
     * Gets format of storage.
     *
     * @param  AbstractPlatform $platform
     *
     * @return string
     */
    protected function getFormat(AbstractPlatform $platform)
    {
        return $platform->getDateTimeFormatString();
    }

    /**
     * Gets DateTimeZone.
     *
     * @return DateTimeZone
     * @throws \Xloit\DateTime\Exception\InvalidArgumentException
     */
    protected function getDateTimeZone()
    {
        if (!static::$utc) {
            static::$utc = DateFormatter::getTimezone();
        }

        return static::$utc;
    }
}
