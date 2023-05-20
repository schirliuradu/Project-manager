<?php

namespace Tests\Unit\Services;

use App\Exceptions\ExpiredJwtTokenException;
use App\Services\JwtService;
use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validator;
use Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \App\Services\JwtService
 */
class JwtServiceTest extends UnitTestCase
{
    private JwtService $service;

    private Signer $algorithmMock;
    private Signer\Key $signingKeyMock;
    private Builder $builderMock;
    private Parser $parserMock;
    private DateTimeImmutable $datetimeMock;
    private Validator $validatorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->algorithmMock = \Mockery::mock(Signer::class);
        $this->signingKeyMock = \Mockery::mock(Signer\Key::class);
        $this->builderMock = \Mockery::mock(Builder::class);
        $this->parserMock = \Mockery::mock(Parser::class);
        $this->datetimeMock = \Mockery::mock(DateTimeImmutable::class);
        $this->validatorMock = \Mockery::mock(Validator::class);

        $this->service = new JwtService(
            $this->algorithmMock,
            $this->signingKeyMock,
            $this->builderMock,
            $this->parserMock,
            $this->datetimeMock,
            $this->validatorMock
        );
    }

    /**
     * @test
     * @covers ::generateTokens
     */
    public function should_return_both_access_and_refresh_tokens_as_strings(): void
    {
        $userId = 1111;

        $fakeAccessToken = \Mockery::mock(UnencryptedToken::class);
        $fakeAccessToken->shouldReceive('toString')->andReturn('hooray-access-token');

        $fakeRefreshToken = \Mockery::mock(UnencryptedToken::class);
        $fakeRefreshToken->shouldReceive('toString')->andReturn('hooray-refresh-token');

        $this->datetimeMock->shouldReceive('modify')->twice();

        $this->builderMock->shouldReceive('issuedBy')->twice()->andReturnSelf();
        $this->builderMock->shouldReceive('permittedFor')->twice()->andReturnSelf();
        $this->builderMock->shouldReceive('issuedAt')->twice()->andReturnSelf();
        $this->builderMock->shouldReceive('expiresAt')->twice()->andReturnSelf();
        $this->builderMock->shouldReceive('withClaim')->twice()->andReturnSelf();
        $this->builderMock->shouldReceive('getToken')
            ->twice()
            ->with($this->algorithmMock, $this->signingKeyMock)
            ->andReturns($fakeAccessToken, $fakeRefreshToken);

        $tokens = $this->service->generateTokens($userId);

        $this->assertCount(2, $tokens);
        $this->assertIsString($tokens[0]);
        $this->assertIsString($tokens[1]);
    }

    /**
     * @test
     * @covers ::parseAndValidateToken
     */
    public function should_handle_bearer_parsing_errors_and_bubble_them_up(): void
    {
        $this->parserMock->shouldReceive('parse')
            ->once()
            ->andThrow(new CannotDecodeContent());

        $this->expectException(CannotDecodeContent::class);
        $this->service->parseAndValidateToken('test.bearer.token');
    }

    /**
     * @test
     * @covers ::parseAndValidateToken
     */
    public function should_handle_validation_errors_and_bubble_them_up(): void
    {
        $this->parserMock->shouldReceive('parse')
            ->once()
            ->andReturn(\Mockery::mock(Token::class));

        $this->validatorMock->shouldReceive('assert')
            ->once()
            ->andThrow(new RequiredConstraintsViolated());

        $this->expectException(RequiredConstraintsViolated::class);
        $this->service->parseAndValidateToken('test.bearer.token');
    }

    /**
     * @test
     * @covers ::parseAndValidateToken
     */
    public function should_throw_custom_exception_if_token_is_expired(): void
    {
        $fakeToken = \Mockery::mock(Token::class);
        $fakeToken->shouldReceive('isExpired')
            ->once()
            ->andReturnTrue();

        $this->parserMock->shouldReceive('parse')
            ->once()
            ->andReturn($fakeToken);

        $this->validatorMock->shouldReceive('assert')->once();

        $this->expectException(ExpiredJwtTokenException::class);
        $this->service->parseAndValidateToken('test.bearer.token');
    }

    /**
     * @test
     * @covers ::parseAndValidateToken
     */
    public function should_follow_correctly_validation_flow_if_there_are_no_violated_constraints_and_bearer_is_valid(): void
    {
        $fakeToken = \Mockery::mock(Token::class);
        $fakeToken->shouldReceive('isExpired')
            ->once()
            ->andReturnFalse();

        $this->parserMock->shouldReceive('parse')
            ->once()
            ->andReturn($fakeToken);

        $this->validatorMock->shouldReceive('assert')->once();

        $this->service->parseAndValidateToken('test.bearer.token');
    }
}