<?php

/**
 * Contains SmarterU\Exceptions\MissingValueException
 *
 * @author     Will Santanen <will.santanen@thecoresolution.com>
 * @copyright  $year$ Core Business Solutions
 * @license    Proprietary
 * @version    $version$
 * @since      2022/07/25
 */

declare(strict_types=1);

namespace CBS\SmarterU\Exceptions;

/**
 * An exception type to use when the XML request to be made to the SmarterU
 * API cannot be created because one or more required values are missing.
 */
class MissingValueException extends \Exception {
}
