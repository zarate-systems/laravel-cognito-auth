<?php

namespace ZarateSystems\LaravelCognitoAuth;

use RuntimeException;

class AuthAttemptException extends RuntimeException
{
    /**
     * @var AuthAttempt
     */
    protected $response;

    /**
     * AuthAttemptException constructor.
     *
     * @param  AuthAttempt  $response
     */
    public function __construct(AuthAttempt $response)
    {
        $this->response = $response->getResponse();

        parent::__construct('Unable to authenticate');
    }

    /**
     * @return array|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}