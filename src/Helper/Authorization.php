<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Helper;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use ShipperHQ\GraphQL\Client\GraphQLClient;
use ShipperHQ\GraphQL\Response\CreateSecretToken;

/**
 * Shipping data helper
 */
class Authorization
{
    const SHIPPERHQ_ENDPOINT_PATH = GraphQLHelper::SHIPPERHQ_ENDPOINT_PATH;
    const SHIPPERHQ_TIMEOUT_PATH = GraphQLHelper::SHIPPERHQ_TIMEOUT_PATH;
    const SHIPPERHQ_SERVER_SCOPE_PATH = GraphQLHelper::SHIPPERHQ_SERVER_SCOPE_PATH;
    const SHIPPERHQ_SERVER_API_KEY_PATH = GraphQLHelper::SHIPPERHQ_SERVER_API_KEY_PATH;
    const SHIPPERHQ_TOKEN_ENDPOINT_PATH = 'carriers/shipper/token_url';
    const SHIPPERHQ_SERVER_AUTH_CODE_PATH = 'carriers/shipper/password';
    const SHIPPERHQ_SERVER_SECRET_TOKEN_PATH = 'carriers/shipper/secret_token';
    const SHIPPERHQ_SERVER_PUBLIC_TOKEN_PATH = 'carriers/shipper/public_token';
    const SHIPPERHQ_SERVER_TOKEN_EXPIRES_PATH = 'carriers/shipper/token_expires';
    const SHIPPERHQ_SERVER_EXPIRING_SOON_THRESHOLD = 60 * 60; // 1 hour

    /** @var ReinitableConfigInterface */
    private $configReader;

    /** @var WriterInterface */
    private $configWriter;

    /** @var GraphQLClient */
    private $graphqlClient;

    /** @var DateTime */
    private $dateTime;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /** @var bool */
    private $isConfigCacheFlushScheduled = false;

    /**
     * Authorization constructor.
     * @param ReinitableConfigInterface $configReader
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param GraphQLClient $graphqlClient
     */
    public function __construct(
        ReinitableConfigInterface $configReader,
        WriterInterface $configWriter,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        GraphQLClient $graphqlClient,
        DateTime $dateTime
    )
    {
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->jsonDecoder = $jsonDecoder;
        $this->graphqlClient = $graphqlClient;
        $this->dateTime = $dateTime;
    }

