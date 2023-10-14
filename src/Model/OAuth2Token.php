<?php
/*
 * This file is part of TwitterOAuth2.
 *
 * (c) Drate Rattletrap <draterattletrap@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DrateRattletrap\Twitter\Model;

use DateInterval;
use DateTime;
use JsonSerializable;

class OAuth2Token implements JsonSerializable
{
    /**
     * @var string
     */
    private $tokenType;

    /**
     * @var int
     */
    private $expiresIn;

    /**
     * @var int
     */
    private $accessToken;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var int
     */
    private $lastRefreshed;

    public function __construct(string $tokenType, string $expiresIn, string $accessToken, string $scope, string $refreshToken)
    {
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->accessToken = $accessToken;
        $this->scope = $scope;
        $this->refreshToken = $refreshToken;
        $this->lastRefreshed = idate('U');
    }

    /**
     * Get the value of tokenType
     *
     * @return  string
     */ 
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * Get the value of expiresIn
     *
     * @return  int
     */ 
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * Get the value of accessToken
     *
     * @return  int
     */ 
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the value of scope
     *
     * @return  string
     */ 
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Get the value of refreshToken
     *
     * @return  string
     */ 
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get the Autorization header for this token
     * 
     * @return string
     */
    public function getAuthorizationHeader(): string
    {
        return $this->getTokenType() . ' ' . $this->getAccessToken();
    }

    /**
     *
     * @return boolean
     */
    public function isExpired(): bool
    {
        $now = new DateTime('now');
        $expires = new DateTime('now');
        $dateTimeInterval = new DateInterval('PT' . intval($this->expiresIn) . 'S');
        $expires->setTimestamp($this->lastRefreshed);
        $expires->add($dateTimeInterval);

        return $now > $expires;
    }

    public function jsonSerialize(): array
    {
        return [
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'access_token' => $this->accessToken,
            'scope' => $this->scope,
            'refresh_token' => $this->refreshToken,
            'last_refreshed' => $this->lastRefreshed,
        ];
    }
}
