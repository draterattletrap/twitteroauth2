<?php
/*
 * This file is part of TwitterOAuth2.
 *
 * (c) Drate Rattletrap <draterattletrap@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DrateRattletrap\Twitter;

use Curl\Curl;
use DrateRattletrap\Twitter\Exception\OAuth2Exception;
use DrateRattletrap\Twitter\Model\OAuth2Token;

class Client
{
    const EXPIRED = 418;

    /**
     * @var string
     */
    public $resourceOwnerDetailsUri = '/2/users/me';

    /**
     * @var Curl\Curl
     */
    private $curl;

    /**
     * @var OAuth2Token
     */
    private $oauth2Token;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(OAuth2Token $oauth2Token, string $baseUrl='https://api.twitter.com')
    {
        if ($oauth2Token->isExpired()) {
            throw new OAuth2Exception(
                'oauth2 token has expired',
                self::EXPIRED
            );
        }

        $this->curl = new Curl($baseUrl);
        $this->oauth2Token = $oauth2Token;
        $this->baseUrl = $baseUrl;
    }

    /**
     *
     * @return mixed
     * @throws OAuth2Exception
     */
    public function getResourceOwnerDetails()
    {
        return $this->getByUri($this->resourceOwnerDetailsUri)->data;
    }

    /**
     * Set the Curl class for unit testing purposes.
     *
     * @param Curl $curl
     */
    public function setCurl(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * 
     * @param string $uri
     * @param array $data
     * @return mixed
     * @throws OAuth2Exception
     */
    public function getByUri(string $uri, array $data=[])
    {
        $this->curl->setHeader('Authorization', $this->oauth2Token->getAuthorizationHeader());
        $this->curl->get($uri, $data);
        if ($this->curl->error) {
            throw new OAuth2Exception(
                $this->curl->errorMessage . PHP_EOL . $this->curl->diagnose(),
                $this->curl->errorCode
            );
        }
        
        return $this->curl->response;
    }
}
