<?php

namespace App\Services;

use App\Factories\JwtConfigurationFactory;
use DateTimeInterface;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;

class JwtService
{
    /**
     * JwtService class constructor.
     *
     * @param JwtFacade $jwtFacade
     * @param JwtConfigurationFactory $jwtConfigurationFactory
     * @param DateTimeInterface $dateTime
     */
    public function __construct(
        private readonly JwtFacade $jwtFacade,
        private readonly JwtConfigurationFactory $jwtConfigurationFactory,
        private readonly DateTimeInterface $dateTime
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
            $this->generateAccessToken($userId),
            $this->generateRefreshToken($userId),
        ];
    }

    /**
     * Creates and returns as string new JWT access token.
     *
     * @param int $userId
     *
     * @return string
     */
    protected function generateAccessToken(int $userId): string
    {
        return $this->generateToken($userId, $this->dateTime->modify('+1 hour'));
    }

    /**
     * Creates and returns as string new JWT refresh token.
     *
     * @param int $userId
     *
     * @return string
     */
    protected function generateRefreshToken(int $userId): string
    {
        return $this->generateToken($userId, $this->dateTime->modify('+7 days'));
    }

    /**
     * Methods which generates generic JWT token.
     *
     * @param int $userId
     * @param DateTimeInterface $expiresAt
     *
     * @return string
     */
    private function generateToken(int $userId, DateTimeInterface $expiresAt): string
    {
        $jwtConfig = $this->jwtConfigurationFactory->create();

        return $this->jwtFacade->issue(
            $jwtConfig->signer(),
            $jwtConfig->signingKey(),
            function (Builder $builder) use ($userId, $expiresAt) {
                return $builder
                    ->issuedBy(env('APP_URL'))
                    ->permittedFor(env('APP_URL'))
                    ->issuedAt($this->dateTime)
                    ->expiresAt($expiresAt)
                    ->withClaim('userId', $userId);
            }
        )->toString();
    }
}