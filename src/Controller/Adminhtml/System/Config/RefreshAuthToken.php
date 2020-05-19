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
namespace ShipperHQ\Shipper\Controller\Adminhtml\System\Config;

use Magento\Backend\Model\Auth;
use Magento\Framework\Controller\Result\JsonFactory;
use ShipperHQ\Shipper\Helper\Authorization;

class RefreshAuthToken extends \ShipperHQ\Shipper\Controller\Adminhtml\RefreshAuthToken
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \ShipperHQ\Shipper\Helper\Authorization
     */
    private $authHelper;

    /** @var \ShipperHQ\Shipper\Helper\Config */
    private $configHelper;

    /**
     * @param \Magento\Backend\App\Action\Context    $context
     * @param \ShipperHQ\Shipper\Helper\Authorization $authHelper
     * @param JsonFactory                            $resultJsonFactory
     * @param \ShipperHQ\Shipper\Helper\Config        $configHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \ShipperHQ\Shipper\Helper\Authorization $authHelper,
        JsonFactory $resultJsonFactory,
        \ShipperHQ\Shipper\Helper\Config $configHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authHelper = $authHelper;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * Refresh auth token
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->configHelper->deleteFromConfig(Authorization::SHIPPERHQ_SERVER_SECRET_TOKEN_PATH);
        $this->configHelper->deleteFromConfig(Authorization::SHIPPERHQ_SERVER_TOKEN_EXPIRES_PATH);
        $this->configHelper->deleteFromConfig(Authorization::SHIPPERHQ_SERVER_PUBLIC_TOKEN_PATH);

        $this->configHelper->runScheduledCleaningNow();

        $result = false;
        if (!empty($this->authHelper->getSecretToken())) {
            $result = true;
            $message = __('ShipperHQ Authorization Token Successfully Updated');
        } else {
            $message = __('ShipperHQ Authorization Token Could Not Be Updated');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => (int)$result,
            'message' => $message,
        ]);
    }
}
