<?php

/**
 * This file contains the class CBS\SmarterU\DataTypes\Group.
 *
 * @author Brian Reich <brian.reich@thecoresolution.com>
 * @copyright $year$ Core Business Solutions
 * @license Proprietary
 * @since 2020/04/22
 * @version $version$
 */

declare(strict_types=1);

namespace CBS\SmarterU\DataTypes;

use CBS\SmarterU\DataTypes\GroupPermissions;
use CBS\SmarterU\DataTypes\Tag;
use CBS\SmarterU\Exceptions\InvalidArgumentException;
use DateTimeInterface;

/**
 * Represents a SmarterU Group.
 *
 * A group is a collection of users that can be collectively assigned to
 * training.
 */
class Group {
    #region Constants

    /** Active status */
    public const STATUS_ACTIVE = 'Active';

    /** Inactive status */
    public const STATUS_INACTIVE = 'Inactive';

    #endregion Constants

    #region Properties

    /** The name of the group. */
    protected string $name;

    /** The unique id of the Group. */
    protected ?string $groupId;

    /** The data and time when the group was created. */
    protected DateTimeInterface $createdDate;

    /** The date and time when the group was last modified */
    protected DateTimeInterface $modifiedDate;

    /** The group's description. */
    protected string $description;

    /** The home message displayed for the group. */
    protected string $homeGroupMessage;

    /**
     * A list of notification email addresses for the group.
     *
     * @var string[]
     */
    protected array $notificationEmails;

    /**
     * Specifies whether the account's Enable User Help setting is overriden by
     * the group.
     *
     * True: The account's Enable User Help setting is overriden by the group
     * False: The account's Enable User Help setting is not overriden
     */
    protected ?bool userHelpOverrideDefault;

    /**
     * Specifies whether a link displays in the header of the learner interface
     * that enables users who have the group as their home group to request
     * help.
     *
     * True: A link to request help displays in the header.
     * False: A link to request help does not display in the header.
     */
    protected ?bool userHelpEnabled;

    /**
     * The email addresses to which help requests will be sent.
     */
    protected ?array userHelpEmail;

    /**
     * The text to be displayed with the help link in the learner interface
     * header.
     */
    protected ?string userHelpText;

    /**
     * An array of tags applied to the group.
     *
     * @var Tag[]
     */
    protected array $tags;

    /**
     * Specifies whether there's a limit on how many users can be added to the
     * group.
     *
     * True: There is a limit on how many users can be added to the group.
     * False: There is no limit on how many users can be added to the group.
     */
    protected ?bool $userLimitEnabled;

    /**
     * The maximum number of users that can be added to the group.
     */
    protected ?int $userLimitAmount;

    /** A count of learning modules in the group. */
    protected int $learningModuleCount;

    /** The group's status. */
    protected string $status;

    /**
     * A container for the Users who are assigned to the group and the
     * permissions that each User has within the group. Each element
     * must be an instance of CBS\SmarterU\DataTypes\GroupPermissions.
     * GroupPermissions::$groupName, GroupPermissions::$groupId, and either
     * GroupPermissions::$email or GroupPermissions::$employeeId may be
     * left unset.
     */
    protected array $users = [];

    /**
     * A container for assigning courses to the group. Each element must be an
     * instance of CBS\SmarterU\DataTypes\LearningModule.
     */
    protected array $learningModules = [];

    /**
     * A container for assigning subscription variants to the group. Each element
     * must be an instance of CBS\SmarterU\DataTypes\SubscriptionVariant.
     */
    protected array $subscriptionVariants = [];

    /**
     * The identifier of the dashboard set to be assigned to the group. If no
     * value is provided, the account's default dashboard set is assigned.
     */
    protected ?string $dashboardSetId;

    #endregion Properties

    #region Getters and Setters

    /**
     * Sets the group's tags.
     *
     * @param Tag[] $tags The group's tags.
     * @return self
     * @throws InvalidArgumentException if any array members are not a Tag.
     */
    public function setTags(array $tags): self {
        // If we ever get typed arrays this could go away.
        foreach ($tags as $tag) {
            if (! $tag instanceof Tag) {
                throw new InvalidArgumentException(
                    'Parameter to '
                    . __METHOD__
                    . 'must be a list of Tag instances'
                );
            }
        }

        $this->tags = $tags;
        return $this;
    }

    /**
     * Returns the group's tags.
     *
     * @return Tag[] Returns the group's tags.
     */
    public function getTags(): array {
        return $this->tags;
    }

    /**
     * Get whether or not the group has a user limit enabled.
     *
     * @return ?bool true if and only if the group has a user limit enabled
     */
    public function getUserLimitEnabled(): ?bool {
        return $this->userLimitEnabled;
    }

    /**
     * Set whether or not the group has a user limit enabled.
     *
     * @param bool $userLimitEnabled true if and only if the group has a user
     *      limit enabled
     * @return self
     */
    public function setUserLimitEnabled(bool $userLimitEnabled): self {
        $this->userLimitEnabled = $userLimitEnabled;
        return $this;
    }

