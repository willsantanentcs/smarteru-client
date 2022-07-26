<?php

/**
 * Contains SmarterU\Queries\BaseQuery.php
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     MIT
 * @version     $version$
 * @since       2022/07/14
 */

declare(strict_types=1);

namespace CBS\SmarterU\Queries;

use CBS\SmarterU\Exceptions\MissingValueException;
use SimpleXMLElement;

/**
 * Creates an XML representation of the elements that are universal to every
 * SmarterU API query.
 */
abstract class BaseQuery {
    /**
     * The account API key.
     */
    protected string $accountApi;

    /**
     * The user API key.
     */
    protected string $userApi;

    /**
     * Return the account API key.
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
     * Return the user API key.
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
     * Create the base XML element containing the features that are common to
     * all queries.
     *
     * @return SimpleXMLElement The base XML element from which to build the query
     * @throws MissingValueException if the Account API Key and/or the User API
     *      Key are not set.
     */
    public function createBaseXml(): SimpleXMLElement {
        if (!isset($this->accountApi)) {
            throw new MissingValueException(
                'Account API key must be set before creating a query.'
            );
        }
        if (!isset($this->userApi)) {
            throw new MissingValueException(
                'User API key must be set before creating a query.'
            );
        }
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;

        $baseXml = simplexml_load_string($xmlString);

        $baseXml->addChild('AccountAPI', $this->getAccountApi());
        $baseXml->addChild('UserAPI', $this->getUserApi());

        return $baseXml;
    }
}
