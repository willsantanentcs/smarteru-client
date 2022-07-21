<?php

/**
 * This file contains the class Core\SmarterU\DataTypes\User.
 *
 * @author Brian Reich <brian.reich@thecoresolution.com>
 * @copyright $year$ Core Business Solutions
 * @license MIT
 * @since 2022/06/16
 * @version $version$
 */

declare(strict_types=1);

namespace SmarterU\DataTypes;

use SmarterU\Exceptions\InvalidArgumentException;
use SmarterU\Queries\BaseQuery;
use SimpleXMLElement;

/**
 * A User in SmarterU.
 */
class User {
    /**
     * Indicates the user's account is active.
     */
    public const STATUS_ACTIVE = 'Active';

    /**
     * Indicates the user's account is inactive.
     */
    public const STATUS_INACTIVE = 'Inactive';

    #region Properties

    /**
     * The user ID of the user.
     */
    protected ?string $id;

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

    /**
     * A container for the user's supervisors. The supervisor must already exist
     * in SmarterU.
     */
    protected array $supervisors = [];

    /**
     * The name of the organization to assign to the user. The organization name
     * provided must already exist within your SmarterU account.
     */
    protected ?string $organization;

    /**
     * A container for the teams to assign to the user. The team names provided
     * must already exist within your SmarterU account.
     */
    protected array $teams = [];

    /**
     * A container for the custom user fields in your account. The custom fields
     * must alread exist within your SmarterU account.
     */
    protected array $customFields = [];

    /**
     * The language you want the user's account to use.
     */
    protected ?string $language;

    /**
     * The user's status.
     */
    protected string $status = self::STATUS_ACTIVE;

    /**
     * The title of the user.
     */
    protected ?string $title;

    /**
     * The division of the user.
     */
    protected ?string $division;

    /**
     * Specifies whether the user can provide feedback on online courses.
     */
    protected bool $allowFeedback = false;

    /**
     * The user's primary phone number.
     */
    protected ?string $phonePrimary;

    /**
     * The user's alternate phone number.
     */
    protected ?string $phoneAlternate;

    /**
     * The user's mobile phone number.
     */
    protected ?string $phoneMobile;

    /**
     * The user's fax number.
     */
    protected ?string $fax;

    /**
     * The user's website.
     */
    protected ?string $website;

    /**
     * The first address line of the user.
     */
    protected ?string $address1;

    /**
     * The secondary address line of the user.
     */
    protected ?string $address2;

    /**
     * The city of the user's address.
     */
    protected ?string $city;

    /**
     * The province or state of the user's address.
     */
    protected ?string $province;

    /**
     * The country of the user's address. Acceptable values are "Canada", "United
     * States", or "International".
     */
    protected ?string $country;

    /**
     * The user's postal code.
     */
    protected ?string $postalCode;

    /**
     * The location where the user's physical mail should be sent. Acceptable
     * values are "Personal" or "Organization".
     */
    protected ?string $sendMailTo;

    /**
     * A container for the learning plans to assign to the user. The learning
     * plans must already exist within your SmarterU account. Plans can be
     * identified by their names and/or their IDs.
     */
    protected ?array $roles;

    /**
     * Specifies whether the user will receive email notifications.
     */
    protected bool $receiveNotifications = true;

    /**
     * The name of the user's home group. If not provided, will default to
     * the first group provided in the Group section of the createUser
     * method.
     */
    protected ?string $homeGroup;

    /**
     * A container for assigning groups to the user and specifying group
     * permissions to be granted to this user. Each element must be an instance
     * of SmarterU\DataTypes\GroupPermissions.
     */
    protected array $groups;

    /**
     * A container for assigning venues to the user.
     */
    protected array $venues;

    /**
     * A container for adding a user's wages.
     */
    protected array $wages;

    
    #endregion Properties

    #region Getters and Setters

    /**
     * Gets the user's ID.
     *
     * @return ?string the user's ID
     */
    public function getId(): ?string {
        return $this->id;
    }

    /**
     * Sets the user's ID.
     *
     * @param string $id the user's ID
     * @return self
     */
    public function setId(string $id): self {
        $this->id = $id;
        return $this;
    }

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

