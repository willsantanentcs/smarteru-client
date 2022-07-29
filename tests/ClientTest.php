<?php

/**
 * Contains Tests\CBS\SmarterU\ClientTest
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     ??
 * @version     $version$
 * @since       2022/07/27
 */

declare(strict_types=1);

namespace Tests\CBS\SmarterU;

use CBS\SmarterU\DataTypes\GroupPermissions;
use CBS\SmarterU\DataTypes\Permission;
use CBS\SmarterU\DataTypes\User;
use CBS\SmarterU\Exceptions\HttpException;
use CBS\SmarterU\Exceptions\MissingValueException;
use CBS\SmarterU\Exceptions\SmarterUException;
use CBS\SmarterU\Queries\GetUserQuery;
use CBS\SmarterU\Queries\ListUsersQuery;
use CBS\SmarterU\Queries\Tags\DateRangeTag;
use CBS\SmarterU\Queries\Tags\MatchTag;
use CBS\SmarterU\Client;
use DateTime;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

/**
 * Tests CBS\SmarterU\Client.
 */
class ClientTest extends TestCase {
    /**
     * A User to use for testing purposes.
     */
    protected User $user1;

    /**
     * A second User to use for testing purposes.
     */
    protected User $user2;

    /**
     * An inactive User to use for testing purposes.
     */
    protected User $user3;

