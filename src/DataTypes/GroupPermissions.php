<?php

/**
 * Contains SmarterU\DataTypes\Group.
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     MIT
 * @version     $version$
 * @since       2022/07/20
 */

declare(strict_types=1);

namespace CBS\SmarterU\DataTypes;

use CBS\SmarterU\DataTypes\Permission;

/**
 * The GroupPermissions class represents a User's affiliation with a Group
 * and the permissions that User has within the Group.
 */
class GroupPermissions {
    /**
     * The name of the group the user is a member of. Mutually exclusive with
     * the group's ID.
     */
    protected ?string $groupName;

    /**
     * The user-specified ID of the group the user is a member of. This is the
     * GroupID returned by the getGroup and listGroups methods. Mutually
     * exclusive with the group's name.
     */
    protected ?int $groupId;

    /**
     * A container for the permissions to be granted to the user. Elements must
     * be an instance of SmarterU\DataTypes\Permission.
     */
    protected array $permissions;

    /**
     * Get the name of the group the user is a member of.
     *
     * @return ?string the name of the group the user is a member of
     */
    public function getGroupName(): ?string {
        return $this->groupName;
    }

    /**
     * Set the name of the group the user is a member of.
     *
     * @param string $groupName the name of the group the user is a member of
     * @return self
     */
    public function setGroupName(string $groupName): self {
        $this->groupName = $groupName;
        $this->groupId = null;
        return $this;
    }

    /**
     * Get the user-specified ID of the group the user is a member of.
     *
     * @return ?int the ID of the group the user is a member of.
     */
    public function getGroupId(): ?int {
        return $this->groupId;
    }

    /**
     * Set the user-specified ID of the group the user is a member of.
     *
     * @param int $groupId the id of the group the user is a member of
     * @return self
     */
    public function setGroupId(int $groupId): self {
        $this->groupName = null;
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * Get the container for the permissions to be granted or denied to the
     * user within the group.
     *
     * @return array the container for the permissions
     */
    public function getPermissions(): array {
        return $this->permissions;
    }

    /**
     * Set the container for the permissions to be granted or denied to the
     * user within the group.
     *
     * @param array $permissions the container for the permissions
     * @return self
     */
    public function setPermissions(array $permissions): self {
        $this->permissions = $permissions;
        return $this;
    }

}
