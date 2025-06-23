<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Scan\ScanRequest;
use App\Interfaces\Customer\ScanRepositoryInterface;
use App\Models\Scan;
use Illuminate\Http\Request;

class AccessibilityScanController extends Controller
{
    private $scanRepository;

    public function __construct(ScanRepositoryInterface $scanRepository)
    {
        $this->scanRepository = $scanRepository;
    }

    public function scan(ScanRequest $request)
    {
        return $this->scanRepository->scan(
            $request['url'],
            $request['website_id'],
            $request
        );
    }

    public function getScanDetails($scanId)
    {
        $scan = Scan::findOrFail($scanId);
        return $this->scanRepository->getScanDetails($scan);
    }
    public function getScanHistory(Request $request)
    {
        return $this->scanRepository->getScanHistory($request);
    }
}