    /**
     * Set up the test Users.
     */
    public function setUp(): void {
        $permission1 = (new Permission())
            ->setAction('Grant')
            ->setCode('MANAGE_USERS');
        $permission2 = (new Permission())
            ->setAction('Grant')
            ->setCode('MANAGE_GROUP');
        $groupPermissions = (new GroupPermissions())
            ->setGroupName('Group1')
            ->setPermissions([$permission1, $permission2]);
        $groupPermission2 = (new GroupPermissions())
            ->setGroupName('Group2')
            ->setPermissions([$permission1, $permission2]);
        
        $this->user1 = (new User())
            ->setId('1')
            ->setEmail('phpunit@test.com')
            ->setEmployeeId('1')
            ->setGivenName('PHP')
            ->setSurname('Unit')
            ->setPassword('password')
            ->setTimezone('EST')
            ->setLearnerNotifications(true)
            ->setSupervisorNotifications(true)
            ->setSendEmailTo('Self')
            ->setAlternateEmail('phpunit@test1.com')
            ->setAuthenticationType('External')
            ->setSupervisors(['supervisor1', 'supervisor2'])
            ->setOrganization('organization')
            ->setTeams(['team1', 'team2'])
            ->setLanguage('English')
            ->setStatus('Active')
            ->setTitle('Title')
            ->setDivision('division')
            ->setAllowFeedback(true)
            ->setPhonePrimary('555-555-5555')
            ->setPhoneAlternate('555-555-1234')
            ->setPhoneMobile('555-555-4321')
            ->setFax('555-555-5432')
            ->setWebsite('https://localhost')
            ->setAddress1('123 Main St')
            ->setAddress2('Apt. 1')
            ->setCity('Anytown')
            ->setProvince('Pennsylvania')
            ->setCountry('United States')
            ->setPostalCode('12345')
            ->setSendMailTo('Personal')
            ->setReceiveNotifications(true)
            ->setHomeGroup('My Home Group')
            ->setGroups([$groupPermissions, $groupPermission2]);

        $this->user2 = (new User())
            ->setId('2')
            ->setEmail('phpunit2@test.com')
            ->setEmployeeId('2')
            ->setGivenName('Test')
            ->setSurname('User')
            ->setPassword('password')
            ->setTimezone('EST')
            ->setLearnerNotifications(true)
            ->setSupervisorNotifications(true)
            ->setSendEmailTo('Self')
            ->setAlternateEmail('phpunit2@test1.com')
            ->setAuthenticationType('External')
            ->setSupervisors(['supervisor1', 'supervisor2'])
            ->setOrganization('organization')
            ->setTeams(['team1', 'team2'])
            ->setLanguage('English')
            ->setStatus('Active')
            ->setTitle('Title')
            ->setDivision('division')
            ->setAllowFeedback(true)
            ->setPhonePrimary('555-555-5556')
            ->setPhoneAlternate('555-555-1235')
            ->setPhoneMobile('555-555-4320')
            ->setFax('555-555-5431')
            ->setWebsite('https://localhost')
            ->setAddress1('124 Main St')
            ->setAddress2('Apt. 1')
            ->setCity('Anytown')
            ->setProvince('Pennsylvania')
            ->setCountry('United States')
            ->setPostalCode('12345')
            ->setSendMailTo('Personal')
            ->setReceiveNotifications(true)
            ->setHomeGroup('My Home Group')
            ->setGroups([$groupPermissions, $groupPermission2]);

        $this->user3 = (new User())
            ->setId('3')
            ->setEmail('phpunit3@test.com')
            ->setEmployeeId('3')
            ->setGivenName('Inactive')
            ->setSurname('User')
            ->setPassword('password')
            ->setTimezone('EST')
            ->setLearnerNotifications(true)
            ->setSupervisorNotifications(true)
            ->setSendEmailTo('Self')
            ->setAlternateEmail('phpunit3@test1.com')
            ->setAuthenticationType('External')
            ->setSupervisors(['supervisor1', 'supervisor2'])
            ->setOrganization('organization')
            ->setTeams(['team1', 'team2'])
            ->setLanguage('English')
            ->setStatus('Inactive')
            ->setTitle('Title')
            ->setDivision('division')
            ->setAllowFeedback(true)
            ->setPhonePrimary('555-555-5551')
            ->setPhoneAlternate('555-555-1232')
            ->setPhoneMobile('555-555-4323')
            ->setFax('555-555-5434')
            ->setWebsite('https://localhost')
            ->setAddress1('125 Main St')
            ->setAddress2('Apt. 1')
            ->setCity('Anytown')
            ->setProvince('Pennsylvania')
            ->setCountry('United States')
            ->setPostalCode('12345')
            ->setSendMailTo('Personal')
            ->setReceiveNotifications(true)
            ->setHomeGroup('My Home Group')
            ->setGroups([$groupPermissions, $groupPermission2]);
    }
    /**
     * Test agreement between getters and setters.
     */
    public function testAgreement() {
        $accountApi = 'account';
        $userApi = 'user';
        $httpClient = (new HttpClient(['base_uri' => 'https://localhost']));
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi)
            ->setHttpClient($httpClient);
        self::assertEquals($accountApi, $client->getAccountApi());
        self::assertEquals($userApi, $client->getUserApi());
        self::assertEquals($httpClient, $client->getHttpClient());
    }

    /**
     * Test that createUser() throws an exception if the Account API Key is
     * not set prior to calling the method.
     */
    public function testCreateUserThrowsExceptionWhenAccountAPIKeyNotSet() {
        $client = (new Client());
        
        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'Account API key must be set before creating a query.'
        );
        $client->createUser($this->user1);
    }

    /**
     * Test that createUser() throws an exception if the User API Key is not
     * set prior to calling the method.
     */
    public function testCreateUserThrowsExceptionWhenUserAPIKeyNotSet() {
        $accountApi = 'account';
        $client = (new Client())
            ->setAccountApi($accountApi);

        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'User API key must be set before creating a query.'
        );
        $client->createUser($this->user1);
    }

    /**
     * Test that createUser() passes the correct information into the API
     * when making the request.
     */
    public function testCreateUserMakesCorrectAPICall() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        /**
         * The response needs a body because createUser() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by createUser() is correct. The
         * processing of the response will be tested further down.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $info->addChild('Email', 'test@test.com');
        $info->addChild('EmployeeID', '1');
        $xml->addChild('Errors');
        $body = $xml->asXML();

        // Set up the container to capture the request.
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
        
        // Make the request.
        $client->createUser($this->user1);

        // Make sure there is only 1 request, then translate it to XML.
        self::assertCount(1, $container);
        $package = $container[0]['options']['package'];
        $packageAsXml = simplexml_load_string($package);

        // Ensure that the package begins with a <SmarterU> tag and has the
        // correct children.
        self::assertEquals($packageAsXml->getName(), 'SmarterU');
        $elements = [];
        foreach ($packageAsXml->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $packageAsXml->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $packageAsXml->UserAPI);
        self::assertContains('Method', $elements);
        self::assertEquals('createUser', $packageAsXml->Method);
        self::assertContains('Parameters', $elements);

        // Ensure that the <Parameters> tag has the correct children.
        $parameters = [];
        foreach ($packageAsXml->Parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        $userInfo = [];
        foreach ($packageAsXml->Parameters->User->children() as $user) {
            $userInfo[] = $user->getName();
        }
        self::assertCount(5, $userInfo);
        self::assertContains('Info', $userInfo);
        self::assertContains('Profile', $userInfo);
        self::assertContains('Groups', $userInfo);
        self::assertContains('Venues', $userInfo);
        self::assertContains('Wages', $userInfo);

        // Ensure that the <Info> tag has the correct children.
        $infoTag = [];
        foreach ($packageAsXml->Parameters->User->Info->children() as $info) {
            $infoTag[] = $info->getName();
        }
        self::assertCount(11, $infoTag);
        self::assertContains('Email', $infoTag);
        self::assertEquals(
            $this->user1->getEmail(),
            $packageAsXml->Parameters->User->Info->Email
        );
        self::assertContains('EmployeeID', $infoTag);
        self::assertEquals(
            $this->user1->getEmployeeId(),
            $packageAsXml->Parameters->User->Info->EmployeeID
        );
        self::assertContains('GivenName', $infoTag);
        self::assertEquals(
            $this->user1->getGivenName(),
            $packageAsXml->Parameters->User->Info->GivenName
        );
        self::assertContains('Surname', $infoTag);
        self::assertEquals(
            $this->user1->getSurname(),
            $packageAsXml->Parameters->User->Info->Surname
        );
        self::assertContains('Password', $infoTag);
        self::assertEquals(
            $this->user1->getPassword(),
            $packageAsXml->Parameters->User->Info->Password
        );
        self::assertContains('Timezone', $infoTag);
        self::assertEquals(
            $this->user1->getTimezone(),
            $packageAsXml->Parameters->User->Info->Timezone
        );
        self::assertContains('LearnerNotifications', $infoTag);
        self::assertEquals(
            (string) $this->user1->getLearnerNotifications(),
            $packageAsXml->Parameters->User->Info->LearnerNotifications
        );
        self::assertContains('SupervisorNotifications', $infoTag);
        self::assertEquals(
            (string) $this->user1->getSupervisorNotifications(),
            $packageAsXml->Parameters->User->Info->SupervisorNotifications
        );
        self::assertContains('SendEmailTo', $infoTag);
        self::assertEquals(
            $this->user1->getSendEmailTo(),
            $packageAsXml->Parameters->User->Info->SendEmailTo
        );
        self::assertContains('AlternateEmail', $infoTag);
        self::assertEquals(
            $this->user1->getAlternateEmail(),
            $packageAsXml->Parameters->User->Info->AlternateEmail
        );
        self::assertContains('AuthenticationType', $infoTag);
        self::assertEquals(
            $this->user1->getAuthenticationType(),
            $packageAsXml->Parameters->User->Info->AuthenticationType
        );

        // Ensure that the <Profile> tag has the correct children.
        $profileTag = [];
        foreach ($packageAsXml->Parameters->User->Profile->children() as $profile) {
            $profileTag[] = $profile->getName();
        }
        self::assertCount(22, $profileTag);
        self::assertContains('Supervisors', $profileTag);
        $supervisors = $packageAsXml->Parameters->User->Profile->Supervisors->asXML();
        $supervisor1 = 
            '<Supervisors><Supervisor>'
            . $this->user1->getSupervisors()[0]
            . '</Supervisor>';
        $supervisor2 =
            '<Supervisor>'
            . $this->user1->getSupervisors()[1]
            . '</Supervisor></Supervisors>';
        self::assertStringContainsString($supervisor1, $supervisors);
        self::assertStringContainsString($supervisor2, $supervisors);
        self::assertContains('Organization', $profileTag);
        self::assertEquals(
            $this->user1->getOrganization(),
            $packageAsXml->Parameters->User->Profile->Organization
        );
        self::assertContains('Teams', $profileTag);
        $teams = $packageAsXml->Parameters->User->Profile->Teams->asXML();
        $team1 = '<Teams><Team>' . $this->user1->getTeams()[0] . '</Team>';
        $team2 = '<Team>' . $this->user1->getTeams()[1] . '</Team></Teams>';
        self::assertStringContainsString($team1, $teams);
        self::assertStringContainsString($team2, $teams);
        self::assertContains('Language', $profileTag);
        self::assertEquals(
            $this->user1->getLanguage(),
            $packageAsXml->Parameters->User->Profile->Language
        );
        self::assertContains('Status', $profileTag);
        self::assertEquals(
            $this->user1->getStatus(),
            $packageAsXml->Parameters->User->Profile->Status
        );
        self::assertContains('Title', $profileTag);
        self::assertEquals(
            $this->user1->getTitle(),
            $packageAsXml->Parameters->User->Profile->Title
        );
        self::assertContains('Division', $profileTag);
        self::assertEquals(
            $this->user1->getDivision(),
            $packageAsXml->Parameters->User->Profile->Division
        );
        self::assertContains('AllowFeedback', $profileTag);
        self::assertEquals(
            (string) $this->user1->getAllowFeedback(),
            $packageAsXml->Parameters->User->Profile->AllowFeedback
        );
        self::assertContains('PhonePrimary', $profileTag);
        self::assertEquals(
            $this->user1->getPhonePrimary(),
            $packageAsXml->Parameters->User->Profile->PhonePrimary
        );
        self::assertContains('PhoneAlternate', $profileTag);
        self::assertEquals(
            $this->user1->getPhoneAlternate(),
            $packageAsXml->Parameters->User->Profile->PhoneAlternate
        );
        self::assertContains('PhoneMobile', $profileTag);
        self::assertEquals(
            $this->user1->getPhoneMobile(),
            $packageAsXml->Parameters->User->Profile->PhoneMobile
        );
        self::assertContains('Fax', $profileTag);
        self::assertEquals(
            $this->user1->getFax(),
            $packageAsXml->Parameters->User->Profile->Fax
        );
        self::assertContains('Website', $profileTag);
        self::assertEquals(
            $this->user1->getWebsite(),
            $packageAsXml->Parameters->User->Profile->Website
        );
        self::assertContains('Address1', $profileTag);
        self::assertEquals(
            $this->user1->getAddress1(),
            $packageAsXml->Parameters->User->Profile->Address1
        );
        self::assertContains('Address2', $profileTag);
        self::assertEquals(
            $this->user1->getAddress2(),
            $packageAsXml->Parameters->User->Profile->Address2
        );
        self::assertContains('City', $profileTag);
        self::assertEquals(
            $this->user1->getCity(),
            $packageAsXml->Parameters->User->Profile->City
        );
        self::assertContains('Province', $profileTag);
        self::assertEquals(
            $this->user1->getProvince(),
            $packageAsXml->Parameters->User->Profile->Province
        );
        self::assertContains('Country', $profileTag);
        self::assertEquals(
            $this->user1->getCountry(),
            $packageAsXml->Parameters->User->Profile->Country
        );
        self::assertContains('PostalCode', $profileTag);
        self::assertEquals(
            $this->user1->getPostalCode(),
            $packageAsXml->Parameters->User->Profile->PostalCode
        );
        self::assertContains('SendMailTo', $profileTag);
        self::assertEquals(
            $this->user1->getSendMailTo(),
            $packageAsXml->Parameters->User->Profile->SendMailTo
        );
        self::assertContains('ReceiveNotifications', $profileTag);
        self::assertEquals(
            (string) $this->user1->getReceiveNotifications(),
            $packageAsXml->Parameters->User->Profile->ReceiveNotifications
        );
        self::assertContains('HomeGroup', $profileTag);
        self::assertEquals(
            $this->user1->getHomeGroup(),
            $packageAsXml->Parameters->User->Profile->HomeGroup
        );

        // Ensure that the <Groups> tag has the correct children.
        $group1 = $packageAsXml->Parameters->User->Groups->Group[0];
        $group2 = $packageAsXml->Parameters->User->Groups->Group[1];
        $group1Elements = [];
        foreach ($group1->children() as $group) {
            $group1Elements[] = $group->getName();
        }
        self::assertCount(3, $group1Elements);
        self::assertContains('GroupName', $group1Elements);
        self::assertEquals(
            $this->user1->getGroups()[0]->getGroupName(),
            $group1->GroupName
        );
        self::assertContains('Permission', $group1Elements);
        $permission1 = $group1->Permission[0];
        $permission2 = $group1->Permission[1];
        $permission1Tags = [];
        foreach ($permission1->children() as $tag) {
            $permission1Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission1Tags);
        self::assertContains('Action', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[0]->getAction(),
            $group1->Permission[0]->Action
        );
        self::assertContains('Code', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[0]->getCode(),
            $group1->Permission[0]->Code
        );
        $permission2Tags = [];
        foreach ($permission2->children() as $tag) {
            $permission2Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission2Tags);
        self::assertContains('Action', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[1]->getAction(),
            $group1->Permission[1]->Action
        );
        self::assertContains('Code', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[1]->getCode(),
            $group1->Permission[1]->Code
        );

        $group2Elements = [];
        foreach ($group2->children() as $group) {
            $group2Elements[] = $group->getName();
        }
        self::assertCount(3, $group2Elements);
        self::assertContains('GroupName', $group2Elements);
        self::assertEquals(
            $this->user1->getGroups()[1]->getGroupName(),
            $group2->GroupName
        );
        self::assertContains('Permission', $group2Elements);
        $permission1 = $group2->Permission[0];
        $permission2 = $group2->Permission[1];
        $permission1Tags = [];
        foreach ($permission1->children() as $tag) {
            $permission1Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission1Tags);
        self::assertContains('Action', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[0]->getAction(),
            $group1->Permission[0]->Action
        );
        self::assertContains('Code', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[0]->getCode(),
            $group1->Permission[0]->Code
        );
        $permission2Tags = [];
        foreach ($permission2->children() as $tag) {
            $permission2Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission2Tags);
        self::assertContains('Action', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[1]->getAction(),
            $group1->Permission[1]->Action
        );
        self::assertContains('Code', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[1]->getCode(),
            $group1->Permission[1]->Code
        );

        // Ensure that the <Venues> and <Wages> tags are empty.
        self::assertCount(
            0,
            $packageAsXml->Parameters->User->Venues->children()
        );
        self::assertCount(
            0,
            $packageAsXml->Parameters->User->Wages->Children()
        );
    }

    /**
     * Test that createUser() throws an exception when the request results
     * in an HTTP error.
     */
    public function testCreateUserThrowsExceptionWhenHTTPErrorOccurs() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $response = new Response(404);

        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(HttpException::class);
        self::expectExceptionMessage('Client error: ');
        $client->createUser($this->user1);
    }

    /**
     * Test that createUser() throws an exception when the SmarterU API
     * returns a fatal error, as indicated by the value of the <Result>
     * tag.
     */
    public function testCreateUserThrowsExceptionWhenFatalErrorReturned() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
    
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Failed');
        $xml->addChild('Info');
        $errors = $xml->addChild('Errors');
        $error1 = $errors->addChild('Error');
        $error1->addChild('ErrorID', 'Error1');
        $error1->addChild('ErrorMessage', 'Testing');
        $error2 = $errors->addChild('Error');
        $error2->addChild('ErrorID', 'Error2');
        $error2->addChild('ErrorMessage', '123');
        $body = $xml->asXML();

        $response = new Response(200, [], $body);
    
        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(SmarterUException::class);
        self::expectExceptionMessage('Error1: Testing, Error2: 123');
        $client->createUser($this->user1);
    }

    /**
     * Test that createUser() returns the expected output when the SmarterU API
     * returns a non-fatal error.
     */
    public function testCreateUserHandlesNonFatalError() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $info->addChild('Email', $this->user1->getEmail());
        $info->addChild('EmployeeID', $this->user1->getEmployeeId());
        $errors = $xml->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('ErrorID', 'Error 1');
        $error->addChild('ErrorMessage', 'Non-fatal Error');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->createUser($this->user1);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];

        self::assertIsArray($response);
        self::assertCount(2, $response);
        self::assertArrayHasKey('Email', $response);
        self::assertEquals($response['Email'], $this->user1->getEmail());
        self::assertArrayHasKey('EmployeeID', $response);
        self::assertEquals(
            $response['EmployeeID'],
            $this->user1->getEmployeeId()
        );

        self::assertIsArray($errors);
        self::assertCount(1, $errors);
        self::assertArrayHasKey('Error 1', $errors);
        self::assertEquals($errors['Error 1'], 'Non-fatal Error');
    }

    /**
     * Test that createUser() returns the expected output when the SmarterU API
     * does not return any errors.
     */
    public function testCreateUserReturnsExpectedResult() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $info->addChild('Email', $this->user1->getEmail());
        $info->addChild('EmployeeID', $this->user1->getEmployeeId());
        $xml->addChild('Errors');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->createUser($this->user1);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];

        self::assertIsArray($response);
        self::assertCount(2, $response);
        self::assertArrayHasKey('Email', $response);
        self::assertEquals($response['Email'], $this->user1->getEmail());
        self::assertArrayHasKey('EmployeeID', $response);
        self::assertEquals(
            $response['EmployeeID'],
            $this->user1->getEmployeeId()
        );

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }

    /**
     * Test that getUser() throws an exception when the Account API Key is not
     * set prior to making the request.
     */
    public function testGetUserThrowsExceptionWhenAccountAPIKeyNotSet() {
        $query = (new GetUserQuery())
            ->setEmail($this->user1->getEmail());

        $client = (new Client());
        
        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'Account API key must be set before creating a query.'
        );
        $client->getUser($query);
    }

    /**
     * Test that getUser() throws an exception when the User API Key is not
     * set prior to making the request.
     */
    public function testGetUserQueryThrowsExceptionWhenUserAPIKeyNotSet() {
        $query = (new GetUserQuery())
            ->setEmail($this->user1->getEmail());

        $client = (new Client())
            ->setAccountApi('account');
        
        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'User API key must be set before creating a query.'
        );
        $client->getUser($query);
    }

    /**
     * Test that getUser() passes the correct input into the SmarterU API when
     * all required information is present and the query uses the ID as the
     * user identifier.
     */
    public function testGetUserProducesCorrectInputForUserID() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new GetUserQuery())
            ->setId($this->user1->getId());

        $createdDate = '2022-07-29';
        $modifiedDate = '2022-07-30';

        /**
         * The response needs a body because getUser() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by getUser() is correct. The
         * processing of the response will be tested further down.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $user = $info->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild('Language', $this->user1->getLanguage());
        $user->addChild(
            'AllowFeedback',
            (string) $this->user1->getAllowFeedback()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild(
            'AuthenticationType',
            $this->user1->getAuthenticationType()
        );
        $user->addChild('Timezone', $this->user1->getTimezone());
        $user->addChild('AlternateEmail', $this->user1->getAlternateEmail());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('Organization', $this->user1->getOrganization());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('Supervisors');
        $user->addChild('PhonePrimary', $this->user1->getPhonePrimary());
        $user->addChild('PhoneAlternate', $this->user1->getPhoneAlternate());
        $user->addChild('PhoneMobile', $this->user1->getPhoneMobile());
        $user->addChild('SendMailTo', $this->user1->getSendMailTo());
        $user->addChild('SendEmailTo', $this->user1->getSendEmailTo());
        $user->addChild('Fax', $this->user1->getFax());
        $user->addChild('Address1', $this->user1->getAddress1());
        $user->addChild('Address2', $this->user1->getAddress2());
        $user->addChild('City', $this->user1->getCity());
        $user->addChild('PostalCode', $this->user1->getPostalCode());
        $user->addChild('Province', $this->user1->getProvince());
        $user->addChild('Country', $this->user1->getCountry());
        $user->addChild(
            'SendWeeklyTaskReminder',
            (string) $this->user1->getLearnerNotifications()
        );
        $user->addChild(
            'SendWeeklyProgressSummary',
            (string) $this->user1->getSupervisorNotifications()
        );
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $user->addChild('Roles');
        $user->addChild('CustomFields');
        $user->addChild('Venues');
        $user->addChild('Wages');
        $user->addChild(
            'ReceiveNotifications',
            (string) $this->user1->getReceiveNotifications()
        );
        $errors = $xml->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('ErrorID', 'Error 1');
        $error->addChild('ErrorMessage', 'Non-fatal Error');
        $body = $xml->asXML();

        // Set up the container to capture the request.
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
        self::assertIsString($body);

        // Make the request.
        $client->getUser($query);

        // Make sure there is only 1 request, then translate it to XML.
        self::assertCount(1, $container);
        $package = $container[0]['options']['package'];
        $packageAsXml = simplexml_load_string($package);
        
        self::assertEquals($packageAsXml->getName(), 'SmarterU');
        $elements = [];
        foreach ($packageAsXml->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $packageAsXml->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $packageAsXml->UserAPI);
        self::assertContains('Method', $elements);
        self::assertEquals('getUser', $packageAsXml->Method);
        self::assertContains('Parameters', $elements);

        // Ensure that the <Parameters> tag has the correct children.
        $parameters = [];
        foreach ($packageAsXml->Parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        $userIdentifier = [];
        foreach ($packageAsXml->Parameters->User->children() as $identifier) {
            $userIdentifier[] = $identifier->getName();
        }
        self::assertCount(1, $userIdentifier);
        self::assertContains('ID', $userIdentifier);
        self::assertEquals(
            $query->getId(),
            $packageAsXml->Parameters->User->ID
        );
    }

    /**
     * Test that getUser() passes the correct input into the SmarterU API when
     * all required information is present and the query uses the email address
     * as the user identifier.
     */
    public function testGetUserProducesCorrectInputForEmail() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new GetUserQuery())
            ->setEmail($this->user1->getEmail());

        $createdDate = '2022-07-29';
        $modifiedDate = '2022-07-30';

        /**
         * The response needs a body because getUser() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by getUser() is correct. The
         * processing of the response will be tested further down.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $user = $info->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild('Language', $this->user1->getLanguage());
        $user->addChild(
            'AllowFeedback',
            (string) $this->user1->getAllowFeedback()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild(
            'AuthenticationType',
            $this->user1->getAuthenticationType()
        );
        $user->addChild('Timezone', $this->user1->getTimezone());
        $user->addChild('AlternateEmail', $this->user1->getAlternateEmail());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('Organization', $this->user1->getOrganization());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('Supervisors');
        $user->addChild('PhonePrimary', $this->user1->getPhonePrimary());
        $user->addChild('PhoneAlternate', $this->user1->getPhoneAlternate());
        $user->addChild('PhoneMobile', $this->user1->getPhoneMobile());
        $user->addChild('SendMailTo', $this->user1->getSendMailTo());
        $user->addChild('SendEmailTo', $this->user1->getSendEmailTo());
        $user->addChild('Fax', $this->user1->getFax());
        $user->addChild('Address1', $this->user1->getAddress1());
        $user->addChild('Address2', $this->user1->getAddress2());
        $user->addChild('City', $this->user1->getCity());
        $user->addChild('PostalCode', $this->user1->getPostalCode());
        $user->addChild('Province', $this->user1->getProvince());
        $user->addChild('Country', $this->user1->getCountry());
        $user->addChild(
            'SendWeeklyTaskReminder',
            (string) $this->user1->getLearnerNotifications()
        );
        $user->addChild(
            'SendWeeklyProgressSummary',
            (string) $this->user1->getSupervisorNotifications()
        );
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $user->addChild('Roles');
        $user->addChild('CustomFields');
        $user->addChild('Venues');
        $user->addChild('Wages');
        $user->addChild(
            'ReceiveNotifications',
            (string) $this->user1->getReceiveNotifications()
        );
        $errors = $xml->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('ErrorID', 'Error 1');
        $error->addChild('ErrorMessage', 'Non-fatal Error');
        $body = $xml->asXML();

        // Set up the container to capture the request.
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
        self::assertIsString($body);

        // Make the request.
        $client->getUser($query);

        // Make sure there is only 1 request, then translate it to XML.
        self::assertCount(1, $container);
        $package = $container[0]['options']['package'];
        $packageAsXml = simplexml_load_string($package);
        
        self::assertEquals($packageAsXml->getName(), 'SmarterU');
        $elements = [];
        foreach ($packageAsXml->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $packageAsXml->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $packageAsXml->UserAPI);
        self::assertContains('Method', $elements);
        self::assertEquals('getUser', $packageAsXml->Method);
        self::assertContains('Parameters', $elements);

        // Ensure that the <Parameters> tag has the correct children.
        $parameters = [];
        foreach ($packageAsXml->Parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        $userIdentifier = [];
        foreach ($packageAsXml->Parameters->User->children() as $identifier) {
            $userIdentifier[] = $identifier->getName();
        }
        self::assertCount(1, $userIdentifier);
        self::assertContains('Email', $userIdentifier);
        self::assertEquals(
            $query->getEmail(),
            $packageAsXml->Parameters->User->Email
        );
    }

    /**
     * Test that getUser() passes the correct input into the SmarterU API when
     * all required information is present and the query uses the employee ID
     * as the user identifier.
     */
    public function testGetUserProducesCorrectInputForEmployeeID() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new GetUserQuery())
            ->setEmployeeId($this->user1->getEmployeeId());

        $createdDate = '2022-07-29';
        $modifiedDate = '2022-07-30';

        /**
         * The response needs a body because getUser() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by getUser() is correct. The
         * processing of the response will be tested further down.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $user = $info->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild('Language', $this->user1->getLanguage());
        $user->addChild(
            'AllowFeedback',
            (string) $this->user1->getAllowFeedback()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild(
            'AuthenticationType',
            $this->user1->getAuthenticationType()
        );
        $user->addChild('Timezone', $this->user1->getTimezone());
        $user->addChild('AlternateEmail', $this->user1->getAlternateEmail());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('Organization', $this->user1->getOrganization());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('Supervisors');
        $user->addChild('PhonePrimary', $this->user1->getPhonePrimary());
        $user->addChild('PhoneAlternate', $this->user1->getPhoneAlternate());
        $user->addChild('PhoneMobile', $this->user1->getPhoneMobile());
        $user->addChild('SendMailTo', $this->user1->getSendMailTo());
        $user->addChild('SendEmailTo', $this->user1->getSendEmailTo());
        $user->addChild('Fax', $this->user1->getFax());
        $user->addChild('Address1', $this->user1->getAddress1());
        $user->addChild('Address2', $this->user1->getAddress2());
        $user->addChild('City', $this->user1->getCity());
        $user->addChild('PostalCode', $this->user1->getPostalCode());
        $user->addChild('Province', $this->user1->getProvince());
        $user->addChild('Country', $this->user1->getCountry());
        $user->addChild(
            'SendWeeklyTaskReminder',
            (string) $this->user1->getLearnerNotifications()
        );
        $user->addChild(
            'SendWeeklyProgressSummary',
            (string) $this->user1->getSupervisorNotifications()
        );
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $user->addChild('Roles');
        $user->addChild('CustomFields');
        $user->addChild('Venues');
        $user->addChild('Wages');
        $user->addChild(
            'ReceiveNotifications',
            (string) $this->user1->getReceiveNotifications()
        );
        $errors = $xml->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('ErrorID', 'Error 1');
        $error->addChild('ErrorMessage', 'Non-fatal Error');
        $body = $xml->asXML();

        // Set up the container to capture the request.
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
        self::assertIsString($body);

        // Make the request.
        $client->getUser($query);

        // Make sure there is only 1 request, then translate it to XML.
        self::assertCount(1, $container);
        $package = $container[0]['options']['package'];
        $packageAsXml = simplexml_load_string($package);
        
        self::assertEquals($packageAsXml->getName(), 'SmarterU');
        $elements = [];
        foreach ($packageAsXml->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $packageAsXml->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $packageAsXml->UserAPI);
        self::assertContains('Method', $elements);
        self::assertEquals('getUser', $packageAsXml->Method);
        self::assertContains('Parameters', $elements);

        // Ensure that the <Parameters> tag has the correct children.
        $parameters = [];
        foreach ($packageAsXml->Parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        $userIdentifier = [];
        foreach ($packageAsXml->Parameters->User->children() as $identifier) {
            $userIdentifier[] = $identifier->getName();
        }
        self::assertCount(1, $userIdentifier);
        self::assertContains('EmployeeID', $userIdentifier);
        self::assertEquals(
            $query->getEmployeeId(),
            $packageAsXml->Parameters->User->EmployeeID
        );
    }

    /**
     * Test that getUser() throws an exception when the request results
     * in an HTTP error.
     */
    public function testGetUserThrowsExceptionWhenHTTPErrorOccurs() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new GetUserQuery())
            ->setId($this->user1->getId());

        $response = new Response(404);

        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(HttpException::class);
        self::expectExceptionMessage('Client error: ');
        $client->getUser($query);
    }

    /**
     * Test that getUser() throws an exception when the SmarterU API
     * returns a fatal error.
     */
    public function testGetUserThrowsExceptionWhenFatalErrorReturned() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new GetUserQuery())
            ->setId($this->user1->getId());

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
    
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Failed');
        $xml->addChild('Info');
        $errors = $xml->addChild('Errors');
        $error1 = $errors->addChild('Error');
        $error1->addChild('ErrorID', 'Error1');
        $error1->addChild('ErrorMessage', 'Testing');
        $error2 = $errors->addChild('Error');
        $error2->addChild('ErrorID', 'Error2');
        $error2->addChild('ErrorMessage', '123');
        $body = $xml->asXML();

        $response = new Response(200, [], $body);
    
        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(SmarterUException::class);
        self::expectExceptionMessage('Error1: Testing, Error2: 123');
        $client->getUser($query);
    }

    /**
     * Test that getUser() returns the expected output when the SmarterU API
     * returns a non-fatal error.
     */
    public function testGetUserHandlesNonFatalError() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new GetUserQuery())
            ->setId($this->user1->getId());
        
        $createdDate = '2022-07-29';
        $modifiedDate = '2022-07-30';

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $user = $info->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild('Language', $this->user1->getLanguage());
        $user->addChild(
            'AllowFeedback',
            (string) $this->user1->getAllowFeedback()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild(
            'AuthenticationType',
            $this->user1->getAuthenticationType()
        );
        $user->addChild('Timezone', $this->user1->getTimezone());
        $user->addChild('AlternateEmail', $this->user1->getAlternateEmail());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('Organization', $this->user1->getOrganization());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('Supervisors');
        $user->addChild('PhonePrimary', $this->user1->getPhonePrimary());
        $user->addChild('PhoneAlternate', $this->user1->getPhoneAlternate());
        $user->addChild('PhoneMobile', $this->user1->getPhoneMobile());
        $user->addChild('SendMailTo', $this->user1->getSendMailTo());
        $user->addChild('SendEmailTo', $this->user1->getSendEmailTo());
        $user->addChild('Fax', $this->user1->getFax());
        $user->addChild('Address1', $this->user1->getAddress1());
        $user->addChild('Address2', $this->user1->getAddress2());
        $user->addChild('City', $this->user1->getCity());
        $user->addChild('PostalCode', $this->user1->getPostalCode());
        $user->addChild('Province', $this->user1->getProvince());
        $user->addChild('Country', $this->user1->getCountry());
        $user->addChild(
            'SendWeeklyTaskReminder',
            (string) $this->user1->getLearnerNotifications()
        );
        $user->addChild(
            'SendWeeklyProgressSummary',
            (string) $this->user1->getSupervisorNotifications()
        );
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $user->addChild('Roles');
        $user->addChild('CustomFields');
        $user->addChild('Venues');
        $user->addChild('Wages');
        $user->addChild(
            'ReceiveNotifications',
            (string) $this->user1->getReceiveNotifications()
        );
        $errors = $xml->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('ErrorID', 'Error 1');
        $error->addChild('ErrorMessage', 'Non-fatal Error');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->getUser($query);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];

        self::assertCount(38, $response);
        self::assertArrayHasKey('ID', $response);
        self::assertEquals($this->user1->getId(), $response['ID']);
        self::assertArrayHasKey('Email', $response);
        self::assertEquals($this->user1->getEmail(), $response['Email']);
        self::assertArrayHasKey('EmployeeID', $response);
        self::assertEquals(
            $this->user1->getEmployeeId(),
            $response['EmployeeID']
        );
        self::assertArrayHasKey('CreatedDate', $response);
        self::assertEquals($createdDate, $response['CreatedDate']);
        self::assertArrayHasKey('ModifiedDate', $response);
        self::assertEquals($modifiedDate, $response['ModifiedDate']);
        self::assertArrayHasKey('GivenName', $response);
        self::assertEquals(
            $this->user1->getGivenName(),
            $response['GivenName']
        );
        self::assertArrayHasKey('Surname', $response);
        self::assertEquals($this->user1->getSurname(), $response['Surname']);
        self::assertArrayHasKey('Language', $response);
        self::assertEquals($this->user1->getLanguage(), $response['Language']);
        self::assertArrayHasKey('AllowFeedback', $response);
        self::assertEquals(
            (string) $this->user1->getAllowFeedback(),
            $response['AllowFeedback']
        );
        self::assertArrayHasKey('Status', $response);
        self::assertEquals($this->user1->getStatus(), $response['Status']);
        self::assertArrayHasKey('AuthenticationType', $response);
        self::assertEquals(
            $this->user1->getAuthenticationType(),
            $response['AuthenticationType']
        );
        self::assertArrayHasKey('Timezone', $response);
        self::assertEquals(
            $this->user1->getTimezone(),
            $response['Timezone']
        );
        self::assertArrayHasKey('AlternateEmail', $response);
        self::assertEquals(
            $this->user1->getAlternateEmail(),
            $response['AlternateEmail']
        );
        self::assertArrayHasKey('HomeGroup', $response);
        self::assertEquals(
            $this->user1->getHomeGroup(),
            $response['HomeGroup']
        );
        self::assertArrayHasKey('Organization', $response);
        self::assertEquals(
            $this->user1->getOrganization(),
            $response['Organization']
        );
        self::assertArrayHasKey('Title', $response);
        self::assertEquals($this->user1->getTitle(), $response['Title']);
        self::assertArrayHasKey('Division', $response);
        self::assertEquals($this->user1->getDivision(), $response['Division']);
        self::assertArrayHasKey('Supervisors', $response);
        self::assertIsArray($response['Supervisors']);
        self::assertCount(0, $response['Supervisors']);
        // TODO implement supervisors. For iteration 1, we can assume it's blank.
        self::assertArrayHasKey('PhonePrimary', $response);
        self::assertEquals(
            $this->user1->getPhonePrimary(),
            $response['PhonePrimary']
        );
        self::assertArrayHasKey('PhoneAlternate', $response);
        self::assertEquals(
            $this->user1->getPhoneAlternate(),
            $response['PhoneAlternate']
        );
        self::assertArrayHasKey('PhoneMobile', $response);
        self::assertEquals(
            $this->user1->getPhoneMobile(),
            $response['PhoneMobile']
        );
        self::assertArrayHasKey('SendMailTo', $response);
        self::assertEquals(
            $this->user1->getSendMailTo(),
            $response['SendMailTo']
        );
        self::assertArrayHasKey('SendEmailTo', $response);
        self::assertEquals(
            $this->user1->getSendEmailTo(),
            $response['SendEmailTo']
        );
        self::assertArrayHasKey('Fax', $response);
        self::assertEquals($this->user1->getFax(), $response['Fax']);
        self::assertArrayHasKey('Address1', $response);
        self::assertEquals($this->user1->getAddress1(), $response['Address1']);
        self::assertArrayHasKey('Address2', $response);
        self::assertEquals($this->user1->getAddress2(), $response['Address2']);
        self::assertArrayHasKey('City', $response);
        self::assertEquals($this->user1->getCity(), $response['City']);
        self::assertArrayHasKey('PostalCode', $response);
        self::assertEquals(
            $this->user1->getPostalCode(),
            $response['PostalCode']
        );
        self::assertArrayHasKey('Province', $response);
        self::assertEquals($this->user1->getProvince(), $response['Province']);
        self::assertArrayHasKey('Country', $response);
        self::assertEquals($this->user1->getCountry(), $response['Country']);
        self::assertArrayHasKey('LearnerNotifications', $response);
        self::assertEquals(
            (string) $this->user1->getLearnerNotifications(),
            $response['LearnerNotifications']
        );
        self::assertArrayHasKey('SupervisorNotifications', $response);
        self::assertEquals(
            (string) $this->user1->getSupervisorNotifications(),
            $response['SupervisorNotifications']
        );
        self::assertArrayHasKey('Teams', $response);
        self::assertIsArray($response['Teams']);
        self::assertCount(count($this->user1->getTeams()), $response['Teams']);
        foreach ($this->user1->getTeams() as $team) {
            self::assertContains($team, $response['Teams']);
        }
        self::assertArrayHasKey('Roles', $response);
        self::assertIsArray($response['Roles']);
        self::assertCount(0, $response['Roles']);
        self::assertArrayHasKey('CustomFields', $response);
        self::assertIsArray($response['CustomFields']);
        self::assertCount(0, $response['CustomFields']);
        self::assertArrayHasKey('Venues', $response);
        self::assertIsArray($response['Venues']);
        self::assertCount(0, $response['Venues']);
        self::assertArrayHasKey('Wages', $response);
        self::assertIsArray($response['Wages']);
        self::assertCount(0, $response['Wages']);

        self::assertIsArray($errors);
        self::assertCount(1, $errors);
        self::assertArrayHasKey('Error 1', $errors);
        self::assertEquals($errors['Error 1'], 'Non-fatal Error');
    }

    /**
     * Test that getUser() returns the expected output when the SmarterU API
     * does not return any errors.
     */
    public function testGetUserReturnsExpectedResult() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new GetUserQuery())
            ->setId($this->user1->getId());
        
        $createdDate = '2022-07-29';
        $modifiedDate = '2022-07-30';

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $user = $info->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild('Language', $this->user1->getLanguage());
        $user->addChild(
            'AllowFeedback',
            (string) $this->user1->getAllowFeedback()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild(
            'AuthenticationType',
            $this->user1->getAuthenticationType()
        );
        $user->addChild('Timezone', $this->user1->getTimezone());
        $user->addChild('AlternateEmail', $this->user1->getAlternateEmail());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('Organization', $this->user1->getOrganization());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('Supervisors');
        $user->addChild('PhonePrimary', $this->user1->getPhonePrimary());
        $user->addChild('PhoneAlternate', $this->user1->getPhoneAlternate());
        $user->addChild('PhoneMobile', $this->user1->getPhoneMobile());
        $user->addChild('SendMailTo', $this->user1->getSendMailTo());
        $user->addChild('SendEmailTo', $this->user1->getSendEmailTo());
        $user->addChild('Fax', $this->user1->getFax());
        $user->addChild('Address1', $this->user1->getAddress1());
        $user->addChild('Address2', $this->user1->getAddress2());
        $user->addChild('City', $this->user1->getCity());
        $user->addChild('PostalCode', $this->user1->getPostalCode());
        $user->addChild('Province', $this->user1->getProvince());
        $user->addChild('Country', $this->user1->getCountry());
        $user->addChild(
            'SendWeeklyTaskReminder',
            (string) $this->user1->getLearnerNotifications()
        );
        $user->addChild(
            'SendWeeklyProgressSummary',
            (string) $this->user1->getSupervisorNotifications()
        );
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $user->addChild('Roles');
        $user->addChild('CustomFields');
        $user->addChild('Venues');
        $user->addChild('Wages');
        $user->addChild(
            'ReceiveNotifications',
            (string) $this->user1->getReceiveNotifications()
        );
        $errors = $xml->addChild('Errors');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->getUser($query);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];

        self::assertCount(38, $response);
        self::assertArrayHasKey('ID', $response);
        self::assertEquals($this->user1->getId(), $response['ID']);
        self::assertArrayHasKey('Email', $response);
        self::assertEquals($this->user1->getEmail(), $response['Email']);
        self::assertArrayHasKey('EmployeeID', $response);
        self::assertEquals(
            $this->user1->getEmployeeId(),
            $response['EmployeeID']
        );
        self::assertArrayHasKey('CreatedDate', $response);
        self::assertEquals($createdDate, $response['CreatedDate']);
        self::assertArrayHasKey('ModifiedDate', $response);
        self::assertEquals($modifiedDate, $response['ModifiedDate']);
        self::assertArrayHasKey('GivenName', $response);
        self::assertEquals(
            $this->user1->getGivenName(),
            $response['GivenName']
        );
        self::assertArrayHasKey('Surname', $response);
        self::assertEquals($this->user1->getSurname(), $response['Surname']);
        self::assertArrayHasKey('Language', $response);
        self::assertEquals(
            $this->user1->getLanguage(),
            $response['Language']
        );
        self::assertArrayHasKey('AllowFeedback', $response);
        self::assertEquals(
            (string) $this->user1->getAllowFeedback(),
            $response['AllowFeedback']
        );
        self::assertArrayHasKey('Status', $response);
        self::assertEquals($this->user1->getStatus(), $response['Status']);
        self::assertArrayHasKey('AuthenticationType', $response);
        self::assertEquals(
            $this->user1->getAuthenticationType(),
            $response['AuthenticationType']
        );
        self::assertArrayHasKey('Timezone', $response);
        self::assertEquals($this->user1->getTimezone(), $response['Timezone']);
        self::assertArrayHasKey('AlternateEmail', $response);
        self::assertEquals(
            $this->user1->getAlternateEmail(),
            $response['AlternateEmail']
        );
        self::assertArrayHasKey('HomeGroup', $response);
        self::assertEquals(
            $this->user1->getHomeGroup(),
            $response['HomeGroup']
        );
        self::assertArrayHasKey('Organization', $response);
        self::assertEquals(
            $this->user1->getOrganization(),
            $response['Organization']
        );
        self::assertArrayHasKey('Title', $response);
        self::assertEquals($this->user1->getTitle(), $response['Title']);
        self::assertArrayHasKey('Division', $response);
        self::assertEquals($this->user1->getDivision(), $response['Division']);
        self::assertArrayHasKey('Supervisors', $response);
        self::assertIsArray($response['Supervisors']);
        self::assertCount(0, $response['Supervisors']);
        // TODO implement supervisors. For iteration 1, we can assume it's blank.
        self::assertArrayHasKey('PhonePrimary', $response);
        self::assertEquals(
            $this->user1->getPhonePrimary(),
            $response['PhonePrimary']
        );
        self::assertArrayHasKey('PhoneAlternate', $response);
        self::assertEquals(
            $this->user1->getPhoneAlternate(),
            $response['PhoneAlternate']
        );
        self::assertArrayHasKey('PhoneMobile', $response);
        self::assertEquals(
            $this->user1->getPhoneMobile(),
            $response['PhoneMobile']
        );
        self::assertArrayHasKey('SendMailTo', $response);
        self::assertEquals(
            $this->user1->getSendMailTo(),
            $response['SendMailTo']
        );
        self::assertArrayHasKey('SendEmailTo', $response);
        self::assertEquals(
            $this->user1->getSendEmailTo(),
            $response['SendEmailTo']
        );
        self::assertArrayHasKey('Fax', $response);
        self::assertEquals($this->user1->getFax(), $response['Fax']);
        self::assertArrayHasKey('Address1', $response);
        self::assertEquals($this->user1->getAddress1(), $response['Address1']);
        self::assertArrayHasKey('Address2', $response);
        self::assertEquals($this->user1->getAddress2(), $response['Address2']);
        self::assertArrayHasKey('City', $response);
        self::assertEquals($this->user1->getCity(), $response['City']);
        self::assertArrayHasKey('PostalCode', $response);
        self::assertEquals(
            $this->user1->getPostalCode(),
            $response['PostalCode']
        );
        self::assertArrayHasKey('Province', $response);
        self::assertEquals($this->user1->getProvince(), $response['Province']);
        self::assertArrayHasKey('Country', $response);
        self::assertEquals($this->user1->getCountry(), $response['Country']);
        self::assertArrayHasKey('LearnerNotifications', $response);
        self::assertEquals(
            (string) $this->user1->getLearnerNotifications(),
            $response['LearnerNotifications']
        );
        self::assertArrayHasKey('SupervisorNotifications', $response);
        self::assertEquals(
            (string) $this->user1->getSupervisorNotifications(),
            $response['SupervisorNotifications']
        );
        self::assertArrayHasKey('Teams', $response);
        self::assertIsArray($response['Teams']);
        self::assertCount(count($this->user1->getTeams()), $response['Teams']);
        foreach ($this->user1->getTeams() as $team) {
            self::assertContains($team, $response['Teams']);
        }
        self::assertArrayHasKey('Roles', $response);
        self::assertIsArray($response['Roles']);
        self::assertCount(0, $response['Roles']);
        self::assertArrayHasKey('CustomFields', $response);
        self::assertIsArray($response['CustomFields']);
        self::assertCount(0, $response['CustomFields']);
        self::assertArrayHasKey('Venues', $response);
        self::assertIsArray($response['Venues']);
        self::assertCount(0, $response['Venues']);
        self::assertArrayHasKey('Wages', $response);
        self::assertIsArray($response['Wages']);
        self::assertCount(0, $response['Wages']);

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }

    /**
     * Test that listUsers() throws an exception when the Account API Key is
     * not set prior to calling the method.
     */
    public function testListUsersThrowsExceptionWhenAccountAPIKeyNotSet() {
        $query = (new ListUsersQuery())
            ->setUserStatus('Active');

        $client = (new Client());
        
        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'Account API key must be set before creating a query.'
        );
        $client->listUsers($query);
    }

    /**
     * Test that listUsers() throws an exception when the User API Key is not
     * set prior to making the request.
     */
    public function testListUsersThrowsExceptionWhenUserAPIKeyNotSet() {
        $query = (new ListUsersQuery())
            ->setUserStatus('Active');

        $client = (new Client())
            ->setAccountApi('account');
        
        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'User API key must be set before creating a query.'
        );
        $client->listUsers($query);
    }

    /**
     * Test that listUsers() sends the correct information when making an API
     * call.
     */
    public function testListUsersMakesCorrectAPICall() {
        $sortField = 'NAME';
        $sortOrder = 'ASC';
        $email = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue($this->user1->getEmail());
        $employeeId = (new MatchTag())
            ->setMatchType('CONTAINS')
            ->setValue($this->user1->getEmployeeId());
        $name = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue(
                $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
            );
        $now = new DateTime();
        $time1 = new DateTime('2022-07-25');
        $time2 = new DateTime('2022-07-26');
        $time3 = new DateTime('2022-07-28');
        $createdDate = (new DateRangeTag())
            ->setDateFrom($time2)
            ->setDateTo($now);
        $modifiedDate = (new DateRangeTag())
            ->setDateFrom($time1)
            ->setDateTo($time3);
        $groupName = 'My Group';
        $status = 'ACTIVE';
        $teams = ['team1', 'team2'];

        $accountApi = 'account';
        $userApi = 'user';

        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new ListUsersQuery())
            ->setSortField($sortField)
            ->setSortOrder($sortOrder)
            ->setEmail($email)
            ->setEmployeeId($employeeId)
            ->setName($name)
            ->setgroupName($groupName)
            ->setUserStatus($status)
            ->setCreatedDate($createdDate)
            ->setModifiedDate($modifiedDate)
            ->setTeams($teams);

        $createdDate = '2022-07-20';
        $modifiedDate = '2022-07-29';

        /**
         * The response needs a body because listUsers() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by listUsers() is correct. The
         * processing of the response will be tested over the next few tests.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $users = $info->addChild('Users');
        $user = $info->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild(
            'Name',
            $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $info->addChild('TotalRecords', '1');
        $errors = $xml->addChild('Errors');
        $body = $xml->asXML();

        // Set up the container to capture the request.
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);

        // Make the request.
        $client->listUsers($query);

        // Make sure there is only 1 request, then translate it to XML.
        self::assertCount(1, $container);
        $package = $container[0]['options']['package'];
        $packageAsXml = simplexml_load_string($package);
        
        self::assertEquals($packageAsXml->getName(), 'SmarterU');
        $elements = [];
        foreach ($packageAsXml->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $packageAsXml->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $packageAsXml->UserAPI);
        self::assertContains('Method', $elements);
        self::assertEquals('listUsers', $packageAsXml->Method);
        self::assertContains('Parameters', $elements);

        // Ensure that the <Parameters> tag has the correct children.
        $parameters = [];
        foreach ($packageAsXml->Parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        $userInfo = [];
        foreach ($packageAsXml->Parameters->User->children() as $info) {
            $userInfo[] = $info->getName();
        }
        self::assertCount(5, $userInfo);
        self::assertContains('Page', $userInfo);
        self::assertEquals(
            (string) $query->getPage(),
            $packageAsXml->Parameters->User->Page
        );
        self::assertContains('PageSize', $userInfo);
        self::assertEquals(
            (string) $query->getPageSize(),
            $packageAsXml->Parameters->User->PageSize
        );
        self::assertContains('SortField', $userInfo);
        self::assertEquals(
            $query->getSortField(),
            $packageAsXml->Parameters->User->SortField
        );
        self::assertContains('SortOrder', $userInfo);
        self::assertEquals(
            $query->getSortOrder(),
            $packageAsXml->Parameters->User->SortOrder
        );
        self::assertContains('Filters', $userInfo);
        $filters = [];
        foreach ($packageAsXml->Parameters->User->Filters->children() as $filter) {
            $filters[] = $filter->getName();
        }
        self::assertCount(6, $filters);
        self::assertContains('Users', $filters);
        $users = [];
        foreach ($packageAsXml->Parameters->User->Filters->Users->children() as $user) {
            $users[] = $user->getName();
        }
        self::assertCount(1, $users);
        self::assertContains('UserIdentifier', $users);
        $userIdentifiers = [];
        foreach ($packageAsXml->Parameters->User->Filters->Users->UserIdentifier->children() as $identifier) {
            $userIdentifiers[] = $identifier->getName();
        }
        self::assertCount(3, $userIdentifiers);
        self::assertContains('Email', $userIdentifiers);
        $emailTag = [];
        foreach ($packageAsXml->Parameters->User->Filters->Users->UserIdentifier->Email->children() as $tag) {
            $emailTag[] = $tag->getName();
        }
        self::assertCount(2, $emailTag);
        self::assertContains('MatchType', $emailTag);
        self::assertEquals(
            $email->getMatchType(),
            $packageAsXml->Parameters->User->Filters->Users->UserIdentifier->Email->MatchType
        );
        self::assertContains('Value', $emailTag);
        self::assertEquals(
            $email->getValue(),
            $packageAsXml->Parameters->User->Filters->Users->UserIdentifier->Email->Value
        );
        self::assertContains('EmployeeID', $userIdentifiers);
        $employeeIdTag = [];
        foreach ($packageAsXml->Parameters->User->Filters->Users->UserIdentifier->EmployeeID->children() as $tag) {
            $employeeIdTag[] = $tag->getName();
        }
        self::assertCount(2, $employeeIdTag);
        self::assertContains('MatchType', $employeeIdTag);
        self::assertEquals(
            $employeeId->getMatchType(),
            $packageAsXml->Parameters->User->Filters->Users->UserIdentifier->EmployeeID->MatchType
        );
        self::assertContains('Value', $employeeIdTag);
        self::assertEquals(
            $employeeId->getValue(),
            $packageAsXml->Parameters->User->Filters->Users->UserIdentifier->EmployeeID->Value
        );
        self::assertContains('Name', $userIdentifiers);
        $nameTag = [];
        foreach ($packageAsXml->Parameters->User->Filters->Users->UserIdentifier->Name->children() as $tag) {
            $nameTag[] = $tag->getName();
        }
        self::assertCount(2, $nameTag);
        self::assertContains('MatchType', $nameTag);
        self::assertEquals(
            $name->getMatchType(),
            $packageAsXml->Parameters->User->Filters->Users->UserIdentifier->Name->MatchType
        );
        self::assertContains('Value', $nameTag);
        self::assertEquals(
            $name->getValue(),
            $packageAsXml->Parameters->User->Filters->Users->UserIdentifier->Name->Value
        );
        self::assertContains('GroupName', $filters);
        self::assertEquals(
            $groupName,
            $packageAsXml->Parameters->User->Filters->GroupName
        );
        self::assertContains('UserStatus', $filters);
        self::assertEquals(
            $status,
            $packageAsXml->Parameters->User->Filters->UserStatus
        );
        self::assertContains('CreatedDate', $filters);
        $createdDateTag = [];
        foreach ($packageAsXml->Parameters->User->Filters->CreatedDate->children() as $tag) {
            $createdDateTag[] = $tag->getName();
        }
        self::assertCount(2, $createdDateTag);
        self::assertContains('CreatedDateFrom', $createdDateTag);
        self::assertEquals(
            $time2->format('d/m/Y'),
            $packageAsXml->Parameters->User->Filters->CreatedDate->CreatedDateFrom
        );
        self::assertContains('CreatedDateTo', $createdDateTag);
        self::assertEquals(
            $now->format('d/m/Y'),
            $packageAsXml->Parameters->User->Filters->CreatedDate->CreatedDateTo
        );
        self::assertContains('ModifiedDate', $filters);
        $modifiedDateTag = [];
        foreach ($packageAsXml->Parameters->User->Filters->ModifiedDate->children() as $tag) {
            $modifiedDateTag[] = $tag->getName();
        }
        self::assertCount(2, $modifiedDateTag);
        self::assertContains('ModifiedDateFrom', $modifiedDateTag);
        self::assertEquals(
            $time1->format('d/m/Y'),
            $packageAsXml->Parameters->User->Filters->ModifiedDate->ModifiedDateFrom
        );
        self::assertContains('ModifiedDateTo', $modifiedDateTag);
        self::assertEquals(
            $time3->format('d/m/Y'),
            $packageAsXml->Parameters->User->Filters->ModifiedDate->ModifiedDateTo
        );
        self::assertContains('Teams', $filters);
        $teams = [];
        foreach ((array) $packageAsXml->Parameters->User->Filters->Teams->children() as $team) {
            $teams[] = $team;
        }
        $teams = $teams[0];
        self::assertCount(count($this->user1->getTeams()), $teams);
        foreach ($this->user1->getTeams() as $team) {
            self::assertContains($team, $teams);
        }
    }

    /**
     * Test that listUsers() throws an exception when an HTTP error occurs
     * while attempting to make a request to the SmarterU API.
     */
    public function testListUsersThrowsExceptionWhenHTTPErrorOccurs() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new ListUsersQuery())
            ->setUserStatus('Active');

        $response = new Response(404);

        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(HttpException::class);
        self::expectExceptionMessage('Client error: ');
        $client->listUsers($query);
    }

    /**
     * Test that listUsers() throws an exception when the SmarterU API
     * returns a fatal error.
     */
    public function testListUsersThrowsExceptionWhenFatalErrorReturned() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new ListUsersQuery())
            ->setUserStatus('Active');

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
    
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Failed');
        $xml->addChild('Info');
        $errors = $xml->addChild('Errors');
        $error1 = $errors->addChild('Error');
        $error1->addChild('ErrorID', 'Error1');
        $error1->addChild('ErrorMessage', 'Testing');
        $error2 = $errors->addChild('Error');
        $error2->addChild('ErrorID', 'Error2');
        $error2->addChild('ErrorMessage', '123');
        $body = $xml->asXML();

        $response = new Response(200, [], $body);
    
        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(SmarterUException::class);
        self::expectExceptionMessage('Error1: Testing, Error2: 123');
        $client->listUsers($query);
    }

    /**
     * Test that listUsers() returns the expected output when the SmarterU API
     * returns a non-fatal error.
     */
    public function testListUsersHandlesNonFatalError() {
        $sortField = 'NAME';
        $sortOrder = 'ASC';
        $email = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue($this->user1->getEmail());
        $employeeId = (new MatchTag())
            ->setMatchType('CONTAINS')
            ->setValue($this->user1->getEmployeeId());
        $name = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue(
                $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
            );
        $now = new DateTime();
        $time1 = new DateTime('2022-07-25');
        $time2 = new DateTime('2022-07-26');
        $time3 = new DateTime('2022-07-28');
        $createdDate = (new DateRangeTag())
            ->setDateFrom($time2)
            ->setDateTo($now);
        $modifiedDate = (new DateRangeTag())
            ->setDateFrom($time1)
            ->setDateTo($time3);
        $groupName = 'My Group';
        $status = 'ACTIVE';
        $teams = ['team1', 'team2'];

        $accountApi = 'account';
        $userApi = 'user';

        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new ListUsersQuery())
            ->setSortField($sortField)
            ->setSortOrder($sortOrder)
            ->setEmail($email)
            ->setEmployeeId($employeeId)
            ->setName($name)
            ->setgroupName($groupName)
            ->setUserStatus($status)
            ->setCreatedDate($createdDate)
            ->setModifiedDate($modifiedDate)
            ->setTeams($teams);

        $createdDate = '2022-07-20';
        $modifiedDate = '2022-07-29';

        /**
         * The response needs a body because listUsers() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by listUsers() is correct. The
         * processing of the response will be tested over the next few tests.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $users = $info->addChild('Users');
        $user = $users->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild(
            'Name', $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $info->addChild('TotalRecords', '1');
        $errors = $xml->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('ErrorID', 'Error 1');
        $error->addChild('ErrorMessage', 'Non-fatal Error');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->listUsers($query);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];
        
        self::assertIsArray($response);
        $user = $response[0];
        self::assertIsArray($user);
        self::assertArrayHasKey('ID', $user);
        self::assertEquals($this->user1->getId(), $user['ID']);
        self::assertArrayHasKey('Email', $user);
        self::assertEquals($this->user1->getEmail(), $user['Email']);
        self::assertArrayHasKey('EmployeeID', $user);
        self::assertEquals($this->user1->getEmployeeID(), $user['EmployeeID']);
        self::assertArrayHasKey('GivenName', $user);
        self::assertEquals($this->user1->getGivenName(), $user['GivenName']);
        self::assertArrayHasKey('Surname', $user);
        self::assertEquals($this->user1->getSurname(), $user['Surname']);
        self::assertArrayHasKey('Name', $user);
        self::assertEquals(
            $this->user1->getGivenName() . ' ' . $this->user1->getSurname(),
            $user['Name']
        );
        self::assertArrayHasKey('Status', $user);
        self::assertEquals($this->user1->getStatus(), $user['Status']);
        self::assertArrayHasKey('Title', $user);
        self::assertEquals($this->user1->getTitle(), $user['Title']);
        self::assertArrayHasKey('Division', $user);
        self::assertEquals($this->user1->getDivision(), $user['Division']);
        self::assertArrayHasKey('HomeGroup', $user);
        self::assertEquals($this->user1->getHomeGroup(), $user['HomeGroup']);
        self::assertArrayHasKey('CreatedDate', $user);
        self::assertEquals($createdDate, $user['CreatedDate']);
        self::assertArrayHasKey('ModifiedDate', $user);
        self::assertEquals($modifiedDate, $user['ModifiedDate']);
        self::assertArrayHasKey('Teams', $user);
        self::assertCount(count($this->user1->getTeams()), $user['Teams']);
        foreach ($this->user1->getTeams() as $team) {
            self::assertContains($team, $user['Teams']);
        }

        self::assertIsArray($errors);
        self::assertCount(1, $errors);
        self::assertArrayHasKey('Error 1', $errors);
        self::assertEquals('Non-fatal Error', $errors['Error 1']);
    }

    /**
     * Test that listUsers() returns the expected output when the SmarterU API
     * does not return any errors and the query only matches 1 User.
     */
    public function testListUsersReturnsExpectedResultSingleUser() {
        $sortField = 'NAME';
        $sortOrder = 'ASC';
        $email = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue($this->user1->getEmail());
        $employeeId = (new MatchTag())
            ->setMatchType('CONTAINS')
            ->setValue($this->user1->getEmployeeId());
        $name = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue(
                $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
            );
        $now = new DateTime();
        $time1 = new DateTime('2022-07-25');
        $time2 = new DateTime('2022-07-26');
        $time3 = new DateTime('2022-07-28');
        $createdDate = (new DateRangeTag())
            ->setDateFrom($time2)
            ->setDateTo($now);
        $modifiedDate = (new DateRangeTag())
            ->setDateFrom($time1)
            ->setDateTo($time3);
        $groupName = 'My Group';
        $status = 'ACTIVE';
        $teams = ['team1', 'team2'];

        $accountApi = 'account';
        $userApi = 'user';

        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new ListUsersQuery())
            ->setSortField($sortField)
            ->setSortOrder($sortOrder)
            ->setEmail($email)
            ->setEmployeeId($employeeId)
            ->setName($name)
            ->setgroupName($groupName)
            ->setUserStatus($status)
            ->setCreatedDate($createdDate)
            ->setModifiedDate($modifiedDate)
            ->setTeams($teams);

        $createdDate = '2022-07-20';
        $modifiedDate = '2022-07-29';

        /**
         * The response needs a body because listUsers() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by listUsers() is correct. The
         * processing of the response will be tested over the next few tests.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $users = $info->addChild('Users');
        $user = $users->addChild('User');
        $user->addChild('ID', $this->user1->getId());
        $user->addChild('Email', $this->user1->getEmail());
        $user->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user->addChild('GivenName', $this->user1->getGivenName());
        $user->addChild('Surname', $this->user1->getSurname());
        $user->addChild(
            'Name',
            $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
        );
        $user->addChild('Status', $this->user1->getStatus());
        $user->addChild('Title', $this->user1->getTitle());
        $user->addChild('Division', $this->user1->getDivision());
        $user->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user->addChild('CreatedDate', $createdDate);
        $user->addChild('ModifiedDate', $modifiedDate);
        $teams = $user->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams->addChild('Team', $team);
        }
        $info->addChild('TotalRecords', '1');
        $errors = $xml->addChild('Errors');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->listUsers($query);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];
        
        self::assertIsArray($response);
        self::assertCount(1, $response);
        $user = $response[0];
        self::assertIsArray($user);
        self::assertArrayHasKey('ID', $user);
        self::assertEquals($this->user1->getId(), $user['ID']);
        self::assertArrayHasKey('Email', $user);
        self::assertEquals($this->user1->getEmail(), $user['Email']);
        self::assertArrayHasKey('EmployeeID', $user);
        self::assertEquals($this->user1->getEmployeeID(), $user['EmployeeID']);
        self::assertArrayHasKey('GivenName', $user);
        self::assertEquals($this->user1->getGivenName(), $user['GivenName']);
        self::assertArrayHasKey('Surname', $user);
        self::assertEquals($this->user1->getSurname(), $user['Surname']);
        self::assertArrayHasKey('Name', $user);
        self::assertEquals(
            $this->user1->getGivenName() . ' ' . $this->user1->getSurname(),
            $user['Name']
        );
        self::assertArrayHasKey('Status', $user);
        self::assertEquals($this->user1->getStatus(), $user['Status']);
        self::assertArrayHasKey('Title', $user);
        self::assertEquals($this->user1->getTitle(), $user['Title']);
        self::assertArrayHasKey('Division', $user);
        self::assertEquals($this->user1->getDivision(), $user['Division']);
        self::assertArrayHasKey('HomeGroup', $user);
        self::assertEquals($this->user1->getHomeGroup(), $user['HomeGroup']);
        self::assertArrayHasKey('CreatedDate', $user);
        self::assertEquals($createdDate, $user['CreatedDate']);
        self::assertArrayHasKey('ModifiedDate', $user);
        self::assertEquals($modifiedDate, $user['ModifiedDate']);
        self::assertArrayHasKey('Teams', $user);
        self::assertCount(count($this->user1->getTeams()), $user['Teams']);
        foreach ($this->user1->getTeams() as $team) {
            self::assertContains($team, $user['Teams']);
        }

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }

    /**
     * Test that listUsers returns the expected output when the SmarterU API
     * does not return any errors and the query matches multiple Users.
     */
    public function testListUsersReturnsExpectedResultMultipleUsers() {
        $sortField = 'NAME';
        $sortOrder = 'ASC';
        $email = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue($this->user1->getEmail());
        $employeeId = (new MatchTag())
            ->setMatchType('CONTAINS')
            ->setValue($this->user1->getEmployeeId());
        $name = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue(
                $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
            );
        $now = new DateTime();
        $time1 = new DateTime('2022-07-25');
        $time2 = new DateTime('2022-07-26');
        $time3 = new DateTime('2022-07-28');
        $createdDate = (new DateRangeTag())
            ->setDateFrom($time2)
            ->setDateTo($now);
        $modifiedDate = (new DateRangeTag())
            ->setDateFrom($time1)
            ->setDateTo($time3);
        $groupName = 'My Group';
        $status = 'ACTIVE';
        $teams = ['team1', 'team2'];

        $accountApi = 'account';
        $userApi = 'user';

        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $query = (new ListUsersQuery())
            ->setSortField($sortField)
            ->setSortOrder($sortOrder)
            ->setEmail($email)
            ->setEmployeeId($employeeId)
            ->setName($name)
            ->setgroupName($groupName)
            ->setUserStatus($status)
            ->setCreatedDate($createdDate)
            ->setModifiedDate($modifiedDate)
            ->setTeams($teams);

        $createdDate = '2022-07-20';
        $modifiedDate = '2022-07-29';

        /**
         * The response needs a body because listUsers() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by listUsers() is correct. The
         * processing of the response will be tested over the next few tests.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $users = $info->addChild('Users');
        $user1 = $users->addChild('User');
        $user1->addChild('ID', $this->user1->getId());
        $user1->addChild('Email', $this->user1->getEmail());
        $user1->addChild('EmployeeID', $this->user1->getEmployeeId());
        $user1->addChild('GivenName', $this->user1->getGivenName());
        $user1->addChild('Surname', $this->user1->getSurname());
        $user1->addChild(
            'Name',
            $this->user1->getGivenName() . ' ' . $this->user1->getSurname()
        );
        $user1->addChild('Status', $this->user1->getStatus());
        $user1->addChild('Title', $this->user1->getTitle());
        $user1->addChild('Division', $this->user1->getDivision());
        $user1->addChild('HomeGroup', $this->user1->getHomeGroup());
        $user1->addChild('CreatedDate', $createdDate);
        $user1->addChild('ModifiedDate', $modifiedDate);
        $teams1 = $user1->addChild('Teams');
        foreach ($this->user1->getTeams() as $team) {
            $teams1->addChild('Team', $team);
        }
        $user2 = $users->addChild('User');
        $user2->addChild('ID', $this->user2->getId());
        $user2->addChild('Email', $this->user2->getEmail());
        $user2->addChild('EmployeeID', $this->user2->getEmployeeId());
        $user2->addChild('GivenName', $this->user2->getGivenName());
        $user2->addChild('Surname', $this->user2->getSurname());
        $user2->addChild(
            'Name',
            $this->user2->getGivenName() . ' ' . $this->user2->getSurname()
        );
        $user2->addChild('Status', $this->user2->getStatus());
        $user2->addChild('Title', $this->user2->getTitle());
        $user2->addChild('Division', $this->user2->getDivision());
        $user2->addChild('HomeGroup', $this->user2->getHomeGroup());
        $user2->addChild('CreatedDate', $createdDate);
        $user2->addChild('ModifiedDate', $modifiedDate);
        $teams2 = $user2->addChild('Teams');
        foreach ($this->user2->getTeams() as $team) {
            $teams2->addChild('Team', $team);
        }
        $user3 = $users->addChild('User');
        $user3->addChild('ID', $this->user3->getId());
        $user3->addChild('Email', $this->user3->getEmail());
        $user3->addChild('EmployeeID', $this->user3->getEmployeeId());
        $user3->addChild('GivenName', $this->user3->getGivenName());
        $user3->addChild('Surname', $this->user3->getSurname());
        $user3->addChild(
            'Name',
            $this->user3->getGivenName() . ' ' . $this->user3->getSurname()
        );
        $user3->addChild('Status', $this->user3->getStatus());
        $user3->addChild('Title', $this->user3->getTitle());
        $user3->addChild('Division', $this->user3->getDivision());
        $user3->addChild('HomeGroup', $this->user3->getHomeGroup());
        $user3->addChild('CreatedDate', $createdDate);
        $user3->addChild('ModifiedDate', $modifiedDate);
        $teams3 = $user3->addChild('Teams');
        foreach ($this->user3->getTeams() as $team) {
            $teams3->addChild('Team', $team);
        }
        $info->addChild('TotalRecords', '3');
        $errors = $xml->addChild('Errors');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->listUsers($query);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];
        
        self::assertIsArray($response);
        self::assertCount(3, $response);
        $user1 = $response[0];
        self::assertIsArray($user1);
        self::assertArrayHasKey('ID', $user1);
        self::assertEquals($this->user1->getId(), $user1['ID']);
        self::assertArrayHasKey('Email', $user1);
        self::assertEquals($this->user1->getEmail(), $user1['Email']);
        self::assertArrayHasKey('EmployeeID', $user1);
        self::assertEquals(
            $this->user1->getEmployeeID(),
            $user1['EmployeeID']
        );
        self::assertArrayHasKey('GivenName', $user1);
        self::assertEquals($this->user1->getGivenName(), $user1['GivenName']);
        self::assertArrayHasKey('Surname', $user1);
        self::assertEquals($this->user1->getSurname(), $user1['Surname']);
        self::assertArrayHasKey('Name', $user1);
        self::assertEquals(
            $this->user1->getGivenName() . ' ' . $this->user1->getSurname(),
            $user1['Name']
        );
        self::assertArrayHasKey('Status', $user1);
        self::assertEquals($this->user1->getStatus(), $user1['Status']);
        self::assertArrayHasKey('Title', $user1);
        self::assertEquals($this->user1->getTitle(), $user1['Title']);
        self::assertArrayHasKey('Division', $user1);
        self::assertEquals($this->user1->getDivision(), $user1['Division']);
        self::assertArrayHasKey('HomeGroup', $user1);
        self::assertEquals($this->user1->getHomeGroup(), $user1['HomeGroup']);
        self::assertArrayHasKey('CreatedDate', $user1);
        self::assertEquals($createdDate, $user1['CreatedDate']);
        self::assertArrayHasKey('ModifiedDate', $user1);
        self::assertEquals($modifiedDate, $user1['ModifiedDate']);
        self::assertArrayHasKey('Teams', $user1);
        self::assertCount(count($this->user1->getTeams()), $user1['Teams']);
        foreach ($this->user1->getTeams() as $team) {
            self::assertContains($team, $user1['Teams']);
        }

        $user2 = $response[1];
        self::assertIsArray($user2);
        self::assertArrayHasKey('ID', $user2);
        self::assertEquals($this->user2->getId(), $user2['ID']);
        self::assertArrayHasKey('Email', $user2);
        self::assertEquals($this->user2->getEmail(), $user2['Email']);
        self::assertArrayHasKey('EmployeeID', $user2);
        self::assertEquals(
            $this->user2->getEmployeeID(),
            $user2['EmployeeID']
        );
        self::assertArrayHasKey('GivenName', $user2);
        self::assertEquals($this->user2->getGivenName(), $user2['GivenName']);
        self::assertArrayHasKey('Surname', $user2);
        self::assertEquals($this->user2->getSurname(), $user2['Surname']);
        self::assertArrayHasKey('Name', $user2);
        self::assertEquals(
            $this->user2->getGivenName() . ' ' . $this->user2->getSurname(),
            $user2['Name']
        );
        self::assertArrayHasKey('Status', $user2);
        self::assertEquals($this->user1->getStatus(), $user2['Status']);
        self::assertArrayHasKey('Title', $user2);
        self::assertEquals($this->user1->getTitle(), $user2['Title']);
        self::assertArrayHasKey('Division', $user2);
        self::assertEquals($this->user1->getDivision(), $user2['Division']);
        self::assertArrayHasKey('HomeGroup', $user2);
        self::assertEquals($this->user1->getHomeGroup(), $user2['HomeGroup']);
        self::assertArrayHasKey('CreatedDate', $user2);
        self::assertEquals($createdDate, $user2['CreatedDate']);
        self::assertArrayHasKey('ModifiedDate', $user2);
        self::assertEquals($modifiedDate, $user2['ModifiedDate']);
        self::assertArrayHasKey('Teams', $user2);
        self::assertCount(count($this->user2->getTeams()), $user2['Teams']);
        foreach ($this->user2->getTeams() as $team) {
            self::assertContains($team, $user2['Teams']);
        }

        $user3 = $response[2];
        self::assertIsArray($user3);
        self::assertArrayHasKey('ID', $user3);
        self::assertEquals($this->user3->getId(), $user3['ID']);
        self::assertArrayHasKey('Email', $user3);
        self::assertEquals($this->user3->getEmail(), $user3['Email']);
        self::assertArrayHasKey('EmployeeID', $user3);
        self::assertEquals(
            $this->user3->getEmployeeID(),
            $user3['EmployeeID']
        );
        self::assertArrayHasKey('GivenName', $user3);
        self::assertEquals($this->user3->getGivenName(), $user3['GivenName']);
        self::assertArrayHasKey('Surname', $user3);
        self::assertEquals($this->user3->getSurname(), $user3['Surname']);
        self::assertArrayHasKey('Name', $user3);
        self::assertEquals(
            $this->user3->getGivenName() . ' ' . $this->user3->getSurname(),
            $user3['Name']
        );
        self::assertArrayHasKey('Status', $user3);
        self::assertEquals($this->user3->getStatus(), $user3['Status']);
        self::assertArrayHasKey('Title', $user3);
        self::assertEquals($this->user3->getTitle(), $user3['Title']);
        self::assertArrayHasKey('Division', $user3);
        self::assertEquals($this->user3->getDivision(), $user3['Division']);
        self::assertArrayHasKey('HomeGroup', $user3);
        self::assertEquals($this->user3->getHomeGroup(), $user3['HomeGroup']);
        self::assertArrayHasKey('CreatedDate', $user3);
        self::assertEquals($createdDate, $user3['CreatedDate']);
        self::assertArrayHasKey('ModifiedDate', $user3);
        self::assertEquals($modifiedDate, $user3['ModifiedDate']);
        self::assertArrayHasKey('Teams', $user3);
        self::assertCount(count($this->user3->getTeams()), $user3['Teams']);
        foreach ($this->user3->getTeams() as $team) {
            self::assertContains($team, $user3['Teams']);
        }

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }

    /**
     * Test that updateUser() throws an exception if the Account API Key is
     * not set prior to calling the method.
     */
    public function testUpdateUserThrowsExceptionWhenAccountAPIKeyNotSet() {
        $client = (new Client());
        
        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'Account API key must be set before creating a query.'
        );
        $client->updateUser($this->user1);
    }

    /**
     * Test that updateUser() throws an exception if the User API Key is not
     * set prior to calling the method.
     */
    public function testUpdateUserThrowsExceptionWhenUserAPIKeyNotSet() {
        $accountApi = 'account';
        $client = (new Client())
            ->setAccountApi($accountApi);

        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'User API key must be set before creating a query.'
        );
        $client->UpdateUser($this->user1);
    }

    /**
     * Test that updateUser() passes the correct information into the API
     * when making the request.
     */
    public function testUpdateUserMakesCorrectAPICall() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        /**
         * The response needs a body because updateUser() will try to process
         * the body once the response has been received, however this test is
         * about making sure the request made by updateUser() is correct. The
         * processing of the response will be tested further down.
         */
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $info->addChild('Email', 'test@test.com');
        $info->addChild('EmployeeID', '1');
        $xml->addChild('Errors');
        $body = $xml->asXML();

        // Set up the container to capture the request.
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
        
        // Make the request.
        $client->updateUser($this->user1);

        // Make sure there is only 1 request, then translate it to XML.
        self::assertCount(1, $container);
        $package = $container[0]['options']['package'];
        $packageAsXml = simplexml_load_string($package);

        // Ensure that the package begins with a <SmarterU> tag and has the
        // correct children.
        self::assertEquals($packageAsXml->getName(), 'SmarterU');
        $elements = [];
        foreach ($packageAsXml->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $packageAsXml->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $packageAsXml->UserAPI);
        self::assertContains('Method', $elements);
        self::assertEquals('updateUser', $packageAsXml->Method);
        self::assertContains('Parameters', $elements);

        // Ensure that the <Parameters> tag has the correct children.
        $parameters = [];
        foreach ($packageAsXml->Parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        $userInfo = [];
        foreach ($packageAsXml->Parameters->User->children() as $user) {
            $userInfo[] = $user->getName();
        }
        self::assertCount(5, $userInfo);
        self::assertContains('Info', $userInfo);
        self::assertContains('Profile', $userInfo);
        self::assertContains('Groups', $userInfo);
        self::assertContains('Venues', $userInfo);
        self::assertContains('Wages', $userInfo);

        // Ensure that the <Info> tag has the correct children.
        $infoTag = [];
        foreach ($packageAsXml->Parameters->User->Info->children() as $info) {
            $infoTag[] = $info->getName();
        }
        self::assertCount(11, $infoTag);
        self::assertContains('Email', $infoTag);
        self::assertEquals(
            $this->user1->getEmail(),
            $packageAsXml->Parameters->User->Info->Email
        );
        self::assertContains('EmployeeID', $infoTag);
        self::assertEquals(
            $this->user1->getEmployeeId(),
            $packageAsXml->Parameters->User->Info->EmployeeID
        );
        self::assertContains('GivenName', $infoTag);
        self::assertEquals(
            $this->user1->getGivenName(),
            $packageAsXml->Parameters->User->Info->GivenName
        );
        self::assertContains('Surname', $infoTag);
        self::assertEquals(
            $this->user1->getSurname(),
            $packageAsXml->Parameters->User->Info->Surname
        );
        self::assertContains('Password', $infoTag);
        self::assertEquals(
            $this->user1->getPassword(),
            $packageAsXml->Parameters->User->Info->Password
        );
        self::assertContains('Timezone', $infoTag);
        self::assertEquals(
            $this->user1->getTimezone(),
            $packageAsXml->Parameters->User->Info->Timezone
        );
        self::assertContains('LearnerNotifications', $infoTag);
        self::assertEquals(
            (string) $this->user1->getLearnerNotifications(),
            $packageAsXml->Parameters->User->Info->LearnerNotifications
        );
        self::assertContains('SupervisorNotifications', $infoTag);
        self::assertEquals(
            (string) $this->user1->getSupervisorNotifications(),
            $packageAsXml->Parameters->User->Info->SupervisorNotifications
        );
        self::assertContains('SendEmailTo', $infoTag);
        self::assertEquals(
            $this->user1->getSendEmailTo(),
            $packageAsXml->Parameters->User->Info->SendEmailTo
        );
        self::assertContains('AlternateEmail', $infoTag);
        self::assertEquals(
            $this->user1->getAlternateEmail(),
            $packageAsXml->Parameters->User->Info->AlternateEmail
        );
        self::assertContains('AuthenticationType', $infoTag);
        self::assertEquals(
            $this->user1->getAuthenticationType(),
            $packageAsXml->Parameters->User->Info->AuthenticationType
        );

        // Ensure that the <Profile> tag has the correct children.
        $profileTag = [];
        foreach ($packageAsXml->Parameters->User->Profile->children() as $profile) {
            $profileTag[] = $profile->getName();
        }
        self::assertCount(22, $profileTag);
        self::assertContains('Supervisors', $profileTag);
        $supervisors = $packageAsXml->Parameters->User->Profile->Supervisors->asXML();
        $supervisor1 =
            '<Supervisors><Supervisor>'
            . $this->user1->getSupervisors()[0]
            . '</Supervisor>';
        $supervisor2 =
            '<Supervisor>'
            . $this->user1->getSupervisors()[1]
            . '</Supervisor></Supervisors>';
        self::assertStringContainsString($supervisor1, $supervisors);
        self::assertStringContainsString($supervisor2, $supervisors);
        self::assertContains('Organization', $profileTag);
        self::assertEquals(
            $this->user1->getOrganization(),
            $packageAsXml->Parameters->User->Profile->Organization
        );
        self::assertContains('Teams', $profileTag);
        $teams = $packageAsXml->Parameters->User->Profile->Teams->asXML();
        $team1 = '<Teams><Team>' . $this->user1->getTeams()[0] . '</Team>';
        $team2 = '<Team>' . $this->user1->getTeams()[1] . '</Team></Teams>';
        self::assertStringContainsString($team1, $teams);
        self::assertStringContainsString($team2, $teams);
        self::assertContains('Language', $profileTag);
        self::assertEquals(
            $this->user1->getLanguage(),
            $packageAsXml->Parameters->User->Profile->Language
        );
        self::assertContains('Status', $profileTag);
        self::assertEquals(
            $this->user1->getStatus(),
            $packageAsXml->Parameters->User->Profile->Status
        );
        self::assertContains('Title', $profileTag);
        self::assertEquals(
            $this->user1->getTitle(),
            $packageAsXml->Parameters->User->Profile->Title
        );
        self::assertContains('Division', $profileTag);
        self::assertEquals(
            $this->user1->getDivision(),
            $packageAsXml->Parameters->User->Profile->Division
        );
        self::assertContains('AllowFeedback', $profileTag);
        self::assertEquals(
            (string) $this->user1->getAllowFeedback(),
            $packageAsXml->Parameters->User->Profile->AllowFeedback
        );
        self::assertContains('PhonePrimary', $profileTag);
        self::assertEquals(
            $this->user1->getPhonePrimary(),
            $packageAsXml->Parameters->User->Profile->PhonePrimary
        );
        self::assertContains('PhoneAlternate', $profileTag);
        self::assertEquals(
            $this->user1->getPhoneAlternate(),
            $packageAsXml->Parameters->User->Profile->PhoneAlternate
        );
        self::assertContains('PhoneMobile', $profileTag);
        self::assertEquals(
            $this->user1->getPhoneMobile(),
            $packageAsXml->Parameters->User->Profile->PhoneMobile
        );
        self::assertContains('Fax', $profileTag);
        self::assertEquals(
            $this->user1->getFax(),
            $packageAsXml->Parameters->User->Profile->Fax
        );
        self::assertContains('Website', $profileTag);
        self::assertEquals(
            $this->user1->getWebsite(),
            $packageAsXml->Parameters->User->Profile->Website
        );
        self::assertContains('Address1', $profileTag);
        self::assertEquals(
            $this->user1->getAddress1(),
            $packageAsXml->Parameters->User->Profile->Address1
        );
        self::assertContains('Address2', $profileTag);
        self::assertEquals(
            $this->user1->getAddress2(),
            $packageAsXml->Parameters->User->Profile->Address2
        );
        self::assertContains('City', $profileTag);
        self::assertEquals(
            $this->user1->getCity(),
            $packageAsXml->Parameters->User->Profile->City
        );
        self::assertContains('Province', $profileTag);
        self::assertEquals(
            $this->user1->getProvince(),
            $packageAsXml->Parameters->User->Profile->Province
        );
        self::assertContains('Country', $profileTag);
        self::assertEquals(
            $this->user1->getCountry(),
            $packageAsXml->Parameters->User->Profile->Country
        );
        self::assertContains('PostalCode', $profileTag);
        self::assertEquals(
            $this->user1->getPostalCode(),
            $packageAsXml->Parameters->User->Profile->PostalCode
        );
        self::assertContains('SendMailTo', $profileTag);
        self::assertEquals(
            $this->user1->getSendMailTo(),
            $packageAsXml->Parameters->User->Profile->SendMailTo
        );
        self::assertContains('ReceiveNotifications', $profileTag);
        self::assertEquals(
            (string) $this->user1->getReceiveNotifications(),
            $packageAsXml->Parameters->User->Profile->ReceiveNotifications
        );
        self::assertContains('HomeGroup', $profileTag);
        self::assertEquals(
            $this->user1->getHomeGroup(),
            $packageAsXml->Parameters->User->Profile->HomeGroup
        );

        // Ensure that the <Groups> tag has the correct children.
        $group1 = $packageAsXml->Parameters->User->Groups->Group[0];
        $group2 = $packageAsXml->Parameters->User->Groups->Group[1];
        $group1Elements = [];
        foreach ($group1->children() as $group) {
            $group1Elements[] = $group->getName();
        }
        self::assertCount(3, $group1Elements);
        self::assertContains('GroupName', $group1Elements);
        self::assertEquals(
            $this->user1->getGroups()[0]->getGroupName(),
            $group1->GroupName
        );
        self::assertContains('Permission', $group1Elements);
        $permission1 = $group1->Permission[0];
        $permission2 = $group1->Permission[1];
        $permission1Tags = [];
        foreach ($permission1->children() as $tag) {
            $permission1Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission1Tags);
        self::assertContains('Action', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[0]->getAction(),
            $group1->Permission[0]->Action
        );
        self::assertContains('Code', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[0]->getCode(),
            $group1->Permission[0]->Code
        );
        $permission2Tags = [];
        foreach ($permission2->children() as $tag) {
            $permission2Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission2Tags);
        self::assertContains('Action', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[1]->getAction(),
            $group1->Permission[1]->Action
        );
        self::assertContains('Code', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[0]->getPermissions()[1]->getCode(),
            $group1->Permission[1]->Code
        );

        $group2Elements = [];
        foreach ($group2->children() as $group) {
            $group2Elements[] = $group->getName();
        }
        self::assertCount(3, $group2Elements);
        self::assertContains('GroupName', $group2Elements);
        self::assertEquals(
            $this->user1->getGroups()[1]->getGroupName(),
            $group2->GroupName
        );
        self::assertContains('Permission', $group2Elements);
        $permission1 = $group2->Permission[0];
        $permission2 = $group2->Permission[1];
        $permission1Tags = [];
        foreach ($permission1->children() as $tag) {
            $permission1Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission1Tags);
        self::assertContains('Action', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[0]->getAction(),
            $group1->Permission[0]->Action
        );
        self::assertContains('Code', $permission1Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[0]->getCode(),
            $group1->Permission[0]->Code
        );
        $permission2Tags = [];
        foreach ($permission2->children() as $tag) {
            $permission2Tags[] = $tag->getName();
        }
        self::assertCount(2, $permission2Tags);
        self::assertContains('Action', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[1]->getAction(),
            $group1->Permission[1]->Action
        );
        self::assertContains('Code', $permission2Tags);
        self::assertEquals(
            $this->user1->getGroups()[1]->getPermissions()[1]->getCode(),
            $group1->Permission[1]->Code
        );

        // Ensure that the <Venues> and <Wages> tags are empty.
        self::assertCount(
            0,
            $packageAsXml->Parameters->User->Venues->children()
        );
        self::assertCount(
            0,
            $packageAsXml->Parameters->User->Wages->Children()
        );
    }

    /**
     * Test that updateUser() throws an exception when the request results
     * in an HTTP error.
     */
    public function testUpdateUserThrowsExceptionWhenHTTPErrorOccurs() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $response = new Response(404);

        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(HttpException::class);
        self::expectExceptionMessage('Client error: ');
        $client->updateUser($this->user1);
    }

    /**
     * Test that updateUser() throws an exception when the SmarterU API
     * returns a fatal error, as indicated by the value of the <Result>
     * tag.
     */
    public function testUpdateUserThrowsExceptionWhenFatalErrorReturned() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
    
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Failed');
        $xml->addChild('Info');
        $errors = $xml->addChild('Errors');
        $error1 = $errors->addChild('Error');
        $error1->addChild('ErrorID', 'Error1');
        $error1->addChild('ErrorMessage', 'Testing');
        $error2 = $errors->addChild('Error');
        $error2->addChild('ErrorID', 'Error2');
        $error2->addChild('ErrorMessage', '123');
        $body = $xml->asXML();

        $response = new Response(200, [], $body);
    
        $container = [];
        $history = Middleware::history($container);

        $mock = (new MockHandler([$response]));
            
        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push($history);
            
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client->setHttpClient($httpClient);

        self::expectException(SmarterUException::class);
        self::expectExceptionMessage('Error1: Testing, Error2: 123');
        $client->updateUser($this->user1);
    }

    /**
     * Test that updateUser() returns the expected output when the SmarterU API
     * returns a non-fatal error.
     */
    public function testUpdateUserHandlesNonFatalError() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $info->addChild('Email', $this->user1->getEmail());
        $info->addChild('EmployeeID', $this->user1->getEmployeeId());
        $errors = $xml->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('ErrorID', 'Error 1');
        $error->addChild('ErrorMessage', 'Non-fatal Error');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->updateUser($this->user1);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];

        self::assertIsArray($response);
        self::assertCount(2, $response);
        self::assertArrayHasKey('Email', $response);
        self::assertEquals($response['Email'], $this->user1->getEmail());
        self::assertArrayHasKey('EmployeeID', $response);
        self::assertEquals(
            $response['EmployeeID'],
            $this->user1->getEmployeeId()
        );

        self::assertIsArray($errors);
        self::assertCount(1, $errors);
        self::assertArrayHasKey('Error 1', $errors);
        self::assertEquals($errors['Error 1'], 'Non-fatal Error');
    }

    /**
     * Test that updateUser() returns the expected output when the SmarterU API
     * does not return any errors.
     */
    public function testUpdateUserReturnsExpectedResult() {
        $accountApi = 'account';
        $userApi = 'user';
        $client = (new Client())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;
        $xml = simplexml_load_string($xmlString);
        $xml->addChild('Result', 'Success');
        $info = $xml->addChild('Info');
        $info->addChild('Email', $this->user1->getEmail());
        $info->addChild('EmployeeID', $this->user1->getEmployeeId());
        $xml->addChild('Errors');
        $body = $xml->asXML();
    
        $response = new Response(200, [], $body);
        $container = [];
        $history = Middleware::history($container);
        $mock = (new MockHandler([$response]));
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client->setHttpClient($httpClient);
            
        // Make the request.
        $result = $client->updateUser($this->user1);
        
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('Response', $result);
        self::assertArrayHasKey('Errors', $result);

        $response = $result['Response'];
        $errors = $result['Errors'];

        self::assertIsArray($response);
        self::assertCount(2, $response);
        self::assertArrayHasKey('Email', $response);
        self::assertEquals($response['Email'], $this->user1->getEmail());
        self::assertArrayHasKey('EmployeeID', $response);
        self::assertEquals(
            $response['EmployeeID'],
            $this->user1->getEmployeeId()
        );

        self::assertIsArray($errors);
        self::assertCount(0, $errors);
    }
}
