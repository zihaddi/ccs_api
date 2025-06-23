<?php

namespace App\Repositories\Customer;

use App\Constants\Constants;
use App\Http\Resources\Customer\Scan\ScanResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Customer\ScanRepositoryInterface;
use App\Services\AccessibilityScanner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ScanRepository implements ScanRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;

    protected $scanner;

    public function __construct(AccessibilityScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    public function scan($url, $websiteId, $request = [])
    {
        try {
            $scanOptions = array_merge([
                'exclude_paths' => [],
                'include_paths' => [],
                'follow_redirects' => true,
                'check_subdomains' => false,
                'concurrent_requests' => 2,
                'request_delay' => 500,
                'website_id' => $request['website_id']
            ], $request['options'] ?? []);

            $scanEntireSite = $request['scan_entire_site'] ?? false;
            $scanner = new AccessibilityScanner(
                $request['wcag_version'] ?? '2.2',
                $request['compliance_level'] ?? 'AA',
                $request['max_pages'] ?? 50,
                $request['standards'] ?? ['wcag']
            );
            $results = $scanner->scan(
                $url,
                $scanEntireSite,
                $scanOptions
            );
            return $this->success($results, 'Scan completed successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    public function getScanDetails($scanId)
    {
        $scan = DB::table('scans')->with('website')->where('id', $scanId)->first();

        if (!$scan) {
            return $this->error(null, 'Scan not found', Response::HTTP_NOT_FOUND, false);
        }

        $scanDetails = ScanResource::make($scan);

        return $this->success($scanDetails, 'Scan details retrieved successfully', Response::HTTP_OK, true);
    }
    public function getScanHistory($request)
    {
        $scans = DB::table('scans')->with('website')
            ->when($request->websiteId, function ($query) use ($request) {
                return $query->where('website_id', $request->websiteId);
            })
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        if ($scans->isEmpty()) {
            return $this->error(null, 'No scan history found', Response::HTTP_NOT_FOUND, false);
        }

        $scanHistory = ScanResource::collection($scans);

        return $this->success($scanHistory, 'Scan history retrieved successfully', Response::HTTP_OK, true);
    }
}
