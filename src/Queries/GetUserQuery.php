<?php

/**
 * Contains SmarterU\Queries\GetUserQuery
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     Proprietary
 * @version     $version
 * @since       2022/07/13
 */

declare(strict_types=1);

namespace SmarterU\Queries;

use SmarterU\Exceptions\InvalidArgumentException;
use SmarterU\Queries\BaseQuery;


/**
 * Represents a getUser query made to the SmarterU API.
 */
class GetUserQuery extends BaseQuery {
    /**
     * The system-generated identifier for the user. This tag is mutually exclusive
     * with the Email and EmployeeID tags. This is the ID returned by the listUsers
     * method.
     */
    protected ?int id = null;

    /**
     * The email address of the user. This tag is mutually exclusive with the ID
     * and EmployeeID tags. This is the Email returned by the listUsers method.
     */
    protected ?string email = null;

    /**
     * The employee ID of the user. This tag is mutually exclusive with the ID and
     * Email tags. This is the EmployeeID returned by the listUsers method.
     */
    protected ?int employeeId = null;

    /**
     * Return the system-generated identifier for the user.
     *
     * @return ?int The system-generated identifier for the user if it exists
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * Set the system-generated identifier for the user.
     *
     * @param int $id The system-generated identifier for the user
     * @return self
     */
    public function setId(int $id): self {
        $this->id = $id;
        $this->email = null;
        $this->employeeId = null;
        return $this;
    }

    /**
     * Return the email address of the user.
     *
     * @return ?string $email The user's email address
     */
    public function getEmail(): ?string {
        return $this->email;
    }

    /**
     * Set the email address for the user.
     *
     * @param string $email The user's email address
     * @return self
     */
    public function setEmail(string $email): self {
        $this->id = null;
        $this->email = $email;
        $this->employeeId = null;
        return $this;
    }

    /**
     * Return the user's employee ID.
     *
     * @return ?int The user's employee ID
     */
    public function getEmployeeId(): ?int {
        return $this->employeeId;
    }

    /**
     * Set the employee ID for the user.
     *
     * @param int $employeeId The user's employee ID
     * @return self
     */
    public function setEmployeeId(int $employeeId): self {
        $this->id = null;
        $this->email = null;
        $this->employeeId = $employeeId;
        return $this;
    }

    /**
     * Generate an XML representation of the query, to be passed into the
     * SmarterU API.
     *
     * @return SimpleXMLElement the XML representation of the query
     * @throws InvalidArgumentException if all parameters are missing
     */
    public function toXml(): SimpleXMLElement {
        $xml = $this->createBasicXml();
        $xml->addChild('method', 'getUser');
        $parameters = $xml->addChild('parameters');
        $user = $parameters->addChild('User');
        if ($this->getId() !== null) {
            $user->addChild('ID', $this->getId());
        }
        else if ($this->getEmail() !== null) {
            $user->addChild('Email', $this->getEmail());
        }
        else if ($this->getEmployeeId() !== null) {
            $user->addChild('EmployeeID', $this->getEmployeeId());
        }
        else {
            throw new InvalidArgumentException('GetUserQuery must have a parameter');
        }
        return $xml;
}
