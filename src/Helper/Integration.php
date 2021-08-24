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

use Magento\Integration\Model\IntegrationService;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Model\OauthService;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\AuthorizationService;

/**
 * Shipping data helper
 */
class Integration
{
    const INTEGRATION_NAME = 'ShipperHQ Listing Pane';

    /** @var IntegrationService */
    private $integrationService;

    /** @var OauthService */
    private $oauthService;

    /** @var AuthorizationService */
    private $authorizationService;

    /** @var string|false */
    private $apiKey = false;

    /**
     * Integration constructor.
     */
    public function __construct(
        IntegrationService $integrationService,
        OauthService $oauthService,
        AuthorizationService $authorizationService
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getApiKey()
    {
        $this->apiKey = $this->getApiKeyFromExistingIntegration();

        if (!$this->apiKey) {
            $this->createNewIntegration();
            $this->apiKey = $this->getApiKeyFromExistingIntegration();
        }

        return $this->apiKey;
    }

    /**
     * @param IntegrationModel $integration
     * @return false|string
     */
    private function getIntegrationApiKey($integration)
    {
        $accessToken = $this->getIntegrationAccessToken($integration);
        if ($accessToken) {
            return $accessToken->getToken();
        }
        return false;
    }

    /**
     * @param IntegrationModel $integration
     * @return false|Token
     */
    private function getIntegrationAccessToken($integration)
    {
        return $this->oauthService->getAccessToken($integration->getConsumerId());
    }

    /**
     * @return false|IntegrationModel
     */
    private function fetchExistingIntegration()
    {
        $integration = $this->integrationService->findByName(self::INTEGRATION_NAME);
        if (!$integration->isObjectNew() && $this->integrationIsActive($integration)) {
            return $integration;
        }

        return false;
    }

    /**
     * @param IntegrationModel $integration
     * @return bool
     */
    private function integrationIsActive($integration)
    {
        return in_array($integration->getStatus(), [
            IntegrationModel::STATUS_ACTIVE,
            IntegrationModel::STATUS_RECREATED
        ]);
    }

    /**
     * @return IntegrationModel|IntegrationService
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createNewIntegration()
    {
        $integration = $this->integrationService->create(['name' => self::INTEGRATION_NAME, 'status' => IntegrationModel::STATUS_ACTIVE]);
        $this->authorizationService->grantPermissions($integration->getId(), ['ShipperHQ_Shipper::createlisting']);
        $this->oauthService->createAccessToken($integration->getConsumerId());
        return $integration;
    }

    /**
     * @return false|string
     */
    private function getApiKeyFromExistingIntegration()
    {
        $existingIntegration = $this->fetchExistingIntegration();
        if ($existingIntegration) {
            return $this->getIntegrationApiKey($existingIntegration);
        }

        return false;
    }
}
