<?php declare(strict_types=1);
/*
 * This file is part of TwitterOAuth2.
 *
 * (c) Drate Rattletrap <draterattletrap@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DrateRattletrap\Twitter\Tests\Unit;

use DrateRattletrap\Twitter\Provider\OAuth2;
use PHPUnit\Framework\TestCase;

final class OAuth2Test extends TestCase
{
    /**
     * @var OAuth2
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new OAuth2(
            [
                'clientId' => 'client',
                'clientSecret' => 'secret',
                'authorizeUri' => 'https://twitter.com/i/oauth2/authorize',
                'accessTokenUri' => 'https://api.twitter.com/2/oauth2/token',
                'resourceOwnerDetailsUri' => 'https://api.twitter.com/2/users/me',
                'redirectUri' => 'https://my.sminstall.net/twitter/tw.php',
                'pkceMethod' => 'plain', // 'S256'
                'scopeSeparator' => ' ',
            ]
        );
    }

    public function testGetAuthenticationBasicToken(): void
    {
        $this->assertSame(
            'Y2xpZW50OnNlY3JldA==',
            $this->provider->getAuthenticationBasicToken()
        );
    }

    public function testGetAuthorizationUrl(): void
    {
        $this->assertSame(
            'https://twitter.com/i/oauth2/authorize?response_type=code&client_id=client&redirect_uri=https%3A%2F%2Fmy.sminstall.net%2Ftwitter%2Ftw.php&scope=users.read%20tweet.read%20offline.access%20dm.read&state=sm_auth%2012345%20ordersiteid%20201&code_challenge=challenge&code_challenge_method=plain',
            $this->provider->getAuthorizationUrl([
                'scope' => ['users.read', 'tweet.read', 'offline.access', 'dm.read'],
                'state' => 'sm_auth 12345 ordersiteid 201',
                'challenge' => 'challenge',
            ])
        );
    }
}
