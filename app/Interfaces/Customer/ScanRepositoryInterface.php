<?php

namespace App\Interfaces\Customer;

interface ScanRepositoryInterface
{
    public function scan($url, $websiteId, $options = []);
    public function getScanDetails($scanId);
    public function getScanHistory($request);
}
