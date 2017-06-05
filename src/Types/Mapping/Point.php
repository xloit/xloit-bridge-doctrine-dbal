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

namespace Xloit\Bridge\Doctrine\DBAL\Types\Mapping;

/**
 * A {@link Point} class for spatial mapping.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types\Mapping
 */
class Point
{
    /**
     *
     *
     * @var float
     */
    protected $latitude = 0.0;

    /**
     *
     *
     * @var float
     */
    protected $longitude = 0.0;

    /**
     * Creates new Point from string.
     *
     * @param string $points string in (%f,%f) format
     *
     * @return $this
     */
    public static function fromString($points)
    {
        /** @noinspection PrintfScanfArgumentsInspection */
        return static::fromArray(sscanf($points, '(%f,%f)'));
    }

    /**
     * Creates new Point from array.
     *
     * @param array $points either hash or array of lat, long.
     *
     * @return $this
     */
    public static function fromArray(array $points)
    {
        if (array_key_exists('latitude', $points)) {
            return new self($points['latitude'], $points['longitude']);
        }

        return new self($points[0], $points[1]);
    }

    /**
     * Constructor to prevent {@link Point} from being loaded more than once.
     *
     * @param float|int $latitude
     * @param float|int $longitude
     */
    public function __construct($latitude = 0.0, $longitude = 0.0)
    {
        $this->latitude  = (float) $latitude;
        $this->longitude = (float) $longitude;
    }

    /**
     * Returns point latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Returns point longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     *
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->latitude === 0.0 && $this->longitude === 0.0;
    }

    /**
     * Returns string representation for Point in (%f,%f) format.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) sprintf('(%F,%F)', $this->latitude, $this->longitude);
    }
}
