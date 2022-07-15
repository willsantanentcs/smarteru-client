<?php

/**
 * Contains SmarterU\Queries\ListUsersQuery.
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     Proprietary
 * @version     $version$
 * @since       2022/07/13
 */

declare(strict_types=1);

namespace SmarterU\Queries;

use SmarterU\Exceptions\InvalidArgumentException;
use SmarterU\Queries\BaseQuery;
use SmarterU\Queries\Tags\DateRangeTag;
use SmarterU\Queries\Tags\MatchTag;

/**
 * Represents a listUsers query made to the SmarterU API.
 */
class ListUsersQuery extends BaseQuery {
    /**
     * The maximum number of users to return.
     */
    public const MAX_PAGE_SIZE = 1000;
    /**
     * The parameter used to sort by name.
     */
    public const SORT_BY_NAME = 'NAME';

    /**
     * The parameter used to sort by employee ID.
     */
    public const SORT_BY_ID = 'EMPLOYEE_ID';

    /**
     * The parameter used to sort query results in ascending order.
     */
    public const SORT_ASC = 'ASC';

    /**
     * The parameter used to sort query results in descending order.
     */
    public const SORT_DESC = 'DESC';

    /**
     * The parameter used to query for only active users.
     */
    public const STATUS_ACTIVE = 'Active';

    /**
     * The parameter used to query for only inactive users.
     */
    public const STATUS_INACTIVE = 'Inactive';

    /**
     * The parameter used to query for all users.
     */
    public const STATUS_ALL = 'All';


    /**
     * The page to get. Default is 1.
     */
    protected ?int $page = 1;

    /**
     * The maximum number of users to return. If the PageSize tag is not provided,
     * up to 50 results are returned by default. The maximum allowed value is 1000.
     */
    protected ?int $pageSize;

    /**
     * The field used to sort the results. Can only be 'NAME' or 'EMPLOYEE_ID'.
     */
    protected ?string $sortField;

    /**
     * The direction that the results will be sorted. Can be either 'ASC' or 'DESC'.
     */
    protected ?string $sortOrder;

    /**
     * The tag representing the email to query for.
     */
    protected ?MatchTag $email;

    /**
     * The tag representing the employee ID to query for.
     */
    protected ?MatchTag $employeeId;

    /**
     * The tag representing the name of the user to query for.
     */
    protected ?MatchTag $name;

    /**
     * This is the name of a group. Only users that have been assigned to the
     * provided group will be returned. 
     */
    protected ?string $groupName;

    /**
     * This is the status of the users to list. Values can be 'Active', 'Inactive',
     * or 'All'. Default is 'All'. 
     */
     protected string $userStatus = self::STATUS_ALL;

    /**
     * The date range when the user's account was created. The dates should be
     * in the format dd-mmm-yyyy.
     */
    protected ?DateRangeTag $createdDate;

    /**
     * The date range when the user's account was last updated. The dates should
     * be in the format dd-mmm-yyyy.
     */
    protected ?DateRangeTag $modifiedDate;

    /**
     * A container for the teams that a user is assigned to.
     */
    protected ?array $teams;

    /**
     * Return the page to get.
     *
     * @return ?int the page to get
     */
    public function getPage(): ?int {
        return $this->page;
    }

    /**
     * Set the page to get.
     *
     * @param ?int $page the page to get
     * @return self
     */
    public function setPage(?int $page): self {
        $this->page = $page;
        return $this;
    }

    /**
     * Return the maximum number of users to return.
     *
     * @return ?int The maximum number of users to return.
     */
    public function getPageSize(): ?int {
        return $this->pageSize;
    }

    /**
     * Set the maximum number of users to return. Cannot be greater than 1000.
     *
     * @param ?int $pageSize the maximum number of users to return
     * @return self
     */
    public function setPageSize(?int $pageSize): self {
        $this->pageSize = min($pageSize, self::MAX_PAGE_SIZE);
        return $this;
    }

    /**
     * Return the field used to sort results.
     *
     * @return ?string the field used to sort results
     */
    public function getSortField(): ?string {
        return $this->sortField;
    }

    /**
     * Set the field used to sort results.
     *
     * @param ?string $sortField the field used to sort results
     * @return self
     * @throws InvalidArgumentException if $sortField is not one of the valid fields
     */
    public function setSortField(?string $sortField): self {
        if (!empty($sortField)
        && $sortField !== self::SORT_BY_NAME
        && $sortField !== self::SORT_BY_ID) {
            throw new InvalidArgumentException('"$sortField" must be either "NAME" or "EMPLOYEE_ID".');        
        }
        $this->sortField = $sortField;
        return $this;
    }

    /**
     * Return the direction the results are sorted in.
     *
     * @return ?string the field used to sort results
     */
    public function getSortOrder(): ?string {
        return $this->sortOrder;
    }

