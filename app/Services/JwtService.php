<?php

namespace App\Services;

use App\Exceptions\ExpiredJwtTokenException;
use DateTimeInterface;
use Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validator;

class JwtService
{
    /**
     * JwtService class constructor.
     *
     * @param Signer $algorithm
     * @param Key $signingKey
     * @param Builder $builder
     * @param Parser $parser
     * @param DateTimeInterface $dateTime
     * @param Validator $validator
     */
    public function __construct(
        private readonly Signer            $algorithm,
        private readonly Signer\Key        $signingKey,
        private readonly Builder           $builder,
        private readonly Parser            $parser,
        private readonly DateTimeInterface $dateTime,
        private readonly Validator         $validator
    ) {
    }

    /**
     * Creates and returns as string new JWT token.
     *
     * @param int $userId
     *
     * @return array
     */
    public function generateTokens(int $userId): array
    {
        return [
            $this->generateAccessToken($userId)->toString(),
            $this->generateRefreshToken($userId)->toString(),
        ];
    }

    /**
     * Method which parses and validates given token returning boolean result.
     *
     * @param string $bearer
     * @throws RequiredConstraintsViolated|ExpiredJwtTokenException|Exception
     */
    public function parseAndValidateToken(string $bearer): void
    {
        $token = $this->parser->parse($bearer);

        $this->validator->assert($token,
            new IssuedBy(env('APP_URL')),
            new SignedWith($this->algorithm, $this->signingKey),
            // we can add and check all stuff we desire here ...
        );

        if ($token->isExpired($this->dateTime)) {
            throw new ExpiredJwtTokenException();
        }
    }

    /**
     * Creates and returns as string new JWT access token.
     *
     * @param int $userId
     *
     * @return UnencryptedToken
     */
    private function generateAccessToken(int $userId): UnencryptedToken
    {
        return $this->generateToken($userId, $this->dateTime->modify('+1 hour'));
    }

    /**
     * Creates and returns as string new JWT refresh token.
     *
     * @param int $userId
     *
     * @return UnencryptedToken
     */
    private function generateRefreshToken(int $userId): UnencryptedToken
    {
        return $this->generateToken($userId, $this->dateTime->modify('+7 days'));
    }

    /**
     * Methods which generates generic JWT token.
     *
     * @param int $userId
     * @param DateTimeInterface $expiresAt
     *
     * @return UnencryptedToken
     */
    private function generateToken(int $userId, DateTimeInterface $expiresAt): UnencryptedToken
    {
        return $this->builder
            ->issuedBy(env('APP_URL'))
            ->permittedFor(env('APP_URL'))
            ->issuedAt($this->dateTime)
            ->expiresAt($expiresAt)
            ->withClaim('userId', $userId)
            ->getToken($this->algorithm, $this->signingKey);
    }
}