    /**
     * Get the maximum number of users that can be added to the group. Null if
     * there is no limit.
     *
     * @return ?int The maximum number of users that can be added to a group,
     *      or null if there is no limit.
     */
    public function getUserLimitAmount(): ?int {
        return $this->userLimitAmount;
    }

    /**
     * Set the maximum number of users that can be added to the group. Set this
     * to null to remove the limit.
     *
     * @param ?int $userLimitAmount The maximum number of users that can be
     *      added to the group, or null if there is no maximum
     * @return self
     */
    public function setUserLimitAmount(?int $userLimitAmount): self {
        $this->userLimitAmount = $userLimitAmount;
        return $this;
    }

    /**
     * Get the container for assigning Users to the group.
     *
     * @return array the container for the Users
     */
    public function getUsers(): array {
        return $this->users;
    }

    /**
     * Set the container for assigning Users to the group.
     *
     * @param array $users A container for assigning Users to the group.
     *      Each element must be an instance of CBS\SmarterU\DataTypes\
     *      GroupPermissions. GroupPermissions::$homeGroup, and either
     *      GroupPermissions::$email or GroupPermissions::$employeeId must be
     *      set.
     * @return self
     */
    public function setUsers(array $users): self {
        $this->users = $users;
        return $this;
    }

    /**
     * Get the container for assigning Learning Modules to the group.
     *
     * @return array the container for Learning Modules
     */
    public function getLearningModules(): array {
        return $this->learningModules;
    }

    /**
     * Set the container for assigning Learning Modules to the group.
     *
     * @param array $learningModules the container for LearningModules
     * @return self
     */
    public function setLearningModules(array $learningModules): self {
        $this->learningModules = $learningModules;
        return $this;
    }

    /**
     * Get the container for assigning subscription variants to the group.
     *
     * @return array The container for subscription variants
     */
    public function getSubscriptionVariants(): array {
        return $this->subscriptionVariants;
    }

    /**
     * Set the container for assigning subscription variants to the group.
     *
     * @param array The container for subscription variants
     * @return self
     */
    public function setSubscriptionVariants(array $subscriptionVariants): self {
        $this->subscriptionVariants = $subscriptionVariants;
        return $this;
    }

    /**
     * Get the identifier of the dashboard set that is assigned to the group.
     *
     * @return ?string the identifier of the dashboard set
     */
    public function getDashboardSetId(): ?string {
        return $this->dashboardSetId;
    }

    /**
     * Set the identifier of the dashboard set that is assigned to the group.
     *
     * @param string $dashboardSetId The identifier of the dashboard set
     * @return self
     */
    public function setDashboardSetId(string $dashboardSetId): self {
        $this->dashboardSetId = $dashboardSetId;
        return $this;
    }

    /**
     * Sets the group's status.
     *
     * The status value must be one of STATUS_ACTIVE or STATUS_INACTIVE. If it
     * is not a valid value, an InvalidArgumentException is thrown.
     *
     * @param string $status The group's status.
     * @return self
     * @throws InvalidArgumentException if status is invalid.
     */
    public function setStatus(string $status): self {
        // Verify that the specific status is a valid value.
        $validStatus = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
        if (! in_array($status, $validStatus)) {
            throw new InvalidArgumentException(sprintf(
                "%s is not one of %s",
                $status,
                implode(', ', $validStatus)
            ));
        }

        $this->status = $status;
        return $this;
    }

    /**
     * Returns the group's status.
     *
     * @return string Returns the group's status.
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * Sets the group's learning module count.
     *
     * $learningModuleCount must be >= 0 or an InvalidArgumentException is
     * thrown.
     *
     * @param int $learningModuleCount The group's learning module count.
     * @return self
     * @throws InvalidArgumentException if value is < 0.
     */
    public function setLearningModuleCount(int $learningModuleCount): self {
        if ($learningModuleCount < 0) {
            throw new InvalidArgumentException('$learningModuleCount must be >= 0');
        }

        $this->learningModuleCount = $learningModuleCount;
        return $this;
    }

    /**
     * Returns the group's learning module count.
     *
     * @return int Returns the group's learning module count.
     */
    public function getLearningModuleCount(): int {
        return $this->learningModuleCount;
    }

    /**
     * Sets the group's user count.
     *
     * $userCount must be >= 0 or an InvalidArgumentException is
     * thrown.
     *
     * @param int $userCount The group's user count.
     * @return self
     * @throws InvalidArgumentException if value is < 0.
     */
    public function setUserCount(int $userCount): self {
        if ($userCount < 0) {
            throw new InvalidArgumentException('$userCount must be >= 0');
        }

        $this->userCount = $userCount;
        return $this;
    }

    /**
     * Returns the group's user count.
     *
     * @return int Returns the group's user count.
     */
    public function getUserCount(): int {
        return $this->userCount;
    }

    /**
     * Sets the group's notification email addresses.
     *
     * All members of the array must be strings. If they are not, then an
     * InvalidArgumentException is thrown.
     *
     * @param string[] $notificationEmails The Group's notification email addresses.
     * @return self
     * @throws InvalidArgumentException if array members are not strings.
     */
    public function setNotificationEmails(array $notificationEmails): self {
        foreach ($notificationEmails as $email) {
            if (! is_string($email)) {
                throw new InvalidArgumentException('Parameter to ' .
                    __METHOD__ . 'must be a list of email addresses as strings');
            }
        }

        $this->notificationEmails = $notificationEmails;
        return $this;
    }

