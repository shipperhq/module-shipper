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

use Magento\Framework\App\Config\ScopeConfigInterface;
use ShipperHQ\GraphQL\Request\SecureHeaders;

/**
 * Shipping data helper
 */
class GraphQLHelper
{
    const SHIPPERHQ_POSTORDER_ENDPOINT_PATH = 'carriers/shipper/postorder_url';
    const SHIPPERHQ_ENDPOINT_PATH = 'carriers/shipper/graphql_url';
    const SHIPPERHQ_TIMEOUT_PATH = 'carriers/shipper/ws_timeout';
    const SHIPPERHQ_SERVER_API_KEY_PATH = 'carriers/shipper/api_key';
    const SHIPPERHQ_SERVER_SCOPE_PATH = 'carriers/shipper/environment_scope';

    /** @var ScopeConfigInterface */
    private $config;

    /** @var Authorization */
    private $authHelper;

    /**
     * GraphQL constructor.
     * @param ScopeConfigInterface $config
     * @param Authorization $authHelper
     */
    public function __construct(
        ScopeConfigInterface $config,
        Authorization $authHelper
    ) {
        $this->config = $config;
        $this->authHelper = $authHelper;
    }

    /**
     * @param null $sessionId
     *
     * @return SecureHeaders
     */
    public function buildRequestHeader($sessionId = 'None')
    {
        $secretToken = $this->authHelper->getSecretToken();
        $shqScope = $this->config->getValue(self::SHIPPERHQ_SERVER_SCOPE_PATH);
        $cartSessionId = $sessionId;

        return new SecureHeaders($secretToken, $shqScope, $cartSessionId);
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->config->getValue(self::SHIPPERHQ_ENDPOINT_PATH);
    }

    /**
     * @return mixed
     */
    public function getPostorderEndpoint()
    {
        return $this->config->getValue(self::SHIPPERHQ_POSTORDER_ENDPOINT_PATH);
    }

    /**
     * @return mixed
     */
    public function getListingEndpoint()
    {
        return $this->config->getValue(self::SHIPPERHQ_POSTORDER_ENDPOINT_PATH) . '/label';
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->config->getValue(self::SHIPPERHQ_TIMEOUT_PATH);
    }
}