    /**
     * Set the direction the results are sorted in.
     *
     * @param ?string $sortOrder the direction the results are sorted in
     * @return self
     * @throws InvalidArgumentException if $sortOrder is not one of the valid orders
     */
    public function setSortOrder(?string $sortOrder): ?self {
        if (!empty($sortOrder)
        && $sortOrder !== self::SORT_ASC
        && $sortOrder !== self::SORT_DESC) {
            throw new InvalidArgumentException('"$sortOrder" must be either "ASC" or "DESC".');        
        }
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * Return the tag representing the email to query for.
     *
     * @return ?MatchTag the tag representing the email to query for
     */
    public function getEmail(): ?MatchTag {
        return $this->email;
    }

    /**
     * Set the tag representing the email to query for.
     *
     * @param ?MatchTag $email the email to query for
     * @return self
     */
    public function setEmail(?MatchTag $email): self {
        $this->email = $email;
        return $this;
    }

    /**
     * Return the tag representing the employee ID to query for.
     *
     * @return ?MatchTag the tag representing the employee ID to query for
     */
    public function getEmployeeId(): ?MatchTag {
        return $this->employeeId;
    }

    /**
     * Set the tag representing the employee ID to query for.
     *
     * @param ?MatchTag $employeeId the employee ID to query for
     * @return self
     */
    public function setEmployeeId(?MatchTag $employeeId): self {
        $this->employeeId = $employeeId;
        return $this;
    }

    /**
     * Return the tag representing the name to query for.
     *
     * @return ?MatchTag the tag representing the name to query for
     */
    public function getName(): ?MatchTag {
        return $this->name;
    }

    /**
     * Set the tag representing the name to query for.
     *
     * @param ?MatchTag $name the name to query for
     * @return self
     */
    public function setName(?MatchTag $name): self {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the name of the group containing the users to query for.
     *
     * @return ?string the name of the group containing the users to query for
     */
    public function getGroupName(): ?string {
        return $this->groupName;
    }

    /**
     * Set the name of the group containing the users to query for.
     *
     * @param ?string $groupName the name of the group containing the users to query for
     * @return self
     */
    public function setGroupName(?string $groupName): self {
        $this->groupName = $groupName;
        return $this;
    }

    /**
     * Return the status of the users to query for.
     *
     * @return string the status of the users to query for
     */
    public function getUserStatus(): string {
        return $this->userStatus;
    }

    /**
     * Set the status of the users to query for.
     *
     * @param string $userStatus the status of the users to query for
     * @return self
     * @throws InvalidArgumentException if the status is not one of the possible
     *      status values
     */
    public function setUserStatus(string $userStatus): self {
        if ($userStatus !== self::STATUS_ACTIVE
        && $userStatus !== self::STATUS_INACTIVE
        && $userStatus !== self::STATUS_ALL) {
            throw new InvalidArgumentException(
                '"$userStatus" must be either "ACTIVE", "INACTIVE", or "ALL".'
            );
        }
        $this->userStatus = $userStatus;
        return $this;
    }

    /**
     * Return the date range when the user's account was created.
     *
     * @return ?DateRangeTag the date range when the user's account was created
     */
    public function getCreatedDate(): ?DateRangeTag {
        return $this->createdDate;
    }

    /**
     * Set the date range when the user's account was created.
     *
     * @param ?DateRangeTag $createdDate the date range when the user's account
     *      was created
     * @return self
     */
    public function setCreatedDate(?DateRangeTag $createdDate): self {
        $this->createdDate = $createdDate;
        return $this;
    }

    /**
     * Return the date range when the user's account was last updated.
     *
     * @return ?DateRangeTag the date range when the user's account was last updated
     */
    public function getModifiedDate(): ?DateRangeTag {
        return $this->modifiedDate;
    }

    /**
     * Set the date range when the user's account was last updated.
     *
     * @param ?DateRangeTag $modifiedDate the date range when the user's account
     *      was last modified
     * @return self
     */
    public function setModifiedDate(?DateRangeTag $modifiedDate): self {
        $this->modifiedDate = $modifiedDate;
        return $this;
    }

    /**
     * Return the container for the teams the user is assigned to.
     *
     * @return ?array the container for the teams the user is assigned to
     */
    public function getTeams(): ?array {
        return $this->teams;
    }

    /**
     * Set the container for the teams the user is assigned to.
     *
     * @param ?array the teams the user is assigned to
     * @return self
     */
    public function setTeams(?array $teams): self {
        $this->teams = $teams;
        return $this;
    }

    /**
     * Generate an XML representation of the query, to be passed into the
     * SmarterU API.
     */
    public function toXml(): SimpleXMLElement {
        $xmlString = $this->createBasicXml();
        $xmlString->addChild('method', 'listUsers');
        $parameters = $xmlString->addChild('parameters');
        $user = $parameters->addChild('User');
        $user->addChild('Page', $this->page);
        if (!empty($this->pageSize)) {
            $user->addChild('PageSize', $this->pageSize);
        }
        if (!empty($this->sortField)) {
            $user->addChild('SortField', $this->sortField);
        }
        if (!empty($this->sortOrder)) {
            $user->addChild('SortOrder', $this->sortOrder);
        }
        $filters = $user->addChild('Filters');
        if ($this->includeUsersTag()) {
            $users = $filter->addChild('Users');
            $userIdentifier = $users->addChild('UserIdentifier');
            if (!empty($this->email)) {
                $email = $userIdentifier->addChild('email');
                $email->addChild('MatchType', $this->email->getMatchType());
                $email->addChild('Value', $this->email->getValue());
            }
            if (!empty($this->employeeId)) {
                $employeeId = $userIdentifier->addChild('EmployeeID');
                $employeeId->addChild('MatchType', $this->employeeId->getMatchType());
                $employeeId->addChild('Value', $this->employeeId->getValue());
            }
            if (!empty($this->name)) {
                $name = $userIdentifier->addChild('Name');
                $name->addChild('MatchType', $this->name->getMatchType());
                $name->addChild('Value', $this->employeeId->getValue());
            }
        }
        if (!empty($this->groupName)) {
            $filters->addChild('GroupName', $this->groupName);
        }
    }

    /**
     * Determine whether or not to filter query results based on the user's
     * identifying information.
     *
     * @return bool True if and only if the query should contain a <Users> tag
     */
    private function includeUsersTag(): bool {
        return !empty($this->email) || !empty($this->employeeId) || !empty($this->name);
    }
}
