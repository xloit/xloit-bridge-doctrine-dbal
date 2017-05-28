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
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonArrayType as AbstractJsonType;
use Zend\Json\Json;

/**
 * A {@link JsonType} class to save parameters as pretty json object.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
class JsonType extends AbstractJsonType
{
    /**
     *
     *
     * @var string
     */
    const JSON = 'json';

    /**
     *
     *
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return string
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $encoded = Json::encode($value);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $encoded;
    }

    /**
     *
     *
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Zend\Json\Exception\RuntimeException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $decoded = Json::decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $decoded;
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
        return static::JSON;
    }

    /**
     * Get the latest json error message.
     * This method declaration has been extracted from symfony's php 5.5 polyfill.
     *
     * @link   https://github.com/symfony/polyfill-php55/blob/master/Php55.php
     * @link   http://nl1.php.net/manual/en/function.json-last-error-msg.php
     *
     * @return string
     */
    protected function getLastErrorMessage()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'No error';

            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';

            case JSON_ERROR_STATE_MISMATCH:
                return 'State mismatch (invalid or malformed JSON)';

            case JSON_ERROR_CTRL_CHAR:
                return 'Control character error, possibly incorrectly encoded';

            case JSON_ERROR_SYNTAX:
                return 'Syntax error';

            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';

            default:
                return 'Unknown error';
        }
    }
}
