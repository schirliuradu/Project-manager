<?php

namespace App\Providers;

use App\Services\JwtService;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Builder as BuilderInterface;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Validator;

class JwtServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // resolve validator interface
        $this->app->when(JwtService::class)
            ->needs(Validator::class)
            ->give(fn () => new \Lcobucci\JWT\Validation\Validator());

        // resolve parser interface
        $this->app->when(JwtService::class)
            ->needs(Parser::class)
            ->give(fn () => new \Lcobucci\JWT\Token\Parser(new JoseEncoder()));

        // resolve builder interface
        $this->app->when(JwtService::class)
            ->needs(BuilderInterface::class)
            ->give(fn () => new Builder(new JoseEncoder(), ChainedFormatter::default()));

        // resolve key interface
        $this->app->when(JwtService::class)
            ->needs(Signer\Key::class)
            ->give(fn () => InMemory::plainText(env('JWT_SECRET')));

        // resolve signer algorithm interface
        $this->app->when(JwtService::class)
            ->needs(Signer::class)
            ->give(fn () => new Signer\Hmac\Sha256());

        // resolve for datetime interface
        $this->app->when(JwtService::class)
            ->needs(DateTimeInterface::class)
            ->give(fn () => new DateTimeImmutable());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
