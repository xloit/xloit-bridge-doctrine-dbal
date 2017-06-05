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

/**
 * An {@link EnumGenderType} class.
 *
 * @package Xloit\Bridge\Doctrine\DBAL\Types
 */
class EnumGenderType extends AbstractEnumType
{
    /**
     *
     *
     * @var string
     */
    const ENUM_GENDER = 'enumGender';

    /**
     *
     *
     * @var int
     */
    const MAN = 1;

    /**
     *
     *
     * @var int
     */
    const WOMAN = 2;

    /**
     * Array of ENUM Values, where ENUM values are keys and their readable versions are values.
     *
     * @var array
     */
    protected static $choices = [
        self::MAN   => 'Man',
        self::WOMAN => 'Woman'
    ];

    /**
     * Name of this type.
     *
     * @var string
     */
    protected $name = self::ENUM_GENDER;
}
