<?php

/**
 * Contains SmarterU\Client
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     MIT
 * @version     $version$
 * @since       2022/07/15
 */

declare(strict_types=1);

namespace CBS\SmarterU;

use CBS\SmarterU\DataTypes\User;
use CBS\SmarterU\Exceptions\HttpException;
use CBS\SmarterU\Exceptions\SmarterUException;
use CBS\SmarterU\Queries\BaseQuery;
use CBS\SmarterU\Queries\GetUserQuery;
use CBS\SmarterU\Queries\ListUsersQuery;
use DateTime;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use SimpleXMLElement;

/**
 * The Client class makes API calls and translates the response to the
 * appropriate object.
 */
class Client {
    /**
     * The URL to POST to.
     */
    protected const POST_URL = 'https://api.smarteru.com/apiv2/';

    /**
     * The account API key, used for authentication purposes when making
     * requests to the SmarterU API.
     */
    protected ?string $accountApi = null;

    /**
     * The user API key, used for authentication purposes when making
     * requests to the SmarterU API.
     */
    protected ?string $userApi = null;

    /**
     * The HTTP Client to use to make the requests. Initialized to a functional
     * HTTP Client by default, change only for testing purposes.
     */
    protected ?HttpClient $httpClient;

    /**
     * Get the account API key.
     *
     * @return ?string the account API key
     */
    public function getAccountApi(): ?string {
        return $this->accountApi;
    }

    /**
     * Set the account API key.
     *
     * @param string $accountApi the account API key
     * @return self
     */
    public function setAccountApi(string $accountApi): self {
        $this->accountApi = $accountApi;
        return $this;
    }

    /**
     * Get the user API key.
     *
     * @return ?string the user API key
     */
    public function getUserApi(): ?string {
        return $this->userApi;
    }

    /**
     * Set the user API key.
     *
     * @param string $userApi the user API key
     * @return self
     */
    public function setUserApi(string $userApi): self {
        $this->userApi = $userApi;
        return $this;
    }

    /**
     * Get the HTTP Client.
     *
     * @return ?HttpClient the HTTP Client
     */
    public function getHttpClient(): ?HttpClient {
        return $this->httpClient;
    }

