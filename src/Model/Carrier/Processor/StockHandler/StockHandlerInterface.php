<?php

namespace ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler;

interface StockHandlerInterface
{
    public function getOriginInstock($origin, $item, $product);

    public function getInstock($item, $product);

    public function getOriginInventoryCount($origin, $item, $product);

    public function getInventoryCount($item, $product);

    public function getOriginAvailabilityDate($origin, $item, $product);

    public function getAvailabilityDate($item, $product);

    public function getLocationInstock($origin, $item, $product);

    public function getLocationInventoryCount($origin, $item, $product);

    public function getLocationAvailabilityDate($origin, $item, $product);
}
