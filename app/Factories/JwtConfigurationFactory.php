<?php

namespace App\Factories;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class JwtConfigurationFactory
{
    /**
     * Creates jwt configuration object instance.
     *
     * @return Configuration
     */
    public function create(): Configuration
    {
        return Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(env('JWT_SECRET'))
        );
    }
}