    /**
     * Set the HTTP Client.
     *
     * @param HttpClient $httpClient The HTTP Client
     * @return self
     */
    public function setHttpClient(HttpClient $httpClient): self {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Make a CreateUser query to the SmarterU API.
     *
     * @param User $user the user to create
     * @return array A 2-dimensional array containing one array that represents
     *      the User's information as returned by the SmarterU API, and one
     *      array that contains any error messages that may have been returned.
     * @throws MissingValueException if the Account API Key and/or the User API
     *      Key are not set.
     */
    public function createUser(User $user) {
        $xml = $user->toSimpleXml(
            $this->getAccountApi(),
            $this->getUserApi(),
            'createUser'
        );

        if (empty($this->getHttpClient())) {
            $this->setHttpClient(new HttpClient(['base_uri' => self::POST_URL]));
        }

        try {
            $response = $this->getHttpClient()->request('POST', self::POST_URL, ['package' => $xml]);
        }
        catch (\Exception $e) {
            throw new HttpException($e->getMessage());
        }
        $body = (string) $response->getBody();
        $bodyAsXml = simplexml_load_string($body);

        $result = (string) $bodyAsXml->Result;

        $errorMessages = [];
        $errors = $bodyAsXml->Errors;
        if ($errors->count() !== 0) {
            $errorMessages = $this->readErrors($errors);
        }

        if (strcmp($result, 'Failed') == 0) {
            $errorsAsString = '';
            foreach ($errorMessages as $id => $message) {
                $errorsAsString .= $id;
                $errorsAsString .= ": ";
                $errorsAsString .= $message;
                $errorsAsString .= ", ";
            }
            throw new SmarterUException($errorsAsString);
        }

        $email = (string) $bodyAsXml->Info->Email;
        $employeeId = (string) $bodyAsXml->Info->EmployeeID;

        $userAsArray = [
            'Email' => $email,
            'EmployeeID' => $employeeId
        ];

        $result = [
            'Response' => $userAsArray,
            'Errors' => $errorMessages
        ];

        return $result;
    }

    /**
     * Make a GetUser query to the SmarterU API.
     *
     * @param GetUserQuery $query The query representing the User to return
     * @return array An array representation of the query results and any
     *      errors that may have been returned.
     */
    public function getUser(GetUserQuery $query): array {
        // If the API keys are not already set in the query, pass them in.
        if (empty($query->getAccountApi())) {
            $query->setAccountApi($this->getAccountApi());
        }
        if (empty($query->getUserApi())) {
            $query->setUserApi($this->getUserApi());
        }

        $xml = $query->toXml();

        if (empty($this->getHttpClient())) {
            $this->setHttpClient(new HttpClient(['base_uri' => self::POST_URL]));
        }

        try {
            $response = $this->getHttpClient()->request('POST', self::POST_URL, ['package' => $xml]);
        }
        catch (\Exception $e) {
            throw new HttpException($e->getMessage());
        }

        $body = (string) $response->getBody();
        $bodyAsXml = simplexml_load_string($body);
        $result = (string) $bodyAsXml->Result;

        $errorMessages = [];
        $errors = $bodyAsXml->Errors;
        if (count($errors) !== 0) {
            $errorMessages = $this->readErrors($errors);
        }

        if (strcmp($result, 'Failed') == 0) {
            $errorsAsString = '';
            foreach ($errorMessages as $id => $message) {
                $errorsAsString .= $id;
                $errorsAsString .= ': ';
                $errorsAsString .= $message;
                $errorsAsString .= ', ';
            }
            throw new SmarterUException($errorsAsString);
        }

        $user = $bodyAsXml->Info->User;
        $teams = [];

        /**
         * Not casting this to an array causes the teams to be placed in a
         * SimpleXMLElement where the array indices are the node names, which
         * renders the team names inaccessible because [#] is invalid syntax
         * for a node name.
         */
        foreach ((array) $user->Teams->Team as $team) {
            $teams[] = $team;
        }

        $userAsRead = [
            'ID' => $user->ID,
            'Email' => $user->Email,
            'EmployeeID' => $user->EmployeeID,
            'CreatedDate' => $user->CreatedDate,
            'ModifiedDate' => $user->ModifiedDate,
            'GivenName' => $user->GivenName,
            'Surname' => $user->Surname,
            'Language' => $user->Language,
            'AllowFeedback' => $user->AllowFeedback,
            'Status' => $user->Status,
            'AuthenticationType' => $user->AuthenticationType,
            'Timezone' => $user->Timezone,
            'AlternateEmail' => $user->AlternateEmail,
            'HomeGroup' => $user->HomeGroup,
            'Organization' => $user->Organization,
            'Title' => $user->Title,
            'Division' => $user->Division,
            // TODO implement supervisors. For iteration 1, we can assume it's blank
            'Supervisors' => [],
            'PhonePrimary' => $user->PhonePrimary,
            'PhoneAlternate' => $user->PhoneAlternate,
            'PhoneMobile' => $user->PhoneMobile,
            'SendMailTo' => $user->SendMailTo,
            'SendEmailTo' => $user->SendEmailTo,
            'Fax' => $user->Fax,
            'Address1' => $user->Address1,
            'Address2' => $user->Address2,
            'City' => $user->City,
            'PostalCode' => $user->PostalCode,
            'Province' => $user->Province,
            'Country' => $user->Country,
            'LearnerNotifications' => $user->SendWeeklyTaskReminder,
            'SupervisorNotifications' => $user->SendWeeklyProgressSummary,
            'Teams' => $teams,
            // TODO implement roles. For iteration 1, we can assume it's blank.
            'Roles' => [],
            // TODO implement custom fields. For iteration 1, we can assume it's blank.
            'CustomFields' => [],
            // TODO implement venues. For iteration 1, we can assume it's blank.
            'Venues' => [],
            // TODO implement wages. For iteration 1, we can assume it's blank.
            'Wages' => [],
            'ReceiveNotifications' => $user->ReceiveNotifications
        ];

        $results = [
            'Response' => $userAsRead,
            'Errors' => $errorMessages
        ];
        return $results;
    }

    /**
     * Make a ListUsers query to the SmarterU API.
     *
     * @param ListUsersQuery $query The query representing the Users to return
     * @return User[] All Users that match the query criteria
     */
    public function listUsers(ListUserQuery $query): array {
        $xml = $query->toXml();

        if (empty($this->getHttpClient())) {
            $this->setHttpClient(new HttpClient(['base_uri' => $this->POST_URL]));
        }

        $response = $this->getHttpClient()->request('POST', $POST_URL, ['package' => $xml->asXML()]);
        $body = (string) $response->getBody();
        $bodyAsXml = simplexml_load_string($body);

        $result = $bodyAsXml->Success;
        $users = $bodyAsXml->Info->Users;

        $errorMessages = [];
        $errors = $bodyAsXml->Errors;
        if (count($errors) !== 0) {
            $errorMessages = $this->readErrors($errors);
        }

        if (strcmp($result, 'Failed') == 0) {
            $errorsAsString = '';
            foreach ($errorMessages as $id => $message) {
                $errorsAsString .= $id;
                $errorsAsString .= ': ';
                $errorsAsString .= $message;
                $errorsAsString .= '\n';
            }
            throw new SmarterUException($errorsAsString);
        }

        $users = [];
        foreach ($users->children() as $user) {
            $currentUser = [];
            $teams = [];
            foreach ($user->Teams->children() as $team) {
                $teams[] = $team;
            }
            $currentUser['ID'] = $user->ID;
            $currentUser['Email'] = $user->Email;
            $currentUser['EmployeeID'] = $user->EmployeeID;
            $currentUser['GivenName'] = $user->GivenName;
            $currentUser['Surname'] = $user->Surname;
            $currentUser['Status'] = $user->Status;
            $currentUser['Title'] = $user->Title;
            $currentUser['Division'] = $user->Division;
            $currentUser['HomeGroup'] = $user->HomeGroup;
            $currentUser['CreatedDate'] = $user->CreatedDate;
            $currentUser['ModifiedDate'] = $user->ModifiedDate;
            $currentUser['Teams'] = $teams;
            $users[] = $currentUser;
        }

        return [$users, $errorMessages];
    }

    /**
     * Make an UpdateUser query to the SmarterU API.
     *
     * @param User $user The User to update
     */
    public function updateUser(User $user) {
        $xml = $user->toSimpleXml(
            $this->getAccountApi(),
            $this->getUserApi(), 'UpdateUser'
        );

        if (empty($this->getHttpClient())) {
            $this->setHttpClient(new HttpClient(['base_uri' => $this->POST_URL]));
        }

        $response = $this->getHttpClient()->request('POST', $POST_URL, ['package' => $xml->asXML()]);
        $body = (string) $response->getBody();
        $bodyAsXml = simplexml_load_string($body);

        $result = $bodyAsXml->Success;

        $errorMessages = [];
        $errors = $bodyAsXml->Errors;
        if (count($errors) !== 0) {
            $errorMessages = $this->readErrors($errors);
        }

        if (strcmp($result, 'Failed') == 0) {
            $errorsAsString = '';
            foreach ($errorMessages as $id => $message) {
                $errorsAsString .= $id;
                $errorsAsString .= ': ';
                $errorsAsString .= $message;
                $errorsAsString .= '\n';
            }
            throw new SmarterUException($errorsAsString);
        }

        $email = $bodyAsXml->Info->Email;
        $employeeId = $bodyAsXml->Info->EmployeeID;

        $results = [
            'Email' => $email,
            'EmployeeID' => $employeeId
        ];
        return [$results, $errorMessages];
    }

    /**
     * Translate the error message(s) returned by the SmarterU API to an array
     * of 'ErrorID' => 'ErrorMessage'. For any non-fatal errors, this array
     * will be part of the array returned by the request methods. For any fatal
     * errors, this array will be converted into a comma-separated string and
     * used to throw an exception.
     *
     * @param SimpleXMLElement $errors the <errors> portion of the response
     * @return array an array representation of these errors
     */
    private function readErrors(SimpleXMLElement $errors): array {
        $errorsAsArray = [];
        foreach ($errors->children() as $error) {
            $errorsAsArray[(string) $error->ErrorID] = (string) $error->ErrorMessage;
        }
        return $errorsAsArray;
    }
}