    /**
     * Get the container for the user's supervisors.
     *
     * @return array the container for the user's supervisors
     */
    public function getSupervisors(): array {
        return $this->supervisors;
    }

    /**
     * Set the container for the user's supervisors. Each supervisor must
     * already exist in SmarterU.
     *
     * @param array $supervisors the supervisors for this user
     * @return self
     */
    public function setSupervisors(array $supervisors): self {
        $this->supervisors = $supervisors;
        return $this;
    }

    /**
     * Get the name of the organization to assign to the user.
     *
     * @return ?string the name of the organization
     */
    public function getOrganization(): ?string {
        return $this->organization;
    }

    /**
     * Set the name of the organization to assign to the user. The organization
     * must already exist within your SmarterU account.
     *
     * @param ?string $organization the name of the organization
     * @return self
     */
    public function setOrganization(?string $organization): self {
        $this->organization = $organization;
        return $this;
    }

    /**
     * Get the container for the teams to assign to the user.
     *
     * @return array the teams to assign to the user
     */
    public function getTeams(): array {
        return $this->teams;
    }

    /**
     * Set the container for the teams to assign to the user. The team names
     * provided must already exist in your SmarterU account.
     *
     * @param array teams the teams to assign to the user
     * @return self
     */
    public function setTeams(array $teams): self {
        $this->teams = $teams;
        return $this;
    }

    /**
     * Get the container for the custom fields in your account.
     *
     * @return array the custom fields
     */
    public function getCustomFields(): array {
        return $this->customFields;
    }

    /**
     * Set the container for the custom fields in your account. The custom
     * fields must already exist within your SmarterU account.
     *
     * @param array $customFields the custom fields in your account
     * @return self
     */
    public function setCustomFields(array $customFields): self {
        $this->customFields = $customFields;
        return $this;
    }

    /**
     * Get the language the user's account uses.
     *
     * @return ?string the language to use
     */
    public function getLanguage(): ?string {
        return $this->language;
    }

    /**
     * Set the language the user's account uses.
     *
     * @param ?string $language the language to use for this user
     * @return self
     */
    public function setLanguage(?string $language): self {
        $this->language = $language;
        return $this;
    }

    /**
     * Return the status of the user.
     *
     * @return string the status of the user
     */
    public function getUserStatus(): string {
        return $this->userStatus;
    }

    /**
     * Set the status of the user.
     *
     * @param string $userStatus the status of the user
     * @return self
     * @throws InvalidArgumentException if the status is not one of the possible
     *      status values
     */
    public function setUserStatus(string $userStatus): self {
        if ($userStatus !== self::STATUS_ACTIVE
        && $userStatus !== self::STATUS_INACTIVE) {
            throw new InvalidArgumentException(
                '"$userStatus" must be either "ACTIVE" or "INACTIVE".'
            );
        }
        $this->userStatus = $userStatus;
        return $this;
    }

    /**
     * Get the user's title.
     *
     * @return ?string the user's title
     */
    public function getTitle(): ?string {
        return $this->title;
    }

