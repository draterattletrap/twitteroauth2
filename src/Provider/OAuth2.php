<?php
/*
 * This file is part of TwitterOAuth2.
 *
 * (c) Drate Rattletrap <draterattletrap@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DrateRattletrap\Twitter\Provider;

use Curl\Curl;
use DrateRattletrap\Twitter\Exception\OAuth2Exception;
use DrateRattletrap\Twitter\Model\OAuth2Token;

class OAuth2
{
    const EXPIRED = 418;

    /**
     * @var string
     */
    protected $authorizeUri;

    /**
     * @var string
     */
    protected $accessTokenUri;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $resourceOwnerDetailsUri;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $scopeSeparator;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var Curl\Curl
     */
    private $curl;

    /**
     * @var OAuth2Token
     */
    private $oauth2Token;

    public function __construct(array $options = [])
    {
        $this->authorizeUri = $options['authorizeUri'];
        $this->accessTokenUri = $options['accessTokenUri'];
        $this->redirectUri = $options['redirectUri'];
        $this->resourceOwnerDetailsUri = $options['resourceOwnerDetailsUri'];
        $this->clientId = $options['clientId'];
        $this->clientSecret = $options['clientSecret'];
        $this->scopeSeparator = $options['scopeSeparator'];

        $this->curl = new Curl();
    }

    /**
     * Builds the authorization URL.
     *
     * @param  array $options
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(array $options)
    {
        // Assemble the authorize URL and direct the user to a browser
        // to sign in to their Twitter account
        $authorizeQuery = array(
            "response_type" => "code",
            "client_id" => $this->clientId,
            "redirect_uri" => $this->redirectUri,
            "scope" => implode($this->scopeSeparator, $options['scope']),
            "state" => $options['state'],
            "code_challenge" => $options['challenge'],
            "code_challenge_method" => "plain" // @Todo "S256" does not work with our getPkceCode. Not sure why.
        );

        return $this->authorizeUri . '?' . http_build_query($authorizeQuery, "", "&", PHP_QUERY_RFC3986);
    }

    public function getOAuth2TokenFromAuthToken(string $code, string $codeVerifier): OAuth2Token
    {
        $this->curl->setHeader('Authorization', 'Basic ' . $this->getAuthenticationBasicToken($this->clientId, $this->clientSecret));
        $response = $this->curl->post(
            $this->accessTokenUri,
            [
                'grant_type' => 'authorization_code',
                "redirect_uri"=> $this->redirectUri,
                'code' => $code,
                'code_verifier' => $codeVerifier,
            ]
        );

        return $this->getOauth2TokenFromResponse($response);
    }

    /**
     * Requests an OAuth2 using a refresh token.
     * 
     * @return OAuth2Token
     * @throws Exception
     */
    public function getOAuth2TokenFromRefreshToken(string $refreshToken): OAuth2Token
    {
        $this->curl->setHeader('Authorization', 'Basic ' . $this->getAuthenticationBasicToken($this->clientId, $this->clientSecret));
        $response = $this->curl->post(
            $this->accessTokenUri,
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]
        );

        return $this->getOauth2TokenFromResponse($response);
    }

    /**
     *
     * @return mixed
     * @throws OAuth2Exception
     */
    public function getResourceOwnerDetails()
    {
        if ($this->oauth2Token->isExpired()) {
            throw new OAuth2Exception(
                'oauth2 token has expired',
                self::EXPIRED
            );
        }

        $this->curl->setHeader('authorization', $this->oauth2Token->getAuthorizationHeader());
        $response = $this->curl->get('/2/users/me');
        if ($this->curl->error) {
            throw new OAuth2Exception(
                'Error[' . print_r($response, true) . '] ' . $response->error_description . PHP_EOL . print_r($this->curl->diagnose(), true),
                $this->curl->errorCode
            );
        }

        return $this->curl->response->data;
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
     * Returns a new random string to use as PKCE code_verifier and
     * hashed as code_challenge parameters in an authorization flow.
     * Must be between 43 and 128 characters long.
     *
     * @param  int $length Length of the random string to be generated.
     * @return string
     */
    public function getPkceCode($length = 64)
    {
        // Generate the code challenge using the OS / cryptographic random function
        $verifierBytes = random_bytes($length);
        $codeVerifier = rtrim(strtr(base64_encode($verifierBytes), "+/", "-_"), "=");

        // Very important, "raw_output" must be set to true or the challenge
        // will not match the verifier.
        $challengeBytes = hash("sha256", $codeVerifier, true);
        $codeChallenge = rtrim(strtr(base64_encode($challengeBytes), "+/", "-_"), "=");

        return $codeChallenge;
    }

    /**
     * Returns a base64 encoded token to use as Basic Authentication Token
     *
     * @return string
     */
    public function getAuthenticationBasicToken()
    {
        return trim(base64_encode(sprintf('%s:%s', $this->clientId, $this->clientSecret)));
    }

    private function getOauth2TokenFromResponse($response)
    {
        if ($this->curl->error) {
            throw new OAuth2Exception(
                'Error[' . $response->error . '] ' . $response->error_description . PHP_EOL . print_r($this->curl->diagnose(), true),
                $this->curl->errorCode
            );
        }

        $this->oauth2Token = new OAuth2Token(
            $response->token_type,
            $response->expires_in,
            $response->access_token,
            $response->scope,
            $response->refresh_token
        );

        return $this->oauth2Token;
    }
}
