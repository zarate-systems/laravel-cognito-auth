<?php

namespace ZarateSystems\LaravelCognitoAuth;

class AuthAttempt
{
    /**
     * @var bool
     */
    protected bool $successful;

    /**
     * @var array
     */
    protected array $response = [];

    /**
     * AuthAttempt constructor.
     *
     * @param  bool  $successful
     * @param  array  $response
     */
    public function __construct(bool $successful, array $response = [])
    {
        $this->successful = $successful;
        $this->response = $response;
    }

    /**
     * Was the authentication attempt successful.
     *
     * @return bool
     */
    public function successful(): bool
    {
        return $this->successful;
    }

    /**
     * Get the response data from an unsuccessful authentication attempt.
     *
     * @return array|null
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }
}
