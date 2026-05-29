<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2022 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

declare(strict_types=1);

namespace ShipperHQ\Shipper\Service\Backend;

use Magento\Backend\Model\Session\Quote as BackendQuoteSession;

class UnsetAdminShipData
{
    /** @var BackendQuoteSession */
    private $backendQuoteSession;

    /**
     * GetAdminShipData constructor.
     * @param BackendQuoteSession $backendQuoteSession
     */
    public function __construct(BackendQuoteSession $backendQuoteSession)
    {
        $this->backendQuoteSession = $backendQuoteSession;
    }

    public function execute()
    {
        return $this->backendQuoteSession->unsShqAdminShipData();
    }
}
