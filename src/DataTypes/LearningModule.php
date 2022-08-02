<?php

/**
 * Contains CBS\SmarterU\DataTypes\LearningModule.
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     MIT
 * @version     $version$
 * @since       2022-08-02
 */

declare(strict_types=1);

namespace CBS\SmarterU\DataTypes;

/**
 * A LearningModule represents a course that is assigned to the Users within
 * a Group.
 */
class LearningModule {
    /**
     * The system-generated identifier of the course to be assigned to the
     * group. This is the LearningModuleID returned by listLearningModules
     * and the ID returned by getLearnerReport.
     */
    protected string $id;

    /**
     * Specifies whether or not users in the group will be able to self-enroll
     * in the course.
     *
     * True: Group users can self-enroll.
     * False: Group users cannot self-enroll.
     */
    protected bool $allowSelfEnroll;

    /**
     * Specifies whether or not users in the group will be automatically
     * enrolled in the course.
     *
     * True: Users will be automatically enrolled.
     * False: Users will not be automatically enrolled.
     */
    protected bool $autoEnroll;

    /**
     * Get the system-generated identifier of the course to be assigned to
     * the group.
     *
     * @return string The system-generated identifier of the course
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * Set the system-generated identifier of the course to be assigned to
     * the group.
     *
     * @param string $id The system-generated identifier of the course
     * @return self
     */
    public function setId(string $id): self {
        $this->id = $id;
        return $this;
    }

    /**
     * Get whether or not users in the group are able to self-enroll in the
     * course.
     *
     * @return bool True if and only if users are able to self-enroll
     */
    public function getAllowSelfEnroll(): bool {
        return $this->allowSelfEnroll;
    }

    /**
     * Set whether or not users in the group are able to self-enroll in the
     * course.
     *
     * @param bool $allowSelfEnroll True if and only if users are able to
     *      self-enroll
     * @return self
     */
    public function setAllowSelfEnroll(bool $allowSelfEnroll): self {
        $this->allowSelfEnroll = $allowSelfEnroll;
        return $this;
    }

    /**
     * Get whether or not users in the group will be automatically enrolled
     * in the course.
     *
     * @return bool True if and only if users in the group will be
     *      automatically enrolled in the course.
     */
    public function getAutoEnroll(): bool {
        return $this->autoEnroll;
    }

    /**
     * Set whether or not users in the group will be automatically enrolled
     * in the course.
     *
     * @param bool $autoEnroll True if and only if users in the group will be
     *      automatically enrolled in the course.
     */
    public function setAutoEnroll(bool $autoEnroll): self {
        $this->autoEnroll = $autoEnroll;
        return $this;
    }
}