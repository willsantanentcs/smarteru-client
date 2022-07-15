<?php

/**
 * Contains SmarterU\Queries\Tags\DateRangeTag.
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     Proprietary
 * @version     $version$
 * @since       2022/07/13
 */

declare(strict_types=1);

namespace SmarterU\Queries\Tags;

use DateTime;

/**
 * This class represents a range of dates to pass into a query.
 */
class DateRangeTag {
    /**
     * The first date to include in the DateRange filter.
     */
    protected DateTime $dateFrom;

    /**
     * The last date to include in the DateRange filter.
     */
    protected DateTime $dateTo;

    /**
     * Return the first date to include in the DateRange filter.
     *
     * @return DateTime the first date to include in the DateRange filter.
     */
    public function getDateFrom(): DateTime {
        return $this->dateFrom;
    }

    /**
     * Set the first date to include in the DateRange filter.
     *
     * @param DateTime $dateFrom the first date to include in the DateRange filter
     * @return self
     */
    public function setDateFrom(DateTime $dateFrom): self {
        $this->dateFrom = $dateFrom->format('d/m/Y');
        return $this;
    }

    /**
     * Return the last date to include in the DateRange filter.
     *
     * @return DateTime the last date to include in the DateRange filter.
     */
    public function getDateTo(): DateTime {
        return $this->dateTo;
    }

    /**
     * Set the last date to include in the DateRange filter.
     *
     * @param DateTime $dateTo the last date to include in the DateRange filter
     * @return self
     */
    public function setDateTo(DateTime $dateTo): self {
        $this->dateFrom = $dateTo->format('d/m/Y');
        return $this;
    }
}