    /**
     * Get a secret token
     * If a token exists and won't be expiring for some time then that token will be returned.  If the token is
     * expiring soon or there is not an existing token then a new one will be fetched from the Auth service.
     *
     * When a new secret token is fetched the public token and expiration date are extracted from the secret token then
     * all three of these values are persisted to configuration.
     *
     * @param bool $cachedOnly
     * @return mixed|string
     * @throws \ReflectionException
     */
    public function getSecretToken(bool $cachedOnly = false)
    {
        if ($cachedOnly || !$this->isNewSecretTokenSuggested()) {
            return $this->getStoredSecretToken();
        }

        $params = [
            'client_id' =>  $this->getApiKey(),
            'client_secret' => $this->getAuthCode(),
                'grant_type' => 'client_credentials'
        ];

        $url = $this->getEndpoint();
        $client = new \Zend_Http_Client();
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setParameterGet($params);
        $response = $client->request();
        $result = $response->getBody();


        $tokenResult = $this->jsonDecoder->decode($result);
        if ($tokenResult && isset($tokenResult['token'])) {

            $tokenStr = $tokenResult['token'];
            if ($tokenStr) {

                $token = (new Parser())->parse($tokenStr);
                $verified = $token->verify(new Sha256(), $this->getAuthCode());
                $currentTime = $this->dateTime->gmtTimestamp();
                $issuedAt = $token->getClaim('iat');
                $expiresAt = $token->getClaim('exp');
                $apiKey = $token->getClaim('api_key');
                $publicToken = $token->getClaim('public_token');

                if ($verified && $apiKey == $this->getApiKey() && $issuedAt <= $currentTime && $currentTime <= $expiresAt) {
                    $this->writeToConfig(self::SHIPPERHQ_SERVER_SECRET_TOKEN_PATH, $tokenStr);
                    $this->writeToConfig(self::SHIPPERHQ_SERVER_PUBLIC_TOKEN_PATH, $publicToken);
                    // Timestamps are always UTC but let's be explicit so it's clear that we expect UTC here
                    $expiresAt = (new \DateTime("@$expiresAt", new \DateTimeZone("UTC")))->format('c');
                    $this->writeToConfig(self::SHIPPERHQ_SERVER_TOKEN_EXPIRES_PATH, $expiresAt);

                    return $tokenStr;
                }
            }
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getPublicToken()
    {
        return $this->getConfigValue(self::SHIPPERHQ_SERVER_PUBLIC_TOKEN_PATH);
    }

    /**
     * Returns if Secret Token has already expired
     *
     * @return bool
     */
    public function isSecretTokenExpired(): bool
    {
        $currentTime = $this->dateTime->gmtTimestamp();
        $expirationTime = strtotime($this->getTokenExpires());
        return $currentTime >= $expirationTime;
    }

    /**
     * Returns if the Secret Token is within THRESHOLD seconds of expiring
     *
     * @return bool
     */
    public function isSecretTokenExpiringSoon(): bool
    {
        $currentTime = $this->dateTime->gmtTimestamp();
        $expirationTime = strtotime($this->getTokenExpires());
        return ($currentTime + self::SHIPPERHQ_SERVER_EXPIRING_SOON_THRESHOLD) >= $expirationTime;
    }

    /**
     * Checks if the secret token has a valid signature
     *
     * @return bool
     */
    public function isSecretTokenValid(): bool
    {
        $tokenStr = $this->getStoredSecretToken();
        $token = (new Parser())->parse($tokenStr);
        return $token->verify(new Sha256(), $this->getAuthCode());
    }

    /**
     * If the current token is invalid or is about to expire then returns true
     *
     * @return bool
     */
    public function isNewSecretTokenSuggested(): bool
    {
        return $this->isSecretTokenExpiringSoon() || !$this->isSecretTokenValid();
    }

    /**
     * Wraps WriterInterface->save() but also schedules the config cache to be cleaned
     *
     * @param $path
     * @param $value
     * @param null $scope
     * @param null $scopeId
     */
    private function writeToConfig($path, $value, $scope = null, $scopeId = null)
    {
        $args = array_filter([$path, $value, $scope, $scopeId]);
        $this->configWriter->save(...$args);
        $this->scheduleConfigCacheFlush();
    }

    /**
     * Wraps ReinitableConfigInterface->getValue except allows for smartly reiniting config cache
     * @param $path
     * @param null $scopeType
     * @param null $scopeCode
     * @return mixed
     */
    private function getConfigValue($path, $scopeType = null, $scopeCode = null)
    {
        $args = array_filter([$path, $scopeType, $scopeCode]); // drop any null arguments

        if ($this->isConfigCacheFlushScheduled) {
            $this->configReader->reinit();
            $this->isConfigCacheFlushScheduled = false;
        }

        return $this->configReader->getValue(...$args);
    }

    /**
     * @return mixed
     */
    private function getApiKey()
    {
        return $this->getConfigValue(self::SHIPPERHQ_SERVER_API_KEY_PATH);
    }

    /**
     * @return mixed
     */
    private function getAuthCode()
    {
        return $this->getConfigValue(self::SHIPPERHQ_SERVER_AUTH_CODE_PATH);
    }

    /**
     * @return mixed
     */
    private function getEndpoint()
    {
        return $this->getConfigValue(self::SHIPPERHQ_TOKEN_ENDPOINT_PATH);
    }

    /**
     * @return mixed
     */
    private function getTimeout()
    {
        return $this->getConfigValue(self::SHIPPERHQ_TIMEOUT_PATH);
    }

    /**
     * @return mixed
     */
    private function getStoredSecretToken()
    {
        return $this->getConfigValue(self::SHIPPERHQ_SERVER_SECRET_TOKEN_PATH);
    }

    /**
     * Token expiration date in ISO 8601 format
     * @return mixed
     */
    private function getTokenExpires()
    {
        return $this->getConfigValue(self::SHIPPERHQ_SERVER_TOKEN_EXPIRES_PATH);
    }

    /**
     * @param \DateTime $expiration
     * @return Authorization
     */
    private function setTokenExpires(\DateTime $expiration): Authorization
    {
        $expiration = $expiration->format('c');
        $this->writeToConfig(self::SHIPPERHQ_SERVER_TOKEN_EXPIRES_PATH, $expiration);
        return $this;
    }

    /**
     * @return Authorization
     */
    private function scheduleConfigCacheFlush(): Authorization
    {
        $this->isConfigCacheFlushScheduled = true;
        return $this;
    }
}