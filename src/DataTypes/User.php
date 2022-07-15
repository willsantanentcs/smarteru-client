<?php

/**
 * This file contains the class Core\SmarterU\DataTypes\User.
 *
 * @author Brian Reich <brian.reich@thecoresolution.com>
 * @copyright $year$ Core Business Solutions
 * @license Proprietary
 * @since 2022/06/16
 * @version $version$
 */

declare(strict_types=1);

namespace SmarterU\DataTypes;

/**
 * A User in SmarterU.
 */
class User {
    #region Properties
    /**
     * The email address of the user. This tag can be empty if an EmployeeID
     * value is provided.
     *
     * @var string|null;
     */
    protected ?string $email;

    /**
     * The employee ID of the user. This must be a unique value between all
     * users in your SmarterU account. If a blank value is provided, an employee
     * ID is not assigned to the user. A value must be provided if no value is
     * provided for the Email tag.
     *
     * @var string
     */
    protected ?string $employeeId;

    /**
     * The given name of the user, also known as their first name.
     *
     * @var string|null
     */
    protected ?string $givenName;

    /**
     * The surname of the user, also known as their last name.
     *
     * @var string|null
     */
    protected ?string $surname;

    /**
     * The password to assign to the user. The password must be a minimum of
     * eight characters. If no password is provided, a random password will be
     * generated for the user. Regardless of whether a random password is
     * generated or one is provided, a user must change their password the
     * first time they log in (if Single Sign On is not used).
     *
     * @var string|null
     */
    protected ?string $password;

    /**
     * The primary time zone of the user. Acceptable values are the values
     * listed in the Provided Name column on the Time Zones page. If this tag
     * is not provided, the user’s time zone will default to the account’s
     * time zone.
     *
     * @var string
     */
    protected string $timezone;

    /**
     * Specifies whether the user should receive weekly reminders of their
     * pending or outstanding courses in SmarterU.
     *
     * @var bool
     */
    protected bool $learnerNotifications;

    /**
     * Specifies whether the user should receive weekly reports on the status
     * of any users they are responsible for.
     *
     * @var bool
     */
    protected bool $supervisorNotifications;

    /**
     * Specifies where the user's emails should be sent. Acceptable values are:
     *   Supervisor -  Emails to the user will be sent to the supervisors'
     *     email addresses. If the user has multiple supervisors, emails will be
     *     sent to all of the user's supervisors. A supervisor must have a
     *     primary email address specified for this option to be allowed.
     *   Self  - Emails to the user are sent to the user's primary email
     *     address. An email address must be provided for this option to be
     *     allowed.
     *   Alternate - Emails to the user will be sent to the email address
     *     specified in the AlternateEmail tag. For this option to be allowed, a
     *     valid email address must be provided in the AlternateEmail tag.
     *
     * @var string
     */
    protected string $sendEmailTo;

    /**
     * An alternate email address for the user. This value is required if the
     * SendEmailTo tag is set to Alternate.
     *
     * @var string|null
     */
    protected ?string $alternateEmail;

    /**
     * Specifies how you would like the user to authenticate. Acceptable values
     * are:
     *   SmarterU - Default. The user will log into SmarterU via the SmarterU
     *     interface.
     *   External - The user will log into SmarterU via an external system using
     *     single-sign on.
     *   Both - The user will log into SmarterU via the SmarterU interface or an
     *     external system.
     *
     * @var string
     */
    protected string $authenticationType;
    
    #endregion Properties

    #region Getters and Setters

    /**
     * Returns the User's authentication type.
     *
     * @return string Returns the User's authentication type.
     */
    public function getAuthenticationType() {
        return $this->authenticationType;
    }

    /**
     * Sets the user's authentication type.
     *
     * @param string $authenticationType The user's authentication type.
     * @return self
     */
    public function setAuthenticationType(string $authenticationType) {
        $this->authenticationType = $authenticationType;
        return $this;
    }

