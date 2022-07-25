<?php

/**
 * Contains Tests\SmarterU\Queries\GetUserQueryTest
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
use CBS\SmarterU\Queries\GetUserQuery;
use PHPUnit\Framework\TestCase;

/**
 * Tests SmarterU\Queries\GetUserQuery;
 */
class GetUserQueryTest extends TestCase {
    /**
     * Tests agreement between getters and setters.
     */
    public function testAgreement() {
        $accountApi = 'account';
        $userApi = 'user';
        $id = '12';
        $email = 'test@phpunit.com';
        $employeeId = '13';
        $query = (new GetUserQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi)
            ->setId($id);

        self::assertEquals($accountApi, $query->getAccountApi());
        self::assertEquals($userApi, $query->getUserApi());
        self::assertEquals($id, $query->getId());
        self::assertNull($query->getEmail());
        self::assertNull($query->getEmployeeId());

        $query->setEmail($email);
        self::assertEquals($email, $query->getEmail());
        self::assertNull($query->getId());
        self::assertNull($query->getEmployeeId());

        $query->setEmployeeId($employeeId);
        self::assertEquals($employeeId, $query->getEmployeeId());
        self::assertNull($query->getId());
        self::assertNull($query->getEmail());
    }

    /**
     * Tests that XML generation throws the expected exception when the
     * account API key is not set.
     */
    public function testExceptionIsThrownWhenAccountAPIKeyNotSet() {
        $query = new GetUserQuery();

        self::expectException(MissingValueException::class);
        self::expectExceptionMessage('Account API key must be set before creating a query.');
        $xml = $query->toXml();
    }

    /**
     * Tests that XML generation throws the expected exception when the
     * user API key is not set.
     */
    public function testExceptionIsThrownWhenUserAPIKeyNotSet() {
        $accountApi = 'account';
        $query = (new GetUserQuery())
            ->setAccountApi($accountApi);

        self::expectException(MissingValueException::class);
        self::expectExceptionMessage('User API key must be set before creating a query.');
        $xml = $query->toXml();
    }

    /**
     * Tests that XML generation throws the expected exception when the
     * required user identifier is not set.
     */
    public function testExceptionIsThrownWhenUserIdentifierNotSet() {
        $accountApi = 'account';
        $userApi = 'user';
        $query = (new GetUserQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi);
        
        self::expectException(MissingValueException::class);
        self::expectExceptionMessage('GetUserQuery must have a parameter');
        $xml = $query->toXml();
    }

    /**
     * Tests that XML generation produces the expected result when all required
     * information is present and the user is identified by their ID.
     */
    public function testXMLGeneratedAsExpectedWithId() {
        $accountApi = 'account';
        $userApi = 'user';
        $id = '12';
        $query = (new GetUserQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi)
            ->setId($id);

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
        self::assertEquals('getUser', $xmlAsElement->method);
        self::assertContains('parameters', $elements);
        $parameters = [];
        foreach ($xmlAsElement->parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        self::assertEquals('User', $xmlAsElement->parameters->User->getName());

        $users = [];
        foreach($xmlAsElement->parameters->User->children() as $user) {
            $users[] = $user->getName();
        }
        self::assertCount(1, $users);
        self::assertContains('ID', $users);
        self::assertEquals($id, $xmlAsElement->parameters->User->ID);
    }

    /**
     * Tests that XML generation produces the expected result when all required
     * information is present and the user is identified by their email.
     */
    public function testXMLGeneratedAsExpectedWithEmail() {
        $accountApi = 'account';
        $userApi = 'user';
        $email = 'phpunit@test.com';
        $query = (new GetUserQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi)
            ->setEmail($email);

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
        self::assertEquals('getUser', $xmlAsElement->method);
        self::assertContains('parameters', $elements);
        $parameters = [];
        foreach ($xmlAsElement->parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        self::assertEquals('User', $xmlAsElement->parameters->User->getName());

        $users = [];
        foreach($xmlAsElement->parameters->User->children() as $user) {
            $users[] = $user->getName();
        }
        self::assertCount(1, $users);
        self::assertContains('Email', $users);
        self::assertEquals($email, $xmlAsElement->parameters->User->Email);
    }

    /**
     * Tests that XML generation produces the expected result when all required
     * information is present and the user is identified by their employee ID.
     */
    public function testXMLGeneratedAsExpectedWithEmployeeId() {
        $accountApi = 'account';
        $userApi = 'user';
        $employeeId = '12';
        $query = (new GetUserQuery())
            ->setAccountApi($accountApi)
            ->setUserApi($userApi)
            ->setEmployeeId($employeeId);

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
        self::assertEquals('getUser', $xmlAsElement->method);
        self::assertContains('parameters', $elements);
        $parameters = [];
        foreach ($xmlAsElement->parameters->children() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        self::assertCount(1, $parameters);
        self::assertContains('User', $parameters);
        self::assertEquals('User', $xmlAsElement->parameters->User->getName());

        $users = [];
        foreach($xmlAsElement->parameters->User->children() as $user) {
            $users[] = $user->getName();
        }
        self::assertCount(1, $users);
        self::assertContains('EmployeeID', $users);
        self::assertEquals($employeeId, $xmlAsElement->parameters->User->EmployeeID);
    }
}