    /**
     * Returns the group's notification email addresses.
     *
     * @return string[] the group's notification email addresses.
     */
    public function getNotificationEmails(): array {
        return $this->notificationEmails;
    }

    /**
     * Get whether the Enable User Help setting is overriden by the group.
     *
     * @return ?bool true if and only if the Enable User Help setting is
     *      overriden by the group
     */
    public function getUserHelpOverrideDefault(): ?bool {
        return $this->userHelpOverrideDefault;
    }

    /**
     * Set whether the Enable User Help setting is overriden by the group.
     *
     * @param bool $userHelpOverrideDefault true if and only if the Enable
     *      User Help setting is overriden by the group
     */
    public function setUserHelpOverrideDefault(bool $userHelpOverrideDefault): self {
        $this->userHelpOverrideDefault = $userHelpOverrideDefault;
        return $this;
    }
    
    /**
     * Get whether a link displays in the header of the learner interface that
     * enables users who have the group as their home group to request help.
     *
     * @return ?bool true if and only if the link is displayed
     */
    public function getUserHelpEnabled(): ?bool {
        return $this->userHelpEnabled;
    }

    /**
     * Set whether a link displays in the header of the learner interface that
     * enables users who have the group as their home group to request help.
     *
     * @param bool userHelpEnabled true if and only if the link is displayed
     * @return self
     */
    public function setUserHelpEnabled(bool $userHelpEnabled): self {
        $this->userHelpEnabled = $userHelpEnabled;
        return $this;
    }

    /**
     * Get the email addresses to which help requests will be sent. If no email
     * addresses are specified, the help requests will be sent to all
     * administrators.
     *
     * @return ?array the email addresses to which help requests will be sent
     */
    public function getUserHelpEmail(): ?array {
        return $this->userHelpEmail;
    }

    /**
     * Set the email addresses to which help requests will be sent. If no email
     * addresses are specified, the help requests will be sent to all
     * administrators.
     *
     * @param array $userHelpEmail the email addresses
     * @return self
     */
    public function setUserHelpEmail(array $userHelpEmail): self {
        $this->userHelpEmail = $userHelpEmail;
        return $this;
    }

    /**
     * Get the text to display for the help link in the learner interface's
     * header.
     *
     * @return ?string the text to display for the help link
     */
    public function getUserHelpText(): ?string {
        return $this->userHelpText;
    }

    /**
     * Set the text to display for the help link in the learner interface's
     * header.
     *
     * @param string $userHelpText the text to display for the help link
     * @return self
     */
    public function setUserHelpText(string $userHelpText): self {
        $this->userHelpText = $userHelpText;
        return $this;
    }

    /**
     * Sets the group's home message.
     *
     * @param string $homeGroupMessage the group's home message.
     * @return self
     */
    public function setHomeGroupMessage(string $homeGroupMessage): self {
        $this->homeGroupMessage = $homeGroupMessage;
        return $this;
    }

    /**
     * Returns the group's home message.
     *
     * @return string the group's home message.
     */
    public function getHomeGroupMessage(): string {
        return $this->homeGroupMessage;
    }

    /**
     * Sets the date and time the group was last modified.
     *
     * @param DateTimeInterface $modifiedDate The date and time the group was last modified.
     * @return self
     */
    public function setModifiedDate(DateTimeInterface $modifiedDate): self {
        $this->modifiedDate = $modifiedDate;
        return $this;
    }

    /**
     * Returns the date and time the group was last modified.
     *
     * @return DateTimeInterface the date and time the group was last modified.
     */
    public function getModifiedDate(): DateTimeInterface {
        return $this->modifiedDate;
    }

    /**
     * Sets the group's description.
     *
     * @param string $description the group's description.
     * @return self
     */
    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the group's description.
     *
     * @return string the group's description.
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * Sets the date and time when the group was created.
     *
     * @param DateTimeImmutable $createdDate the date and time when the group was created.
     * @return self
     */
    public function setCreatedDate(DateTimeInterface $createdDate): self {
        $this->createdDate = $createdDate;
        return $this;
    }

    /**
     * Returns the date and time when the group was created.
     *
     * @return DateTimeInterface the date and time when the group was created.
     */
    public function getCreatedDate(): DateTimeInterface {
        return $this->createdDate;
    }

    /**
     * Sets the unique id of the group.
     *
     * @param string $groupId the unique id of the group.
     * @return self
     */
    public function setGroupId(string $groupId): self {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * Returns the unique id of the group.
     *
     * @return string the unique id of the group.
     */
    public function getGroupId(): string {
        return $this->groupId;
    }

    /**
     * Sets the name of the group.
     *
     * @param string $name the name of the group.
     * @return self
     */
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the group.
     *
     * @return string the name of the group.
     */
    public function getName(): string {
        return $this->name;
    }

    #endregion Getters and Setters
}
