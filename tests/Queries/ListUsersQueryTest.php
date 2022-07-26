<?php

/**
 * Contains Tests\SmarterU\Queries\ListUsersQueryTest
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     ??
 * @version     $version$
 * @since       2022/07/25
 */

declare(strict_types=1);

namespace Tests\SmarterU\Queries;

use CBS\SmarterU\Exceptions\MissingValueException;
use CBS\SmarterU\Queries\ListUsersQuery;
use CBS\SmarterU\Queries\Tags\DateRangeTag;
use CBS\SmarterU\Queries\Tags\MatchTag;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Tests SmarterU\Queries\ListUsersQuery;
 */
class ListUsersQueryTest extends TestCase {
    /**
     * Tests agreement between getters and setters.
     */
    public function testAgreement() {
        $accountApi = 'account';
        $userApi = 'user';
        $page = 1;
        $pageSize = 50;
        $sortField = 'NAME';
        $sortOrder = 'ASC';
        $email = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue('phpunit@test.com');
        $employeeId = (new MatchTag())
            ->setMatchType('CONTAINS')
            ->setValue('2');
        $name = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue('Test User');
        $groupName = 'Test Group';
        $userStatus = 'Active';
        $createdDateFrom = new DateTime('2022-07-25');
        $createdDateTo = new DateTime();
        $modifiedDateFrom = new DateTime('2022-07-20');
        $modifiedDateTo = new DateTime('2022-07-24');
        $createdDate = (new DateRangeTag())
            ->setDateFrom($createdDateFrom)
            ->setDateTo($createdDateTo);
        $modifiedDate = (new DateRangeTag())
            ->setDateFrom($modifiedDateFrom)
            ->setDateTo($modifiedDateTo);
        $team1 = 'team1';
        $team2 = 'team2';
        $teams = [$team1, $team2];

        $query = (new ListUsersQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi)
            ->setPage($page)
            ->setPageSize($pageSize)
            ->setSortField($sortField)
            ->setSortOrder($sortOrder)
            ->setEmail($email)
            ->setEmployeeId($employeeId)
            ->setName($name)
            ->setGroupName($groupName)
            ->setUserStatus($userStatus)
            ->setCreatedDate($createdDate)
            ->setModifiedDate($modifiedDate)
            ->setTeams($teams);
        
        self::assertEquals($accountApi, $query->getAccountApi());
        self::assertEquals($userApi, $query->getUserApi());
        self::assertEquals($page, $query->getPage());
        self::assertEquals($pageSize, $query->getPageSize());
        self::assertEquals($sortField, $query->getSortField());
        self::assertEquals($sortOrder, $query->getSortOrder());
        self::assertInstanceOf(MatchTag::class, $query->getEmail());
        self::assertEquals(
            $email->getMatchType(),
            $query->getEmail()->getMatchType()
        );
        self::assertEquals(
            $email->getValue(),
            $query->getEmail()->getValue()
        );
        self::assertInstanceOf(MatchTag::class, $query->getEmployeeId());
        self::assertEquals(
            $employeeId->getMatchType(),
            $query->getEmployeeId()->getMatchType()
        );
        self::assertEquals(
            $employeeId->getValue(),
            $query->getEmployeeId()->getValue()
        );
        self::assertInstanceOf(MatchTag::class, $query->getName());
        self::assertEquals(
            $name->getMatchType(),
            $query->getName()->getMatchType()
        );
        self::assertEquals(
            $name->getValue(),
            $query->getName()->getValue()
        );
        self::assertEquals($groupName, $query->getGroupName());
        self::assertEquals($userStatus, $query->getUserStatus());
        self::assertInstanceOf(DateRangeTag::class, $query->getCreatedDate());
        self::assertEquals(
            $createdDateFrom,
            $query->getCreatedDate()->getDateFrom()
        );
        self::assertEquals(
            $createdDateTo,
            $query->getCreatedDate()->getDateTo()
        );
        self::assertInstanceOf(DateRangeTag::class, $query->getModifiedDate());
        self::assertEquals(
            $modifiedDateFrom,
            $query->getModifiedDate()->getDateFrom()
        );
        self::assertEquals(
            $modifiedDateTo,
            $query->getModifiedDate()->getDateTo()
        );
        self::assertCount(2, $query->getTeams());
        self::assertContains($team1, $query->getTeams());
        self::assertContains($team2, $query->getTeams());
    }