    /**
     * Returns the User's alternate email address.
     *
     * @return string|null Returns the user's alternate email address.
     */
    public function getAlternateEmail() {
        return $this->alternateEmail;
    }

    /**
     * Sets the user's alternate email address.
     *
     * @param string|null $alternateEmail The user's alternate email address.
     * @return self
     */
    public function setAlternateEmail($alternateEmail) {
        $this->alternateEmail = $alternateEmail;
        return $this;
    }

    /**
     * Returns where the user's email should be sent (Supervisor, Self,
     * or Alternate).
     *
     * @return string Returns where the user's email should be sent.
     */
    public function getSendEmailTo() {
        return $this->sendEmailTo;
    }

    /**
     * Sets where the user's email should be sent (Supervisor, Self, or
     * Alternate).
     *
     * @param string $sendEmailTo Sets where the user's email should be sent.
     * @return self
     */
    public function setSendEmailTo(string $sendEmailTo) {
        $this->sendEmailTo = $sendEmailTo;
        return $this;
    }

    /**
     * Returns the user's timezone.
     *
     * @return string Returns the user's timezone.
     */
    public function getTimezone() {
        return $this->timezone;
    }

    /**
     * Sets the user's timezone.
     *
     * @param string $timezone Sets the user's timezone.
     * @return self
     */
    public function setTimezone(string $timezone) {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Returns the password to assign to the user.
     *
     * @return string|null Returns the password to assign to the user.
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Sets the password to assign to the user
     *
     * @param string|null $password The password to assign to the user
     * @return self
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * Returns the surname of the user, also known as their last name.
     *
     * @return string|null The surname of the user, also known as their last name.
     */
    public function getSurname() {
        return $this->surname;
    }

    /**
     * Set the surname of the user, also known as their last name.
     *
     * @param string|null $surname The surname of the user, also known as their
     *  last name.
     * @return self
     */
    public function setSurname($surname) {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get the given name of the user, also known as their first name.
     *
     * @return string|null The given name of the user, also known as their first name.
     */
    public function getGivenName() {
        return $this->givenName;
    }

    /**
     * Set the given name of the user, also known as their first name.
     *
     * @param  string|null  $givenName  The given name of the user, also known as their first name.
     *
     * @return  self
     */
    public function setGivenName($givenName) {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * Get provided for the Email tag.
     *
     * @return  string
     */
    public function getEmployeeId() {
        return $this->employeeId;
    }

    /**
     * Set provided for the Email tag.
     *
     * @param  string  $employeeId  provided for the Email tag.
     *
     * @return  self
     */
    public function setEmployeeId(string $employeeId) {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * Get value is provided.
     *
     * @return  string|null;
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set value is provided.
     *
     * @param  string|null;  $email  value is provided.
     *
     * @return  self
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

     #endregion Getters and Setters

    /**
     * Get of any users they are responsible for.
     *
     * @return  bool
     */ 
    public function getSupervisorNotifications()
    {
        return $this->supervisorNotifications;
    }

    /**
     * Set of any users they are responsible for.
     *
     * @param  bool  $supervisorNotifications  of any users they are responsible for.
     *
     * @return  self
     */ 
    public function setSupervisorNotifications(bool $supervisorNotifications)
    {
        $this->supervisorNotifications = $supervisorNotifications;

        return $this;
    }

    /**
     * Get pending or outstanding courses in SmarterU.
     *
     * @return  bool
     */ 
    public function getLearnerNotifications()
    {
        return $this->learnerNotifications;
    }

    /**
     * Set pending or outstanding courses in SmarterU.
     *
     * @param  bool  $learnerNotifications  pending or outstanding courses in SmarterU.
     *
     * @return  self
     */ 
    public function setLearnerNotifications(bool $learnerNotifications)
    {
        $this->learnerNotifications = $learnerNotifications;

        return $this;
    }
}
