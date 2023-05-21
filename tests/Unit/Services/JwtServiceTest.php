<?php

namespace Tests\Unit\Services;

use App\Exceptions\ExpiredJwtRefreshTokenException;
use App\Exceptions\ExpiredJwtTokenException;
use App\Exceptions\UnauthorizedUserException;
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
        $fakeToken = \Mockery::mock(UnencryptedToken::class);
        $fakeToken->shouldReceive('isExpired')
            ->once()
            ->andReturnFalse();

        $this->parserMock->shouldReceive('parse')
            ->once()
            ->andReturn($fakeToken);

        $this->validatorMock->shouldReceive('assert')->once();

        $this->service->parseAndValidateToken('test.bearer.token');
    }

    /**
     * @test
     * @covers ::refreshToken
     */
    public function refresh_access_token_should_throw_custom_exception_if_given_refresh_token_is_expired(): void
    {
        $service = \Mockery::mock(JwtService::class, [
            $this->algorithmMock,
            $this->signingKeyMock,
            $this->builderMock,
            $this->parserMock,
            $this->datetimeMock,
            $this->validatorMock
        ])->makePartial();

        $service->shouldReceive('parseAndValidateToken')
            ->once()
            ->with($fakeRefreshToken = 'fake_refresh_token')
            ->andThrow(ExpiredJwtTokenException::class);

        $this->expectException(ExpiredJwtRefreshTokenException::class);
        $service->refreshToken($fakeRefreshToken);
    }

    /**
     * @test
     * @covers ::refreshToken
     */
    public function refresh_access_token_should_throw_custom_exception_if_given_refresh_token_validation_fails(): void
    {
        $service = \Mockery::mock(JwtService::class, [
            $this->algorithmMock,
            $this->signingKeyMock,
            $this->builderMock,
            $this->parserMock,
            $this->datetimeMock,
            $this->validatorMock
        ])->makePartial();

        $service->shouldReceive('parseAndValidateToken')
            ->once()
            ->with($fakeRefreshToken = 'fake_refresh_token')
            ->andThrow(\Exception::class);

        $this->expectException(UnauthorizedUserException::class);
        $service->refreshToken($fakeRefreshToken);
    }

    /**
     * @test
     * @covers ::refreshToken
     */
    public function refresh_access_token_should_generate_and_return_new_access_token_as_string(): void
    {
        $fakeRefreshToken = 'fake_refresh_token';

        $service = \Mockery::mock(JwtService::class, [
            $this->algorithmMock,
            $this->signingKeyMock,
            $this->builderMock,
            $this->parserMock,
            $this->datetimeMock,
            $this->validatorMock
        ])->makePartial();

        $service->shouldReceive('getUserIdFromToken')
            ->once()
            ->with($fakeRefreshToken)
            ->andReturn('fake_user_id');

        $this->datetimeMock->shouldReceive('modify')
            ->once()
            ->with('+1 day')
            ->andReturnSelf();

        $fakeAccessTokenMock = \Mockery::mock(UnencryptedToken::class);
        $fakeAccessTokenMock->shouldReceive('toString')
            ->once()
            ->andReturn('fake_access_token');

        $this->builderMock->shouldReceive('issuedBy')->once()->andReturnSelf();
        $this->builderMock->shouldReceive('permittedFor')->once()->andReturnSelf();
        $this->builderMock->shouldReceive('issuedAt')->once()->andReturnSelf();
        $this->builderMock->shouldReceive('expiresAt')->once()->andReturnSelf();
        $this->builderMock->shouldReceive('withClaim')->once()->andReturnSelf();
        $this->builderMock->shouldReceive('getToken')
            ->once()
            ->with($this->algorithmMock, $this->signingKeyMock)
            ->andReturns($fakeAccessTokenMock);

        $service->refreshToken($fakeRefreshToken);
    }

    /**
     * @test
     * @covers ::getUserIdFromToken
     */
    public function get_user_id_from_token_should_return_user_id(): void
    {
        $service = \Mockery::mock(JwtService::class, [
            $this->algorithmMock,
            $this->signingKeyMock,
            $this->builderMock,
            $this->parserMock,
            $this->datetimeMock,
            $this->validatorMock
        ])->makePartial();

        // no way to mock this stuff, there is no interface for this class, and it is a final, unomockable...
        // the other way to mock this is with a mockery overload, but it is way terrible
        $fakeClaimsMock = new Token\DataSet([
            'userId' => 'fake_user_id'
        ], '');

        $fakeTokenMock = \Mockery::mock(UnencryptedToken::class);
        $fakeTokenMock->shouldReceive('claims')
            ->once()
            ->andReturn($fakeClaimsMock);

        $service->shouldReceive('parseAndValidateToken')
            ->once()
            ->with($fakeToken = 'fake_token')
            ->andReturn($fakeTokenMock);

        $this->assertEquals('fake_user_id', $service->getUserIdFromToken($fakeToken));
    }
}