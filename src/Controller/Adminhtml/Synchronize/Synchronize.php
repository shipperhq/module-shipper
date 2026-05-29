<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2014 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Controller\Adminhtml\Synchronize;

class Synchronize extends \ShipperHQ\Shipper\Controller\Adminhtml\Synchronize
{
    /**
     * Index Action for Synchronize
     * @return Void
     * */

    public function execute()
    {
        $result = $this->sychronizerFactory->create()->synchronizeData();
        if (empty($result)) {
            $message = __('ShipperHQ was unable to verify a connection, please contact support.');
            $this->messageManager->addError($message);
        } elseif (array_key_exists('error', $result)) {
            $message = $result['error'];
            $this->messageManager->addError($message);
        } elseif ($result != 0) {
            $message = __('Updated %1 attribute values from ShipperHQ.', $result['result']);
            $this->messageManager->addSuccess($message);
        }

        $this->_redirect('*/*/index');
    }
}
