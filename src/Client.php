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
     * Make a CreateUser query to the SmarterU API.
     *
     * @param User $user the user to create
     */
    public function createUser(User $user) {
        $query = new BaseQuery();
        //TODO initialize API keys

        $xml = $query->createBaseXml();
        $xml->addChild('Method', 'CreateUser');
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
        



        $httpClient = new HttpClient(['base_uri' => $this->POST_URL]);
        $response = $httpClient->request('POST', $POST_URL, ['body' => $xml]);

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
        $query = new BaseQuery();
        //TODO initialize API keys

        $xml = $query->createBaseXml();
        $xml->addChild('Method', 'updateUser');
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

        $httpClient = new HttpClient(['base_uri' => $this->POST_URL]);
        $response = $httpClient->request('POST', $POST_URL, ['body' => $xml->asXML()]);
    }
}
