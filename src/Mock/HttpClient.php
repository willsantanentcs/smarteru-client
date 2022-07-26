<?php

/**
 * Contains CBS\SmarterU\Mock\HttpClient.
 *
 * @author      Will Santanen <will.santanen@thecoresolution.com>
 * @copyright   $year$ Core Business Solutions
 * @license     ??
 * @version     $version$
 * @since       2022/07/26
 */

declare(strict_types=1);

use CBS\SmarterU\DataTypes\GroupPermissions;
use CBS\SmarterU\DataTypes\Permission;
use CBS\SmarterU\DataTypes\User;
use SimpleXMLElement;

/**
 * This class is a mock HTTP client used to test CBS\SmarterU\Client.
 */
class HttpClient {

    /**
     * The array used to keep track of all valid account API keys.
     */
    protected array $accountApiKeys;

    /**
     * The array used to keep track of all valid user API keys.
     */
    protected array $userApiKeys;

    /**
     * The array used to keep track of the Users that currently exist.
     */
    protected array $users;

    /**
     * The array used to keep track of all methods that are currently supported.
     */
    protected array $supportedMethods;

    /**
     * Add an Account API Key to the list of valid keys.
     *
     * @param string $accountApiKey The API key to add to the list
     * @return self
     */
    public function addAccountApiKey(string $accountApiKey): self {
        $this->accountApiKeys[] = $accountApiKey;
        return $this;
    }

    /**
     * Add a User API Key to the list of valid keys.
     *
     * @param string $userApiKey The API key to add to the list
     * @return self
     */
    public function addUserApiKey(string $userApiKey): self {
        $this->userApiKeys[] = $userApiKey;
    }

    /**
     * Add a method to the list of supported methods.
     *
     * @param string $method The name of the method to add to the list
     * @return self
     */
    public function addSupportedMethod(string $method): self {
        $this->supportedMethods[] = $method;
    }

    /**
     * Process a request made to the client, then pass the data off to the
     * appropriate helper function to generate a response.
     *
     * @param string $type The type of request being made, e.g. 'POST'
     * @param string $url The URL the request is being made to
     * @param ?array $options Any additional data to include with the request.
     *      Defaults to null.
     */
    public function request(string $type, string $url, ?array $options = null): string {
        if (empty($options)) {
            return $this->returnError(['SU:01' => 'No POST data detected']);
        }
        if (!array_key_exists('package', $options)) {
            return $this->returnError(['SU:02' => 'Package parameter not found']);
        }
        $body = $options['body'];
        $xml = simplexml_load_string($body); // returns false on failure
        if (!xml) {
            return $this->returnError(['SU:03' => 'Package data is not properly formatted XML']);
        }
        if ($xml->getName() !== 'SmarterU') {
            return $this->returnError(['SU:04' => 'SmarterU root tag not found in Package data']);
        }
        if (!isset($xml->AccountAPI)) {
            return $this->returnError(['SU:05' => 'AccountAPI tag not found in Package data']);
        }
        if (!isset($xml->UserAPI)) {
            return $this->returnError(['SU:06' => 'UserAPI tag not found in Package data']);
        }
        if (!isset($xml->Method)) {
            return $this->returnError(['SU:07' => 'Method tag not found in Package data']);
        }
        if (!isset($xml->Parameters)) {
            return $this->returnError(['SU:08' => 'Parameters tag not found in Package data']);
        }
        if ($xml->Parameters->count() == 0) {
            return $this->returnError(['SU:09' => 'Parameters tag contains no information']);
        }
        $accountApi = $xml->AccountAPI;
        $userApi = $xml->UserAPI;
        $method = $xml->Method;
        if (
            !in_array($accountApi, $this->accountApiKeys)
            || !in_array($userApi, $this->userApiKeys)
        ) {
            return $this->returnError(['SU:10' => 'User and Account API keys are invalid']);
        }
        if (!in_array($method, $this->supportedMethods)) {
            return $this->returnError(['SU:11' => 'Requested method does not exist']);
        }
    }
    
    /**
     * Generate the response message to be used when a fatal error occurs and
     * prevents the execution of the method.
     *
     * @param array $errors An array of ['errorId' => 'errorMessage'] containing
     *      the error(s) to include in the response.
     * @return string An XML representation of the error message
     */
    protected function returnError(array $errors): string {
        $xmlString = <<<XML
        <SmarterU>
        </SmarterU>
        XML;

        $xml = simplexml_load_string($xmlString);

        $xml->addChild('Result', 'Failed');
        $xml->addChild('Info');
        $errorsTag = $xml->addChild('Errors');
        foreach ($errors as $id => $message) {
            $error = $errorsTag->addChild('Error');
            $error->addChild('ErrorID', $id);
            $error->addChild('ErrorMessage', $message);
        }
        return $xml->asXML();
    }
}
