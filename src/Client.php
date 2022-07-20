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

namespace SmarterU;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use SimpleXMLElement;
use SmarterU\DataTypes\User;
use SmarterU\Queries\BaseQuery;
use SmarterU\Queries\GetUserQuery;
use SmarterU\Queries\ListUsersQuery;

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
    protected string $accountApi;

    /**
     * The user API key, used for authentication purposes when making
     * requests to the SmarterU API.
     */
    protected string $userApi;

    /**
     * Get the account API key.
     *
     * @return string the account API key
     */
    public function getAccountApi(): string {
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
     * @return string the user API key
     */
    public function getUserApi(): string {
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
     * Make a CreateUser query to the SmarterU API.
     *
     * @param User $user the user to create
     */
    public function createUser(User $user) {
        $xml = $user->toSimpleXml(
            $this->getAccountApi(),
            $this->getUserApi(), 'CreateUser'
        );

        $httpClient = new HttpClient(['base_uri' => $this->POST_URL]);
        $response = $httpClient->request('POST', $POST_URL, ['body' => $xml]);
        $body = (string) $response->getBody();
        $bodyAsXml = simplexml_load_string($body);

        $result = $bodyAsXml->Success;
        $email = $bodyAsXml->Info->Email;
        $employeeId = $bodyAsXml->Info->EmployeeID;

        $errors = $bodyAsXml->Errors;
        $errorMessages = [];
        if (count($errors) !== 0) {
            foreach ($errors->children() as $error) {
                $errorMessages[$error->ErrorID] = $error->ErrorMessage;
            }
        }
    }

    /**
     * Make a GetUser query to the SmarterU API.
     *
     * @param GetUserQuery $query The query representing the User to return
     * @return User The User returned by the query
     */
    public function getUser(GetUserQuery $query): User {
        $xml = $query->toXml();
        $httpClient = new HttpClient(['base_uri' => $this->POST_URL]);
        $response = $httpClient->request('POST', $POST_URL, ['body' => $xml]);
    }

    /**
     * Make a ListUsers query to the SmarterU API.
     *
     * @param ListUsersQuery $query The query representing the Users to return
     * @return User[] All Users that match the query criteria
     */
    public function listUsers(ListUserQuery $query): array {
        $xml = $query->toXml();
        $httpClient = new HttpClient(['base_uri' => $this->POST_URL]);
        $response = $httpClient->request('POST', $POST_URL, ['body' => $xml->asXML()]);
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

        $httpClient = new HttpClient(['base_uri' => $this->POST_URL]);
        $response = $httpClient->request('POST', $POST_URL, ['body' => $xml->asXML()]);
    }
}