    /**
     * Set the user's title.
     *
     * @param ?string $title the user's title
     * @return self
     */
    public function setTitle(?string $title): self {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the user's division.
     *
     * @return ?string the user's division
     */
    public function getDivision(): ?string {
        return $this->division;
    }

    /**
     * Set the user's division.
     *
     * @param ?string $division the user's division
     * @return self
     */
    public function setDivision(?string $division): self {
        $this->division = $division;
        return $this;
    }

    /**
     * Get whether or not the user can provide feedback on online courses.
     *
     * @return bool true if and only if the user can provide feedback
     */
    public function getAllowFeedback(): bool {
        return $this->allowFeedback;
    }

    /**
     * Set whether or not the user can provide feedback on online courses.
     *
     * @param bool $allowFeedback true if and only if the user can provide feedback
     * @return self
     */
    public function setAllowFeedback(bool $allowFeedback): self {
        $this->allowFeedback = $allowFeedback;
        return $this;
    }

    /**
     * Get the user's primary phone number.
     *
     * @return ?string the user's primary phone number
     */
    public function getPhonePrimary(): ?string {
        return $this->phonePrimary;
    }

    /**
     * Set the user's primary phone number.
     *
     * @param ?string $phonePrimary the user's primary phone number
     * @return self
     */
    public function setPhonePrimary(?string $phonePrimary): self {
        $this->phonePrimary = $phonePrimary;
        return $this;
    }

    /**
     * Get the user's alternate phone number.
     *
     * @return ?string the user's alternate phone number
     */
    public function getPhoneAlternate(): ?string {
        return $this->phoneAlternate;
    }

    /**
     * Set the user's alternate phone number.
     *
     * @param ?string $phoneAlternate the user's alternate phone number
     * @return self
     */
    public function setPhoneAlternate(?string $phoneAlternate): self {
        $this->phoneAlternate = $phoneAlternate;
        return $this;
    }

    /**
     * Get the user's mobile phone number.
     *
     * @return ?string the user's mobile phone number
     */
    public function getPhoneMobile(): ?string {
        return $this->phoneMobile;
    }

    /**
     * Set the user's mobile phone number.
     *
     * @param ?string $phoneMobile the user's mobile phone number
     * @return self
     */
    public function setPhoneMobile(?string $phoneMobile): self {
        $this->phoneMobile = $phoneMobile;
        return $this;
    }

    /**
     * Get the user's fax number.
     *
     * @return ?string the user's fax number
     */
    public function getFax(): ?string {
        return $this->fax;
    }

    /**
     * Set the user's fax number.
     *
     * @param ?string $fax the user's fax number
     * @return self
     */
    public function setFax(?string $fax): self {
        $this->fax = $fax;
        return $this;
    }

    /**
     * Get the user's website.
     *
     * @return ?string the URL of the user's website
     */
    public function getWebsite(): ?string {
        return $this->website;
    }

    /**
     * Set the user's website.
     *
     * @param ?string $website the URL of the user's website
     * @return self
     */
    public function setWebsite(?string $website): self {
        $this->website = $website;
        return $this;
    }

    /**
     * Get the first line of the user's address.
     *
     * @return ?string the first line of the user's address
     */
    public function getAddress1(): ?string {
        return $this->address1;
    }

    /**
     * Set the first line of the user's address.
     *
     * @param ?string $address1 the first line of the user's address
     * @return self
     */
    public function setAddress1(?string $address1): self {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * Get the second line of the user's address.
     *
     * @return ?string the second line of the user's address.
     */
    public function getAddress2(): ?string {
        return $this->address2;
    }

    /**
     * Set the second line of the user's address.
     *
     * @param ?string $address2 the second line of the user's address
     * @return self
     */
    public function setAddress2(?string $address2): self {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * Get the city of the user's address.
     *
     * @return ?string the city of the user's address
     */
    public function getCity(): ?string {
        return $this->city;
    }

    /**
     * Set the city of the user's address.
     *
     * @param ?string $city the city of the user's address
     * @return self
     */
    public function setCity(?string $city): self {
        $this->city = $city;
        return $this;
    }

    /**
     * Get the province or state of the user's address.
     *
     * @return ?string the province or state of the user's address
     */
    public function getProvince(): ?string {
        return $this->province;
    }

    /**
     * Set the province or state of the user's address.
     *
     * @param ?string $province the province or state of the user's address
     * @return self
     */
    public function setProvince(?string $province): self {
        $this->province = $province;
        return $this;
    }

    /**
     * Get the country of the user's address.
     *
     * @return ?string the country of the user's address
     */
    public function getCountry(): ?string {
        return $this->country;
    }

    /**
     * Set the country of the user's address. Acceptable values are "Canada",
     * "United States", or "International".
     *
     * @param ?string $country the country of the user's address
     * @return self
     */
    public function setCountry(?string $country): self {
        $this->country = $country;
        return $this;
    }

    /**
     * Get the user's postal code.
     *
     * @return ?string the user's postal code
     */
    public function getPostalCode(): ?string {
        return $this->postalCode;
    }

    /**
     * Set the user's postal code.
     *
     * @param ?string $postalCode the user's postal code
     * @return self
     */
    public function setPostalCode(?string $postalCode): self {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * Get the location where the user's physical mail should be sent.
     *
     * @return ?string the location where physical mail should be sent
     */
    public function getSendMailTo(): ?string {
        return $this->sendMailTo;
    }

    /**
     * Set the location where the user's physical mail should be sent.
     *
     * @param ?string $sendMailTo the location where physical mail should be sent
     * @return self
     */
    public function setSendMailTo(?string $sendMailTo): self {
        $this->sendMailTo = $sendMailTo;
        return $this;
    }

    /**
     * Get the container for the learning plans assigned to the user.
     *
     * @return array the container for the learning plans to assign to the user
     */
    public function getRoles(): ?array {
        return $this->roles;
    }

    /**
     * Set the container for the learning plans assigned to the user. The
     * learning plans must already exist within your SmarterU account.
     *
     * @param array the container for the learning plans to assign to the user
     * @return self
     */
    public function setRoles(?array $roles): self {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Get whether or not the user will receive email notifications.
     *
     * @return bool true if and only if the user will receive email notifications
     */
    public function getReceiveNotifications(): bool {
        return $this->receiveNotifications;
    }

    /**
     * Set whether or not the user will receive email notifications.
     *
     * @param bool $receiveNotifications true if and only if the user will
     *      receive email notifications
     * @return self
     */
    public function setReceiveNotifications(bool $receiveNotifications): self {
        $this->receiveNotifications = $receiveNotifications;
        return $this;
    }

    /**
     * Get the name of the user's home group.
     *
     * @return ?string the name of the user's home group
     */
    public function getHomeGroup(): ?string {
        return $this->homeGroup;
    }

    /**
     * Set the name of the user's home group.
     *
     * @param ?string $homeGroup the name of the user's home group
     * @return self
     */
    public function setHomeGroup(?string $homeGroup): self {
        $this->homeGroup = $homeGroup;
        return $this;
    }

    /**
     * Get the container for assigning groups to the user and specifying the
     * user's permissions within those groups.
     *
     * @return array the container for assigning groups to the user
     */
    public function getGroups(): array {
        return $this->groups;
    }

    /**
     * Set the container for assigning groups to the user and specifying the
     * user's permissions within those groups.
     *
     * @param array $groups the container for the user's group assignments
     * @return self
     */
    public function setGroups(array $groups): self {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Get the container for assigning venues to the user.
     *
     * @return array the container for the user's venues
     */
    public function getVenues(): array {
        return $this->venues;
    }

    /**
     * Set the container for assigning venues to the user.
     *
     * @param array $venues the container for the user's venues
     * @return self
     */
    public function setVenues(array $venues): self {
        $this->venues = $venues;
        return $this;
    }

    /**
     * Get the container for adding the user's wages.
     *
     * @return array the container for the user's wages
     */
    public function getWages(): array {
        return $this->wages;
    }

    /**
     * Set the container for adding the user's wages.
     *
     * @param array $wages the container for the user's wages
     * @return self
     */
    public function setWages(array $wages): self {
        $this->wages = $wages;
        return $this;
    }

    /**
     * Generate an XML representation of the user, to be passed into the API
     * for a CreateUser or UpdateUser query.
     *
     * @param string $accountApi the account API key of the user making the request
     * @param string $userApi the user API key of the user making the request
     * @param string $methodName the name of the method being called
     *
     * @return SimpleXMLElement an XML representation of the user
     */
    public function toSimpleXML(
        string $accountApi,
        string $userApi,
        string $methodName
    ): SimpleXMLElement {
        $query = new BaseQuery();
        $query->setAccountApi($accountApi);
        $query->setUserApi($userApi);

        $xml = $query->createBaseXml();
        $xml->addChild('Method', $methodName);
        $parameters = $xml->addChild('Parameters');
        $userTag = $parameters->addChild('User');
        $info = $userTag->addChild('Info');

        $info->addChild('Email', $user->getEmail());
        $info->addChild('EmployeeID', $user->getEmployeeId());
        $info->addChild('GivenName', $user->getGivenName());
        $info->addChild('Surname', $user->getSurname());
        $info->addChild('Password', $user->getPassword());
        if (!empty($user->getTimezone())) {
            $info->addChild('TimeZone', $user->getTimezone());
        }
        $info->addChild('LearnerNotifications', $user->getLearnerNotifications());
        $info->addChild('SupervisorNotifications', $user->getSupervisorNotifications());
        $info->addChild('SendEmailTo', $user->getSendEmailTo());
        $info->addChild('AlternateEmail', $user->getAlternateEmail());
        $info->addChild('AuthenticationType', $user->getAuthenticationType());

        $profile = $userTag->addChild('Profile');
        if (!empty($user->getSupervisors())) {
            $supervisors = $profile->addChild('Supervisors');
            foreach ($user->getSupervisors() as $supervisor) {
                $supervisors->addChild('Supervisor', $supervisor);
            }
        }
        if (!empty($user->getOrganization())) {
            $profile->addChild('Organization', $user->getOrganization());
        }
        if (!empty($user->getTeams())) {
            $teams = $profile->addChild('Teams');
            foreach ($user->getTeams() as $team) {
                $teams->addChild('Team', $team);
            }
        }
        if (!empty($user->getCustomFields())) {
            // TODO implement this. For iteration 1, we can assume this will be empty.
        }
        if (!empty($user->getLanguage())) {
            $profile->addChild('Language', $user->getLanguage());
        }
        if (!empty($user->getStatus())) {
            $profile->addChild('Status', $user->getStatus());
        }
        if (!empty($user->getTitle())) {
            $profile->addChild('Title', $user->getTitle());
        }
        if (!empty($user->getDivision())) {
            $profile->addChild('Division', $user->getDivision());
        }
        if (!empty($user->getAllowFeedback())) {
            $profile->addChild('AllowFeedback', $user->getAllowFeedback());
        }
        if (!empty($user->getPhonePrimary())) {
            $profile->addChild('PhonePrimary', $user->getPhonePrimary());
        }
        if (!empty($user->getPhoneAlternate())) {
            $profile->addChild('PhoneAlternate', $user->getPhoneAlternate());
        }
        if (!empty($user->getPhoneMobile())) {
            $profile->addChild('PhoneMobile', $user->getPhoneMobile());
        }
        if (!empty($user->getFax())) {
            $profile->addChild('Fax', $user->getFax());
        }
        if (!empty($user->getWebsite())) {
            $profile->addChild('Website',$user->getWebsite());
        }
        if (!empty($user->getAddress1())) {
            $profile->addChild('Address1', $user->getAddress1());
        }
        if (!empty($user->getAddress2())) {
            $profile->addChild('Address2',$user->getAddress2());
        }
        if (!empty($user->getCity())) {
            $profile->addChild('City', $user->getCity());
        }
        if (!empty($user->getProvince())) {
            $profile->addChild('Province', $user->getProvince());
        }
        if (!empty($user->getCountry())) {
            $profile->addChild('Country', $user->getCountry());
        }
        if (!empty($user->getPostalCode())) {
            $profile->addChild('PostalCode', $user->getPostalCode());
        }
        if (!empty($user->getSendMailTo())) {
            $profile->addChild('SendMailTo', $user->getSendMailTo());
        }
        if (!empty($user->getRoles())) {
            // TODO implement this. For iteration 1, we can assume this is empty.
        }
        if (!empty($user->getReceiveNotifications())) {
            $profile->addChild('ReceiveNotifications', $user->getReceiveNotifications());
        }
        if (!empty($user->getHomeGroup())) {
            $profile->addChild('HomeGroup', $user->getHomeGroup());
        }
        $groups = $userTag->addChild('Groups');
        foreach($user->getGroups() as $group) {
            $groupTag = $groups->addChild('Group');
            if (!empty($group->getName())) {
                $groupTag->addChild('GroupName', $group->getName());
            }
            if (!empty($group->getId())) {
                $groupTag->addChild('GroupID', $group->getId());
            }
            foreach($group->getPermissions() as $permission){
                $permissionTag = $groupTag->addChild('Permission');
                $permissionTag->addChild('Action', $permission->getAction());
                $permissionTag->addChild('Code', $permission->getCode());
            }
        }

        if (!empty($user->getVenues())) {
            // TODO implement this. For iteration 1, we can assume it's empty.
        }

        if (!empty($user->getWages())) {
            // TODO implement this. For iteration 1, we can assume it's empty.
        }

        return $xml->asXML();
    }
}
