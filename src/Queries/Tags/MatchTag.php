<?php

/**
 * Contains SmarterU\Queries\MatchTag.php.
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     Proprietary
 * @version     $version
 * @since       2022/07/13
 */

declare(strict_types=1);

namespace SmarterU\Queries\Tags;

use SmarterU\Exceptions\InvalidArgumentException;

/**
 * This class represents the value passed into several different query parameters
 * determining whether to retrieve results that exactly match the input or that
 * just contain the input.
 */
class MatchTag {
    /**
     * Only retrieve results that match the input exactly.
     */
    public const MATCH_EXACT = 'EXACT';

    /**
     * Retrieve results containing the input.
     */
    public const MATCH_CONTAINS = 'CONTAINS';

    /**
     * Which type of match to retrieve. Can only be 'EXACT' or 'CONTAINS'.
     */
    protected string $matchType;

    /**
     * The value the query results must match.
     */
    protected string $value;

    /**
     * Return the type of match to retrieve.
     *
     * @return string the type of match to retrieve.
     */
    public function getMatchType(): string {
        return $this->matchType;
    }

    /**
     * Set the type of match to retrieve.
     *
     * @param string $matchType The type of match to retrieve.
     * @return self
     * @throws InvalidArgumentException if $matchType is not one of the valid types
     */
    public function setMatchType(string $matchType): self {
        if ($matchType !== self::MATCH_EXACT && $matchType !== self::MATCH_CONTAINS) {
            throw new InvalidArgumentException('"$matchType" must be either "EXACT" or "CONTAINS".');
        }
        $this->matchType = $matchType;
        return $this;
    }

    /**
     * Return the value the query results must match.
     *
     * @return string the value the query results must match
     */
    public function getValue(): string {
        return $this->value;
    }

    /**
     * Set the value the query results must match.
     *
     * @param string $value The value the query results must match.
     * @return self
     */
    public function setValue(string $value): self {
        $this->value = $value;
        return $this;
    }
}