    /**
     * Tests that XML generation throws the expected exception when the
     * account API key is not set.
     */
    public function testExceptionIsThrownWhenAccountAPIKeyNotSet() {
        $query = new ListUsersQuery();

        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'Account API key must be set before creating a query.'
        );
        $xml = $query->toXml();
    }

    /**
     * Tests that XML generation throws the expected exception when the
     * user API key is not set.
     */
    public function testExceptionIsThrownWhenUserAPIKeyNotSet() {
        $accountApi = 'account';
        $query = (new ListUsersQuery())
            ->setAccountApi($accountApi);

        self::expectException(MissingValueException::class);
        self::expectExceptionMessage(
            'User API key must be set before creating a query.'
        );
        $xml = $query->toXml();
    }

    /**
     * Tests that XML generation produces the expected result when all
     * required and optional information is present.
     */
    public function testEmittedXMLIsAsExpectedWithAllInfo() {
        $accountApi = 'account';
        $userApi = 'user';
        $page = 1;
        $pageSize = 50;
        $sortField = 'NAME';
        $sortOrder = 'ASC';
        $email = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue('phpunit@test.com');
        $employeeId = (new MatchTag())
            ->setMatchType('CONTAINS')
            ->setValue('2');
        $name = (new MatchTag())
            ->setMatchType('EXACT')
            ->setValue('Test User');
        $groupName = 'Test Group';
        $userStatus = 'Active';
        $createdDateFrom = new DateTime('2022-07-25');
        $createdDateTo = new DateTime();
        $modifiedDateFrom = new DateTime('2022-07-20');
        $modifiedDateTo = new DateTime('2022-07-24');
        $createdDate = (new DateRangeTag())
            ->setDateFrom($createdDateFrom)
            ->setDateTo($createdDateTo);
        $modifiedDate = (new DateRangeTag())
            ->setDateFrom($modifiedDateFrom)
            ->setDateTo($modifiedDateTo);
        $team1 = 'team1';
        $team2 = 'team2';
        $teams = [$team1, $team2];

        $query = (new ListUsersQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi)
            ->setPage($page)
            ->setPageSize($pageSize)
            ->setSortField($sortField)
            ->setSortOrder($sortOrder)
            ->setEmail($email)
            ->setEmployeeId($employeeId)
            ->setName($name)
            ->setGroupName($groupName)
            ->setUserStatus($userStatus)
            ->setCreatedDate($createdDate)
            ->setModifiedDate($modifiedDate)
            ->setTeams($teams);

        $xml = $query->toXml();
        self::assertIsString($xml);
        $xmlAsElement = simplexml_load_string($xml);
        self::assertEquals('SmarterU', $xmlAsElement->getName());
        self::assertCount(4, $xmlAsElement);
        $elements = [];
        foreach ($xmlAsElement->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $xmlAsElement->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $xmlAsElement->UserAPI);
        self::assertContains('method', $elements);
        self::assertEquals('listUsers', $xmlAsElement->method);
        self::assertContains('parameters', $elements);
        $parameters = [];
        foreach ($xmlAsElement->parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        $userInfo = [];
        foreach($xmlAsElement->parameters->User->children() as $user) {
            $userInfo[] = $user->getName();
        }
        self::assertCount(5, $userInfo);
        self::assertContains('Page', $userInfo);
        self::assertEquals(
            $page,
            (int) $xmlAsElement->parameters->User->Page
        );
        self::assertContains('PageSize', $userInfo);
        self::assertEquals(
            $pageSize,
            (int) $xmlAsElement->parameters->User->PageSize
        );
        self::assertContains('SortField', $userInfo);
        self::assertEquals(
            $sortField,
            $xmlAsElement->parameters->User->SortField
        );
        self::assertContains('SortOrder', $userInfo);
        self::assertEquals(
            $sortOrder,
            $xmlAsElement->parameters->User->SortOrder
        );
        self::assertContains('Filters', $userInfo);
        $filters = [];
        foreach ($xmlAsElement->parameters->User->Filters->children() as $filter) {
            $filters[] = $filter->getName();
        }
        self::assertCount(6, $filters);
        self::assertContains('Users', $filters);
        self::assertContains('GroupName', $filters);
        self::assertContains('UserStatus', $filters);
        self::assertContains('CreatedDate', $filters);
        self::assertContains('ModifiedDate', $filters);
        self::assertContains('Teams', $filters);
        $users = [];
        foreach ($xmlAsElement->parameters->User->Filters->Users->children() as $user) {
            $users[] = $user->getName();
        }
        self::assertCount(1, $users);
        self::assertContains('UserIdentifier', $users);
        $userIdentifiers = [];
        foreach($xmlAsElement->parameters->User->Filters->Users->UserIdentifier->children() as $identifier) {
            $userIdentifiers[] = $identifier->getName();
        }
        self::assertCount(3, $userIdentifiers);
        self::assertContains('Email', $userIdentifiers);
        self::assertEquals(
            $email->getMatchType(),
            $xmlAsElement->parameters->User->Filters->Users->UserIdentifier->Email->MatchType
        );
        self::assertEquals(
            $email->getValue(),
            $xmlAsElement->parameters->User->Filters->Users->UserIdentifier->Email->Value
        );
        self::assertContains('EmployeeID', $userIdentifiers);
        self::assertEquals(
            $employeeId->getMatchType(),
            $xmlAsElement->parameters->User->Filters->Users->UserIdentifier->EmployeeID->MatchType
        );
        self::assertEquals(
            $employeeId->getValue(),
            $xmlAsElement->parameters->User->Filters->Users->UserIdentifier->EmployeeID->Value
        );
        self::assertContains('Name', $userIdentifiers);
        self::assertEquals(
            $name->getMatchType(),
            $xmlAsElement->parameters->User->Filters->Users->UserIdentifier->Name->MatchType
        );
        self::assertEquals(
            $name->getValue(),
            $xmlAsElement->parameters->User->Filters->Users->UserIdentifier->Name->Value
        );
        self::assertEquals(
            $groupName,
            $xmlAsElement->parameters->User->Filters->GroupName
        );
        self::assertEquals(
            $userStatus,
            $xmlAsElement->parameters->User->Filters->UserStatus
        );
        $createdDateTag = [];
        foreach ($xmlAsElement->parameters->User->Filters->CreatedDate->children() as $date) {
            $createdDateTag[] = $date->getName();
        }
        self::assertCount(2, $createdDateTag);
        self::assertContains('CreatedDateFrom', $createdDateTag);
        self::assertContains('CreatedDateTo', $createdDateTag);
        self::assertEquals(
            $createdDate->getDateFrom()->format('d/m/Y'),
            $xmlAsElement->parameters->User->Filters->CreatedDate->CreatedDateFrom
        );
        self::assertEquals(
            $createdDate->getDateTo()->format('d/m/Y'),
            $xmlAsElement->parameters->User->Filters->CreatedDate->CreatedDateTo
        );
        $modifiedDateTag = [];
        foreach ($xmlAsElement->parameters->User->Filters->ModifiedDate->children() as $date) {
            $modifiedDateTag[] = $date->getName();
        };
        self::assertCount(2, $modifiedDateTag);
        self::assertContains('ModifiedDateFrom', $modifiedDateTag);
        self::assertContains('ModifiedDateTo', $modifiedDateTag);
        self::assertEquals(
            $modifiedDate->getDateFrom()->format('d/m/Y'),
            $xmlAsElement->parameters->User->Filters->ModifiedDate->ModifiedDateFrom
        );
        self::assertEquals(
            $modifiedDate->getDateTo()->format('d/m/Y'),
            $xmlAsElement->parameters->User->Filters->ModifiedDate->ModifiedDateTo
        );
        $teamName = [];
        foreach ($xmlAsElement->parameters->User->Filters->Teams->children() as $team) {
            $teamName[] = $team->getName();
        }
        self::assertCount(2, $teamName);
        self::assertContains('TeamName', $teamName);
        $teamNames = [];
        foreach ($xmlAsElement->parameters->User->Filters->Teams->TeamName as $team) {
            $teamNames[] = $team;
        }
        self::assertEquals($team1, $teamNames[0]);
        self::assertEquals($team2, $teamNames[1]);
    }

    /**
     * Tests that XML generation produces the expected result when all
     * required information but no optional information is present.
     */
    public function testEmittedXMLIsAsExpectedWithoutOptionalInfo() {
        $accountApi = 'account';
        $userApi = 'user';
        $query = (new ListUsersQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);

        $xml = $query->toXml();
        self::assertIsString($xml);
        $xmlAsElement = simplexml_load_string($xml);
        self::assertEquals('SmarterU', $xmlAsElement->getName());
        self::assertCount(4, $xmlAsElement);
        $elements = [];
        foreach ($xmlAsElement->children() as $element) {
            $elements[] = $element->getName();
        }
        self::assertContains('AccountAPI', $elements);
        self::assertEquals($accountApi, $xmlAsElement->AccountAPI);
        self::assertContains('UserAPI', $elements);
        self::assertEquals($userApi, $xmlAsElement->UserAPI);
        self::assertContains('method', $elements);
        self::assertEquals('listUsers', $xmlAsElement->method);
        self::assertContains('parameters', $elements);
        $parameters = [];
        foreach ($xmlAsElement->parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        $userInfo = [];
        foreach($xmlAsElement->parameters->User->children() as $user) {
            $userInfo[] = $user->getName();
        }
        self::assertCount(3, $userInfo);
        self::assertContains('Page', $userInfo);
        self::assertEquals(
            $query->getPage(),
            (int) $xmlAsElement->parameters->User->Page
        );
        self::assertContains('PageSize', $userInfo);
        self::assertEquals(
            $query->getPageSize(),
            (int) $xmlAsElement->parameters->User->PageSize
        );
        self::assertContains('Filters', $userInfo);
        $filters = [];
        foreach ($xmlAsElement->parameters->User->Filters->children() as $filter) {
            $filters[] = $filter->getName();
        }
        self::assertCount(1, $filters);
        self::assertContains('UserStatus', $filters);
        self::assertEquals(
            $query->getUserStatus(),
            $xmlAsElement->parameters->User->Filters->UserStatus
        );
    }
}
