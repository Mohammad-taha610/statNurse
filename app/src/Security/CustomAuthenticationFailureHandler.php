<?php

// src/Security/CustomAuthenticationFailureHandler.php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class CustomAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    private $defaultFailurePath;

    public function __construct(string $defaultFailurePath = '/executive/failure')
    {
        $this->defaultFailurePath = $defaultFailurePath;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Here you can return a custom response or handle the failure in a way you prefer.
        // For instance:
        return new Response('Authentication Failed: '.$exception->getMessage());
    }
}
