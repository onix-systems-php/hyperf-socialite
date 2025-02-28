<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfSocialite\Tests;

use GuzzleHttp\Client;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mockery as m;
use OnixSystemsPHP\HyperfSocialite\Two\LinkedInProvider;
use OnixSystemsPHP\HyperfSocialite\Two\User;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 * @coversNothing
 */
class LinkedInProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testItCanMapAUserWithoutAnEmailAddress()
    {
        $request = m::mock(RequestInterface::class);
        $request->shouldReceive('input')->with('code')->andReturn('fake-code');

        $accessTokenResponse = m::mock(ResponseInterface::class);
        $accessTokenResponse->shouldReceive('getBody')->andReturn(json_encode(['access_token' => 'fake-token']));

        $basicProfileResponse = m::mock(ResponseInterface::class);
        $basicProfileResponse->shouldReceive('getBody')->andReturn(json_encode(['id' => $userId = 1]));

        // Make sure email address response contains no values.
        $emailAddressResponse = m::mock(ResponseInterface::class);
        $emailAddressResponse->shouldReceive('getBody')->andReturn(json_encode(['elements' => []]));

        $guzzle = m::mock(Client::class);
        $guzzle->shouldReceive('post')->once()->andReturn($accessTokenResponse);
        $guzzle->shouldReceive('get')->with('https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))', [
            'headers' => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ])->andReturn($basicProfileResponse);
        $guzzle->shouldReceive('get')->with('https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))', [
            'headers' => [
                'Authorization' => 'Bearer fake-token',
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ])->andReturn($emailAddressResponse);

        $provider = new LinkedInProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->stateless();
        $provider->setHttpClient($guzzle);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($userId, $user->getId());
        $this->assertNull($user->getEmail());
    }
}
