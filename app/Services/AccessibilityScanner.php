<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Exception;
use App\Models\Scan;
use Carbon\Carbon;

class AccessibilityScanner
{
    private $client;
    private $crawler;
    private $url;
    private $results = [];
    private $complianceLevel = 'AA';
    private $standardVersion = '2.2';
    private $standards = ['wcag'];
    private $scannedUrls = [];
    private $maxPages = 50;
    private $baseUrl = '';
    private $scanProgress = 0;
    private $totalIssues = 0;
    private $queue = [];
    private $scanType = 'single';
    private $debug = [];

    public function __construct(
        string $standardVersion = '2.2',
        string $complianceLevel = 'AA',
        int $maxPages = 50,
        array $standards = ['wcag']
    ) {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
            'http_errors' => false,
            'allow_redirects' => [
                'max' => 5,
                'strict' => true,
                'referer' => true,
                'protocols' => ['http', 'https'],
                'track_redirects' => true
            ],
            'headers' => [
                'User-Agent' => 'AccessibilityScanner/1.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Connection' => 'keep-alive'
            ],
            'connect_timeout' => 10,
            'read_timeout' => 30,
            'cookies' => true
        ]);
        $this->standardVersion = $standardVersion;
        $this->complianceLevel = strtoupper($complianceLevel);
        $this->maxPages = $maxPages;
        $this->standards = $standards;
    }

    /**
     * Scan a URL or entire website for accessibility issues
     *
     * @param string $url The URL to scan
     * @param bool $scanEntireSite Whether to scan the entire site
     * @param array $options Additional scan options
     * @return array The scan results
     */
    public function scan(string $url, bool $scanEntireSite = false, array $options = []): array
    {
        $this->resetScanState();
        $this->scanType = $scanEntireSite ? 'site' : 'single';
        $this->baseUrl = $this->normalizeUrl($url);

        // Get website ID from options or throw exception if not provided
        if (!isset($options['website_id'])) {
            throw new Exception('Website ID is required for scanning');
        }

        $results = $scanEntireSite ? $this->scanSite($url, $options) : $this->scanSinglePage($url);

        // Save scan results to database
        $this->saveScanResults($url, $results, $options['website_id']);

        return $results;
    }

    /**
     * Reset the scanner state for a new scan
     */
    private function resetScanState(): void
    {
        $this->scannedUrls = [];
        $this->scanProgress = 0;
        $this->totalIssues = 0;
        $this->queue = [];
        $this->results = [];
        $this->debug = [];
    }

    /**
     * Normalize URL by removing trailing slashes and query parameters
     */
    private function normalizeUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            throw new Exception('Invalid URL format');
        }

        // Ensure scheme is set
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';

        // Build base URL
        $normalized = $scheme . '://' . $parsedUrl['host'];
        if (!empty($parsedUrl['port'])) {
            $normalized .= ':' . $parsedUrl['port'];
        }

        // Add path if exists
        if (!empty($parsedUrl['path'])) {
            // Remove trailing slash except for root path
            $path = rtrim($parsedUrl['path'], '/');
            if (empty($path)) {
                $path = '/';
            }
            $normalized .= $path;
        }

        return $normalized;
    }

    /**
     * Scan an entire website for accessibility issues
     *
     * @param string $startUrl The starting URL for the site scan
     * @param array $options Scan options
     * @return array The combined scan results
     */
    private function scanSite(string $startUrl, array $options = []): array
    {
        $siteResults = [
            'scan_id' => uniqid('scan_'),
            'base_url' => $this->baseUrl,
            'start_time' => date('Y-m-d H:i:s'),
            'status' => 'in_progress',
            'pages_scanned' => 0,
            'pages_with_issues' => 0,
            'total_issues' => 0,
            'max_pages' => $this->maxPages,
            'page_results' => [],
            'debug_info' => [],
            'summary' => [
                'errors' => 0,
                'warnings' => 0,
                'notices' => 0
            ],
            'scan_options' => array_merge([
                'scan_type' => 'site',
                'wcag_version' => $this->standardVersion,
                'compliance_level' => $this->complianceLevel
            ], $options)
        ];

        // Add start URL to queue
        $this->queue[] = $startUrl;
        $this->debug[] = "Initial URL added to queue: $startUrl";

        // Process queue
        while (!empty($this->queue) && count($this->scannedUrls) < $this->maxPages) {
            $currentUrl = array_shift($this->queue);
            $this->debug[] = "Processing URL: $currentUrl";
            $this->debug[] = "Queue size: " . count($this->queue);
            $this->debug[] = "Scanned URLs: " . count($this->scannedUrls);

            if (!in_array($currentUrl, $this->scannedUrls)) {
                $this->processUrl($currentUrl, $siteResults);
            } else {
                $this->debug[] = "Skipping already scanned URL: $currentUrl";
            }
        }

        $siteResults['status'] = 'completed';
        $siteResults['end_time'] = date('Y-m-d H:i:s');
        $siteResults['duration'] = strtotime($siteResults['end_time']) - strtotime($siteResults['start_time']);
        $siteResults['debug_info'] = $this->debug;
        $siteResults['queue_remaining'] = count($this->queue);
        $siteResults['urls_scanned'] = $this->scannedUrls;

        return $siteResults;
    }

    /**
     * Process a single URL in the site scan
     */
    private function processUrl(string $url, array &$siteResults): void
    {
        if (in_array($url, $this->scannedUrls)) {
            $this->debug[] = "URL already scanned, skipping: $url";
            return;
        }

        $this->debug[] = "Starting scan of URL: $url";
        $this->scannedUrls[] = $url;
        $pageResult = $this->scanSinglePage($url);

        // Update site-wide results
        $siteResults['pages_scanned']++;
        $siteResults['page_results'][$url] = $pageResult;

        if (!empty($pageResult['results'])) {
            $siteResults['pages_with_issues']++;
            $summary = $pageResult['summary'] ?? ['errors' => 0, 'warnings' => 0, 'notices' => 0];
            $siteResults['summary']['errors'] += $summary['errors'];
            $siteResults['summary']['warnings'] += $summary['warnings'];
            $siteResults['summary']['notices'] += $summary['notices'];
            $siteResults['total_issues'] += $summary['errors'] + $summary['warnings'] + $summary['notices'];
        }

        if (isset($pageResult['error'])) {
            $this->debug[] = "Error scanning URL: $url - " . $pageResult['message'];
            return;
        }

        // Extract and process links
        if ($this->crawler) {
            try {
                $this->debug[] = "Extracting links from: $url";

                // Find all links including those in JavaScript and meta refreshes
                $links = [];

                // Get standard anchor links
                $links = array_merge($links, $this->crawler->filter('a')->each(function (Crawler $node) {
                    return $this->normalizeFoundUrl($node->attr('href'));
                }));

                // Get meta refresh URLs
                $links = array_merge($links, $this->crawler->filter('meta[http-equiv="refresh"]')->each(function (Crawler $node) {
                    if (preg_match('/url=([^;]*)/i', $node->attr('content'), $matches)) {
                        return $this->normalizeFoundUrl($matches[1]);
                    }
                    return null;
                }));

                // Get links from onclick attributes
                $links = array_merge($links, $this->crawler->filter('[onclick]')->each(function (Crawler $node) {
                    if (preg_match('/window\.location\.href\s*=\s*[\'"]([^\'"]*)[\'"]/', $node->attr('onclick'), $matches)) {
                        return $this->normalizeFoundUrl($matches[1]);
                    }
                    return null;
                }));

                // Filter and clean links
                $links = array_filter($links);
                $links = array_unique($links);
                $this->debug[] = "Found " . count($links) . " unique links";

                $this->processFoundLinks($links, $siteResults);
            } catch (Exception $e) {
                $this->debug[] = "Error extracting links from $url: " . $e->getMessage();
                $siteResults['errors'][] = [
                    'url' => $url,
                    'message' => 'Error extracting links: ' . $e->getMessage()
                ];
            }
        }
    }

    /**
     * Normalize a found URL to proper format
     */
    private function normalizeFoundUrl(?string $href): ?string
    {
        if (!$href) return null;

        // Clean the URL
        $href = trim($href);

        // Skip certain URL types
        if (
            empty($href) ||
            strpos($href, '#') === 0 ||
            strpos($href, 'javascript:') === 0 ||
            strpos($href, 'mailto:') === 0 ||
            strpos($href, 'tel:') === 0 ||
            strpos($href, 'sms:') === 0 ||
            strpos($href, 'data:') === 0
        ) {
            return null;
        }

        try {
            // Parse the base URL
            $baseUrlParts = parse_url($this->baseUrl);
            if (!$baseUrlParts) {
                throw new Exception('Invalid base URL');
            }

            // Handle different URL formats
            if (preg_match('/^(https?:)?\/\//', $href)) {
                // Absolute URL or protocol-relative
                return preg_replace('/^\/\//', 'https://', $href);
            } elseif (strpos($href, '/') === 0) {
                // Root-relative URL
                return $baseUrlParts['scheme'] . '://' . $baseUrlParts['host'] .
                    (isset($baseUrlParts['port']) ? ':' . $baseUrlParts['port'] : '') .
                    $href;
            } else {
                // Relative URL - combine with current base path
                $basePath = isset($baseUrlParts['path']) ? rtrim(dirname($baseUrlParts['path']), '/') : '';
                if ($basePath === '') $basePath = '/';

                return $baseUrlParts['scheme'] . '://' . $baseUrlParts['host'] .
                    (isset($baseUrlParts['port']) ? ':' . $baseUrlParts['port'] : '') .
                    $basePath . '/' . ltrim($href, '/');
            }
        } catch (Exception $e) {
            $this->debug[] = "Error normalizing URL $href: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Process found links and add valid ones to the queue
     */
    private function processFoundLinks(array $links, array &$siteResults): void
    {
        foreach ($links as $nextUrl) {
            if (!$nextUrl) continue;

            if (count($this->scannedUrls) >= $this->maxPages) {
                $this->debug[] = "Max pages reached, stopping scan";
                $siteResults['max_pages_reached'] = true;
                break;
            }

            // Normalize and validate the URL
            try {
                $normalizedUrl = $this->normalizeFoundUrl($nextUrl);
                if (!$normalizedUrl || !filter_var($normalizedUrl, FILTER_VALIDATE_URL)) {
                    $this->debug[] = "Invalid URL format: $nextUrl";
                    continue;
                }

                // Skip if URL should not be scanned
                if (!$this->shouldScanUrl($normalizedUrl, $siteResults['scan_options'])) {
                    $this->debug[] = "URL excluded by filters: $normalizedUrl";
                    continue;
                }

                // Only add internal URLs that haven't been processed
                if (
                    $this->isInternalUrl($normalizedUrl) &&
                    !in_array($normalizedUrl, $this->scannedUrls) &&
                    !in_array($normalizedUrl, $this->queue)
                ) {
                    $this->queue[] = $normalizedUrl;
                    $this->debug[] = "Added to queue: $normalizedUrl";
                } else {
                    $this->debug[] = "Skipped URL: $normalizedUrl (internal: " .
                        ($this->isInternalUrl($normalizedUrl) ? 'yes' : 'no') . ")";
                }
            } catch (Exception $e) {
                $this->debug[] = "Error processing URL $nextUrl: " . $e->getMessage();
            }
        }
    }

    /**
     * Check if URL should be scanned based on include/exclude patterns
     */
    private function shouldScanUrl(string $url, array $options): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';

        // Check exclude paths
        if (!empty($options['exclude_paths'])) {
            foreach ($options['exclude_paths'] as $excludePath) {
                if (strpos($path, $excludePath) === 0) {
                    return false;
                }
            }
        }

        // Check include paths
        if (!empty($options['include_paths'])) {
            $shouldInclude = false;
            foreach ($options['include_paths'] as $includePath) {
                if (strpos($path, $includePath) === 0) {
                    $shouldInclude = true;
                    break;
                }
            }
            return $shouldInclude;
        }

        return true;
    }

    /**
     * Check if URL belongs to the same site
     */
    private function isInternalUrl(string $url): bool
    {
        // Parse URLs
        $baseUrlParts = parse_url($this->baseUrl);
        $urlParts = parse_url($url);

        // Check if URL is valid and has host
        if (!$urlParts || empty($urlParts['host'])) {
            return false;
        }

        // Compare hosts
        return $urlParts['host'] === $baseUrlParts['host'];
    }

    /**
     * Scan a single page for accessibility issues
     */
    private function scanSinglePage(string $url): array
    {
        try {
            $this->debug[] = "Attempting to fetch URL: $url";

            try {
                $response = $this->client->get($url);
                $statusCode = $response->getStatusCode();
            } catch (Exception $e) {
                $this->debug[] = "HTTP request failed for $url: " . $e->getMessage();
                throw new Exception("Failed to fetch URL: " . $e->getMessage());
            }

            if ($statusCode !== 200) {
                $this->debug[] = "Received non-200 status code ($statusCode) for URL: $url";
                return [
                    'url' => $url,
                    'error' => true,
                    'message' => "HTTP error: {$statusCode}",
                    'summary' => ['errors' => 1, 'warnings' => 0, 'notices' => 0]
                ];
            }

            // Get content type and verify it's HTML
            $contentType = $response->getHeaderLine('Content-Type');
            if (!preg_match('/text\/html|application\/xhtml\+xml/', $contentType)) {
                $this->debug[] = "Invalid content type ($contentType) for URL: $url";
                return [
                    'url' => $url,
                    'error' => true,
                    'message' => "Invalid content type: {$contentType}",
                    'summary' => ['errors' => 1, 'warnings' => 0, 'notices' => 0]
                ];
            }

            $html = (string) $response->getBody();

            // Validate HTML content
            if (empty($html)) {
                $this->debug[] = "Empty HTML content received for URL: $url";
                throw new Exception('Empty HTML content received');
            }

            // Validate URL format
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid URL format');
            }

            try {
                $this->crawler = new Crawler($html, $url);  // Pass URL directly in constructor
            } catch (Exception $e) {
                throw new Exception('Failed to create Crawler: ' . $e->getMessage());
            }

            $this->url = $url;
            $this->results = [];

            $this->runAccessibilityChecks();

            return [
                'url' => $url,
                'timestamp' => date('Y-m-d H:i:s'),
                'status_code' => $statusCode,
                'content_type' => $response->getHeaderLine('Content-Type'),
                'standard_version' => "WCAG {$this->standardVersion}",
                'compliance_level' => $this->complianceLevel,
                'results' => $this->results,
                'summary' => $this->generateSummary()
            ];
        } catch (Exception $e) {
            $this->debug[] = "Error scanning $url: " . $e->getMessage();
            return [
                'url' => $url,
                'error' => true,
                'message' => $e->getMessage(),
                'summary' => ['errors' => 1, 'warnings' => 0, 'notices' => 0]
            ];
        }
    }

    /**
     * Generate a summary of the scan results
     *
     * @return array Summary of issues found
     */
    private function generateSummary(): array
    {
        $summary = ['errors' => 0, 'warnings' => 0, 'notices' => 0];

        foreach ($this->results as $check) {
            if (isset($check['issues'])) {
                foreach ($check['issues'] as $issue) {
                    $type = $issue['type'] ?? 'notice';
                    $summary[$type . 's']++;
                }
            }
        }

        return $summary;
    }

    /**
     * Run all accessibility checks for the current page
     */
    private function runAccessibilityChecks(): void
    {
        // Run WCAG checks first
        if (in_array('wcag', $this->standards)) {
            $this->checkPerceivable();
            $this->checkOperable();
            $this->checkUnderstandable();
            $this->checkRobust();

            if ($this->standardVersion === '2.2') {
                $this->checkWCAG22Features();
            }
        }

        // Run compliance standard specific checks
        $this->runComplianceChecks();
    }

    /**
     * Run checks for different compliance standards
     */
    private function runComplianceChecks(): void
    {
        if (in_array('section508', $this->standards)) {
            $this->checkSection508();
        }
        if (in_array('ada', $this->standards)) {
            $this->checkADA();
        }
        if (in_array('aoda', $this->standards)) {
            $this->checkAODA();
        }
        if (in_array('en301549', $this->standards)) {
            $this->checkEN301549();
        }
    }

    private function checkSection508(): void
    {
        $issues = [];

        // Check software applications and operating systems
        $this->checkKeyboardAccess($issues);
        $this->checkTimeouts($issues);
        $this->checkFlashing($issues);

        // Check web-based intranet and internet information
        $this->checkMultimediaAccessibility($issues);
        $this->checkColorCoding($issues);

        $this->results['section_508'] = [
            'title' => 'Section 508 Compliance',
            'issues' => array_merge($issues, $this->checkAccessibilityPolicy())
        ];
    }

    private function checkADA(): void
    {
        $this->results['ada'] = [
            'title' => 'ADA Compliance',
            'issues' => array_merge(
                $this->checkAccessibilityStatement(),
                $this->checkUserCustomization(),
                $this->checkAssistiveTechnology()
            )
        ];
    }

    private function checkAODA(): void
    {
        $this->results['aoda'] = [
            'title' => 'AODA Compliance',
            'issues' => $this->checkAccessibilityPlan()
        ];
    }

    private function checkEN301549(): void
    {
        $this->results['en301549'] = [
            'title' => 'EN 301 549 Compliance',
            'issues' => array_merge(
                $this->checkICTFunctionality(),
                $this->checkBiometrics(),
                $this->checkStandardization(),
                $this->checkDocumentationAccessibility()
            )
        ];
    }

    private function checkWCAG22Features(): void
    {
        if ($this->standardVersion !== '2.2') {
            return;
        }

        // 2.4.11 Focus Appearance (AA)
        $this->checkFocusAppearance();

        // 2.4.12 Focus Not Obscured (AA)
        $this->checkFocusNotObscured();

        // 2.5.7 Dragging Movements (AA)
        $this->checkDraggingMovements();

        // 2.5.8 Target Size (AA)
        $this->checkTargetSize();

        // 3.2.6 Consistent Help (A)
        $this->checkConsistentHelp();

        // 3.3.7 Redundant Entry (A)
        $this->checkRedundantEntry();

        // 3.3.8 Accessible Authentication (AA)
        $this->checkAccessibleAuthentication();

        // 3.3.9 Redundant Entry (AAA)
        if ($this->complianceLevel === 'AAA') {
            $this->checkRedundantEntryEnhanced();
        }
    }

    private function checkFocusAppearance(): void
    {
        $elements = $this->crawler->filter(':focus');
        $issues = [];

        $elements->each(function (Crawler $element) use (&$issues) {
            // Check focus indicator contrast and size
            $focusStyles = $this->getComputedStyles($element);
            if (!$this->meetsFocusRequirements($focusStyles)) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '2.4.11',
                    'message' => 'Focus indicator does not meet minimum contrast or size requirements',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['focus_appearance'] = [
            'title' => 'Focus Appearance',
            'wcag' => '2.4.11',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkAccessibleAuthentication(): void
    {
        $forms = $this->crawler->filter('form');
        $issues = [];

        $forms->each(function (Crawler $form) use (&$issues) {
            $passwordInputs = $form->filter('input[type="password"]');
            if ($passwordInputs->count() > 0) {
                // Check for cognitive-function-test-free alternative
                $alternatives = $form->filter('[data-auth-alternative]');
                if ($alternatives->count() === 0) {
                    $issues[] = [
                        'type' => 'error',
                        'code' => '3.3.8',
                        'message' => 'Authentication process requires cognitive function test without alternative',
                        'element' => $form->outerHtml()
                    ];
                }
            }
        });

        $this->results['accessible_authentication'] = [
            'title' => 'Accessible Authentication',
            'wcag' => '3.3.8',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    // Add other WCAG 2.2 check methods...

    private function checkSection508Compliance(): void
    {
        $issues = [];

        // Check software applications and operating systems
        $this->checkKeyboardAccess($issues);
        $this->checkTimeouts($issues);
        $this->checkFlashing($issues);

        // Check web-based intranet and internet information
        $this->checkMultimediaAccessibility($issues);
        $this->checkColorCoding($issues);

        $this->results['section_508'] = [
            'title' => 'Section 508 Compliance',
            'issues' => $issues
        ];
    }

    private function checkADACompliance(): void
    {
        $issues = array_merge(
            $this->checkAccessibilityStatement(),
            $this->checkUserCustomization(),
            $this->checkAssistiveTechnology()
        );

        $this->results['ada'] = [
            'title' => 'ADA Compliance',
            'issues' => $issues
        ];
    }

    private function checkEN301549Compliance(): void
    {
        $issues = array_merge(
            $this->checkICTFunctionality(),
            $this->checkBiometrics(),
            $this->checkStandardization()
        );

        $this->results['en301549'] = [
            'title' => 'EN 301 549 Compliance',
            'issues' => $issues
        ];
    }

    private function checkPerceivable()
    {
        // 1.1 Text Alternatives (Level A)
        $this->checkImageAltTexts();
        $this->checkMultimediaAlternatives();

        // 1.2 Time-based Media (Level A, AA, AAA)
        if ($this->complianceLevel !== 'A') {
            $this->checkCaptions();
            $this->checkAudioDescriptions();
        }

        // 1.3 Adaptable (Level A)
        $this->checkHeadingStructure();
        $this->checkFormLabels();
        $this->checkOrientationSupport(); // WCAG 2.1

        // 1.4 Distinguishable
        $this->checkColorContrast();
        if ($this->complianceLevel !== 'A') {
            $this->checkTextSpacing(); // WCAG 2.1
            $this->checkContentHover(); // WCAG 2.1
        }
    }

    private function checkOperable()
    {
        // 2.1 Keyboard Accessible
        $this->checkKeyboardNavigation();
        if ($this->standardVersion >= '2.1') {
            $this->checkCharacterKeyShortcuts(); // WCAG 2.1
        }

        // 2.2 Enough Time
        $this->checkTimingAdjustable();

        // 2.3 Seizures and Physical Reactions
        $this->checkFlashingContent();

        // 2.4 Navigable
        $this->checkPageTitle();
        $this->checkHeadingStructure();
        $this->checkLandmarks();

        // 2.5 Input Modalities (WCAG 2.1)
        if ($this->standardVersion >= '2.1') {
            $this->checkPointerGestures();
            $this->checkPointerCancellation();
            $this->checkTargetSize();
        }
    }

    private function checkUnderstandable()
    {
        // 3.1 Readable
        $this->checkLanguageAttributes();
        if ($this->complianceLevel !== 'A') {
            $this->checkUnusualWords();
            $this->checkAbbreviations();
        }

        // 3.2 Predictable
        $this->checkConsistentNavigation();
        $this->checkConsistentIdentification();

        // 3.3 Input Assistance
        $this->checkFormValidation();
        $this->checkErrorSuggestion();
        if ($this->complianceLevel === 'AAA') {
            $this->checkErrorPrevention();
        }
    }

    private function checkRobust()
    {
        // 4.1 Compatible
        $this->checkValidHTML();
        $this->checkARIAUsage();
        if ($this->standardVersion >= '2.1') {
            $this->checkStatusMessages(); // WCAG 2.1
        }
    }

    private function checkAODACompliance()
    {
        // AODA specific requirements
        $this->results['aoda'] = [
            'title' => 'AODA Compliance',
            'issues' => $this->checkAccessibilityPlan()
        ];
    }

    private function checkImageAltTexts()
    {
        $images = $this->crawler->filter('img');
        $issues = [];

        $images->each(function (Crawler $image) use (&$issues) {
            if (!$image->attr('alt')) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '1.1.1',
                    'message' => 'Image missing alt text',
                    'element' => $image->outerHtml()
                ];
            }
        });

        $this->results['image_alt_texts'] = [
            'title' => 'Image Alt Texts',
            'wcag' => '1.1.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkHeadingStructure()
    {
        $headings = $this->crawler->filter('h1, h2, h3, h4, h5, h6');
        $issues = [];
        $previousLevel = 0;

        $headings->each(function (Crawler $heading) use (&$issues, &$previousLevel) {
            $currentLevel = (int)substr($heading->nodeName(), 1);
            if ($currentLevel - $previousLevel > 1) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '1.3.1',
                    'message' => "Skipped heading level from H$previousLevel to H$currentLevel",
                    'element' => $heading->outerHtml()
                ];
            }
            $previousLevel = $currentLevel;
        });

        $this->results['heading_structure'] = [
            'title' => 'Heading Structure',
            'wcag' => '1.3.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkFormLabels()
    {
        $inputs = $this->crawler->filter('input:not([type="hidden"]), select, textarea');
        $issues = [];

        $inputs->each(function (Crawler $input) use (&$issues) {
            $id = $input->attr('id');
            if ($id) {
                $label = $this->crawler->filter("label[for='$id']");
                if ($label->count() === 0) {
                    $issues[] = [
                        'type' => 'error',
                        'code' => '1.3.1',
                        'message' => 'Form control missing associated label',
                        'element' => $input->outerHtml()
                    ];
                }
            }
        });

        $this->results['form_labels'] = [
            'title' => 'Form Labels',
            'wcag' => '1.3.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkLanguageAttributes()
    {
        $html = $this->crawler->filter('html');
        $issues = [];

        if (!$html->attr('lang')) {
            $issues[] = [
                'type' => 'error',
                'code' => '3.1.1',
                'message' => 'HTML element missing lang attribute',
                'element' => $html->outerHtml()
            ];
        }

        $this->results['language_attributes'] = [
            'title' => 'Language Attributes',
            'wcag' => '3.1.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkKeyboardNavigation()
    {
        $clickableElements = $this->crawler->filter('a, button, [role="button"], [onclick]');
        $issues = [];

        $clickableElements->each(function (Crawler $element) use (&$issues) {
            if ($element->attr('tabindex') === '-1' || $element->attr('disabled') !== null) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '2.1.1',
                    'message' => 'Interactive element might not be keyboard accessible',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['keyboard_navigation'] = [
            'title' => 'Keyboard Navigation',
            'wcag' => '2.1.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkARIAUsage()
    {
        $ariaElements = $this->crawler->filter('[aria-label], [aria-describedby], [aria-labelledby], [role]');
        $issues = [];

        $ariaElements->each(function (Crawler $element) use (&$issues) {
            // Check for common ARIA mistakes
            if ($element->attr('aria-hidden') === 'true' && $element->filter('a, button, input, select, textarea')->count() > 0) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '4.1.2',
                    'message' => 'Interactive elements should not be hidden with aria-hidden',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['aria_usage'] = [
            'title' => 'ARIA Usage',
            'wcag' => '4.1.2',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkMultimediaAlternatives()
    {
        $multimedia = $this->crawler->filter('video, audio');
        $issues = [];

        $multimedia->each(function (Crawler $element) use (&$issues) {
            if (!$element->filter('track[kind="captions"], track[kind="descriptions"]')->count()) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '1.2.1',
                    'message' => 'Multimedia element missing text alternatives',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['multimedia_alternatives'] = [
            'title' => 'Multimedia Alternatives',
            'wcag' => '1.2.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkCaptions()
    {
        $videos = $this->crawler->filter('video');
        $issues = [];

        $videos->each(function (Crawler $video) use (&$issues) {
            if (!$video->filter('track[kind="captions"]')->count()) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '1.2.2',
                    'message' => 'Video missing captions',
                    'element' => $video->outerHtml()
                ];
            }
        });

        $this->results['captions'] = [
            'title' => 'Captions',
            'wcag' => '1.2.2',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkAudioDescriptions()
    {
        $videos = $this->crawler->filter('video');
        $issues = [];

        $videos->each(function (Crawler $video) use (&$issues) {
            if (!$video->filter('track[kind="descriptions"]')->count()) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '1.2.3',
                    'message' => 'Video missing audio descriptions',
                    'element' => $video->outerHtml()
                ];
            }
        });

        $this->results['audio_descriptions'] = [
            'title' => 'Audio Descriptions',
            'wcag' => '1.2.3',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkOrientationSupport()
    {
        $restrictiveElements = $this->crawler->filter('[style*="orientation"], [style*="-orientation"]');
        $issues = [];

        $restrictiveElements->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'code' => '1.3.4',
                'message' => 'Content may have orientation restrictions',
                'element' => $element->outerHtml()
            ];
        });

        $this->results['orientation_support'] = [
            'title' => 'Orientation Support',
            'wcag' => '1.3.4',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkTextSpacing()
    {
        $textElements = $this->crawler->filter('p, h1, h2, h3, h4, h5, h6, span, div');
        $issues = [];

        $textElements->each(function (Crawler $element) use (&$issues) {
            $style = $element->attr('style');
            $class = $element->attr('class');

            // Check for potential contrast issues based on CSS properties
            if ($style && (
                strpos($style, 'color') !== false ||
                strpos($style, 'background') !== false
            )) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '1.4.3',
                    'message' => 'Text might not have sufficient contrast ratio (4.5:1 for normal text, 3:1 for large text)',
                    'element' => $element->outerHtml(),
                    'recommendation' => 'Verify contrast ratio using a color contrast analyzer'
                ];
            }
        });

        $this->results['text_spacing'] = [
            'title' => 'Text Spacing',
            'wcag' => '1.4.12',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkCharacterKeyShortcuts()
    {
        $elementsWithKeyboard = $this->crawler->filter('[accesskey], [onkeypress]');
        $issues = [];

        $elementsWithKeyboard->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'code' => '2.1.4',
                'message' => 'Character key shortcuts should be configurable',
                'element' => $element->outerHtml()
            ];
        });

        $this->results['character_key_shortcuts'] = [
            'title' => 'Character Key Shortcuts',
            'wcag' => '2.1.4',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkTargetSize()
    {
        $clickableElements = $this->crawler->filter('a, button, [role="button"], input[type="submit"]');
        $issues = [];

        $clickableElements->each(function (Crawler $element) use (&$issues) {
            $style = $element->attr('style');
            if ($style && (
                strpos($style, 'width') !== false ||
                strpos($style, 'height') !== false
            )) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '2.5.5',
                    'message' => 'Target size should be at least 44x44 pixels',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['target_size'] = [
            'title' => 'Target Size',
            'wcag' => '2.5.5',
            'level' => 'AAA',
            'issues' => $issues
        ];
    }

    private function checkStatusMessages()
    {
        $statusElements = $this->crawler->filter('[role="status"], [role="alert"], [aria-live]');
        $issues = [];

        $statusElements->each(function (Crawler $element) use (&$issues) {
            if (!$element->attr('aria-live')) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '4.1.3',
                    'message' => 'Status message should have appropriate ARIA live region',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['status_messages'] = [
            'title' => 'Status Messages',
            'wcag' => '4.1.3',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkAccessibilityPolicy()
    {
        $issues = [];

        // Check for accessibility policy link
        $policyLink = $this->crawler->filter('a')->reduce(function (Crawler $node) {
            return stripos($node->text(), 'accessibility') !== false &&
                (stripos($node->text(), 'policy') !== false ||
                    stripos($node->text(), 'statement') !== false);
        });

        if ($policyLink->count() === 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'No accessibility policy link found',
                'recommendation' => 'Add an accessibility policy page link in the footer'
            ];
        }

        return $issues;
    }

    private function checkAccessibilityStatement()
    {
        $issues = [];

        // Check for accessibility statement
        $statement = $this->crawler->filter('a')->reduce(function (Crawler $node) {
            return stripos($node->text(), 'accessibility statement') !== false;
        });

        if ($statement->count() === 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'No accessibility statement found',
                'recommendation' => 'Add an accessibility statement page'
            ];
        }

        return $issues;
    }

    private function checkAccessibilityPlan()
    {
        $issues = [];

        // AODA requires a multi-year accessibility plan
        $plan = $this->crawler->filter('a')->reduce(function (Crawler $node) {
            return stripos($node->text(), 'accessibility plan') !== false;
        });

        if ($plan->count() === 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'No multi-year accessibility plan found',
                'recommendation' => 'Add a multi-year accessibility plan as required by AODA'
            ];
        }

        return $issues;
    }

    private function checkDocumentationAccessibility()
    {
        $issues = [];

        // Check for accessible documentation formats
        $docs = $this->crawler->filter('a[href$=".pdf"], a[href$=".doc"], a[href$=".docx"]');

        $docs->each(function (Crawler $doc) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Document may not be in accessible format',
                'element' => $doc->outerHtml(),
                'recommendation' => 'Ensure document is available in accessible format (HTML or tagged PDF)'
            ];
        });

        return $issues;
    }

    private function checkColorContrast()
    {
        $textElements = $this->crawler->filter('p, h1, h2, h3, h4, h5, h6, span, div, a');
        $issues = [];

        $textElements->each(function (Crawler $element) use (&$issues) {
            $style = $element->attr('style');
            $class = $element->attr('class');

            // Check for potential contrast issues based on CSS properties
            if ($style && (
                strpos($style, 'color') !== false ||
                strpos($style, 'background') !== false
            )) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '1.4.3',
                    'message' => 'Text might not have sufficient contrast ratio (4.5:1 for normal text, 3:1 for large text)',
                    'element' => $element->outerHtml(),
                    'recommendation' => 'Verify contrast ratio using a color contrast analyzer'
                ];
            }
        });

        $this->results['color_contrast'] = [
            'title' => 'Color Contrast',
            'wcag' => '1.4.3',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkContentHover()
    {
        $hoverElements = $this->crawler->filter('[onmouseover], [onmouseenter], [title], [data-tooltip]');
        $issues = [];

        $hoverElements->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'code' => '1.4.13',
                'message' => 'Ensure hover content is dismissible, hoverable, and persistent',
                'element' => $element->outerHtml(),
                'recommendation' => 'Content appearing on hover should be dismissible, hoverable, and remain visible until dismissed'
            ];
        });

        $this->results['content_hover'] = [
            'title' => 'Content on Hover',
            'wcag' => '1.4.13',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkTimingAdjustable()
    {
        $timingElements = $this->crawler->filter('[data-timeout], meta[http-equiv="refresh"], [onclick]');
        $issues = [];

        $timingElements->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'code' => '2.2.1',
                'message' => 'Time limits should be adjustable',
                'element' => $element->outerHtml(),
                'recommendation' => 'Provide options to turn off, adjust, or extend time limits'
            ];
        });

        $this->results['timing_adjustable'] = [
            'title' => 'Timing Adjustable',
            'wcag' => '2.2.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkFlashingContent()
    {
        $flashingElements = $this->crawler->filter('video, .flash, .blink, [style*="animation"]');
        $issues = [];

        $flashingElements->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'code' => '2.3.1',
                'message' => 'Check for content that flashes more than 3 times per second',
                'element' => $element->outerHtml(),
                'recommendation' => 'Ensure no content flashes more than three times in any one-second period'
            ];
        });

        $this->results['flashing_content'] = [
            'title' => 'Flashing Content',
            'wcag' => '2.3.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkPageTitle()
    {
        $title = $this->crawler->filter('title');
        $issues = [];

        if ($title->count() === 0) {
            $issues[] = [
                'type' => 'error',
                'code' => '2.4.2',
                'message' => 'Page missing title element',
                'recommendation' => 'Add a descriptive page title'
            ];
        } else if (strlen(trim($title->text())) < 5) {
            $issues[] = [
                'type' => 'warning',
                'code' => '2.4.2',
                'message' => 'Page title might not be descriptive enough',
                'element' => $title->outerHtml(),
                'recommendation' => 'Make the page title more descriptive'
            ];
        }

        $this->results['page_title'] = [
            'title' => 'Page Title',
            'wcag' => '2.4.2',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkLandmarks()
    {
        $landmarks = $this->crawler->filter('header, nav, main, footer, aside, [role="banner"], [role="navigation"], [role="main"], [role="contentinfo"], [role="complementary"]');
        $issues = [];

        if (!$this->crawler->filter('main, [role="main"]')->count()) {
            $issues[] = [
                'type' => 'error',
                'code' => '1.3.1',
                'message' => 'No main landmark found',
                'recommendation' => 'Add a main landmark to identify the main content area'
            ];
        }

        if (!$this->crawler->filter('nav, [role="navigation"]')->count()) {
            $issues[] = [
                'type' => 'warning',
                'code' => '1.3.1',
                'message' => 'No navigation landmark found',
                'recommendation' => 'Add a navigation landmark to identify navigation regions'
            ];
        }

        $this->results['landmarks'] = [
            'title' => 'Landmarks',
            'wcag' => '1.3.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkPointerGestures()
    {
        $gestureElements = $this->crawler->filter('[ontouchstart], [ongesturestart], [ongesturechange], [ongestureend]');
        $issues = [];

        $gestureElements->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'code' => '2.5.1',
                'message' => 'Ensure pointer gestures have alternatives',
                'element' => $element->outerHtml(),
                'recommendation' => 'Provide alternative methods for gesture-based interactions'
            ];
        });

        $this->results['pointer_gestures'] = [
            'title' => 'Pointer Gestures',
            'wcag' => '2.5.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkPointerCancellation()
    {
        $pointerElements = $this->crawler->filter('[onmousedown], [ontouchstart], [onclick]');
        $issues = [];

        $pointerElements->each(function (Crawler $element) use (&$issues) {
            if ($element->attr('onmouseup') === null) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '2.5.2',
                    'message' => 'Ensure pointer operations can be cancelled',
                    'element' => $element->outerHtml(),
                    'recommendation' => 'Implement up-event cancellation for pointer operations'
                ];
            }
        });

        $this->results['pointer_cancellation'] = [
            'title' => 'Pointer Cancellation',
            'wcag' => '2.5.2',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkUnusualWords()
    {
        $textContent = $this->crawler->filter('body')->text();
        $issues = [];

        // Check for potential jargon or technical terms
        $technicalTerms = ['API', 'SDK', 'HTTP', 'DNS', 'SQL']; // Example terms
        foreach ($technicalTerms as $term) {
            if (stripos($textContent, $term) !== false) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '3.1.3',
                    'message' => "Technical term '$term' may need explanation",
                    'recommendation' => "Provide definition or context for '$term'"
                ];
            }
        }

        $this->results['unusual_words'] = [
            'title' => 'Unusual Words',
            'wcag' => '3.1.3',
            'level' => 'AAA',
            'issues' => $issues
        ];
    }

    private function checkAbbreviations()
    {
        $textElements = $this->crawler->filter('p, li, h1, h2, h3, h4, h5, h6');
        $issues = [];

        $textElements->each(function (Crawler $element) use (&$issues) {
            $text = $element->text();
            if (preg_match('/\b[A-Z]{2,}\b/', $text, $matches)) {
                foreach ($matches as $abbr) {
                    if (!$element->filter("abbr[title]")->count()) {
                        $issues[] = [
                            'type' => 'warning',
                            'code' => '3.1.4',
                            'message' => "Abbreviation '$abbr' found without explanation",
                            'element' => $element->outerHtml(),
                            'recommendation' => "Use <abbr> tag with title attribute for '$abbr'"
                        ];
                    }
                }
            }
        });

        $this->results['abbreviations'] = [
            'title' => 'Abbreviations',
            'wcag' => '3.1.4',
            'level' => 'AAA',
            'issues' => $issues
        ];
    }

    private function checkConsistentNavigation()
    {
        $navigationElements = $this->crawler->filter('nav, [role="navigation"]');
        $issues = [];

        if ($navigationElements->count() > 1) {
            $firstNav = $navigationElements->first()->html();
            $navigationElements->each(function (Crawler $nav) use (&$issues, $firstNav) {
                if ($nav->html() !== $firstNav) {
                    $issues[] = [
                        'type' => 'warning',
                        'code' => '3.2.3',
                        'message' => 'Navigation may be inconsistent across pages',
                        'element' => $nav->outerHtml(),
                        'recommendation' => 'Ensure navigation patterns are consistent throughout the site'
                    ];
                }
            });
        }

        $this->results['consistent_navigation'] = [
            'title' => 'Consistent Navigation',
            'wcag' => '3.2.3',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkConsistentIdentification()
    {
        $functionalElements = $this->crawler->filter('button, a, [role="button"]');
        $issues = [];
        $elementMap = [];

        $functionalElements->each(function (Crawler $element) use (&$issues, &$elementMap) {
            $text = trim($element->text());
            if (isset($elementMap[$text]) && $element->nodeName() !== $elementMap[$text]) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '3.2.4',
                    'message' => 'Inconsistent identification of functional elements',
                    'element' => $element->outerHtml(),
                    'recommendation' => 'Use consistent elements for identical functionality'
                ];
            }
            $elementMap[$text] = $element->nodeName();
        });

        $this->results['consistent_identification'] = [
            'title' => 'Consistent Identification',
            'wcag' => '3.2.4',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkFormValidation()
    {
        $forms = $this->crawler->filter('form');
        $issues = [];

        $forms->each(function (Crawler $form) use (&$issues) {
            $requiredInputs = $form->filter('[required], [aria-required="true"]');

            if ($requiredInputs->count() > 0 && !$form->filter('[aria-invalid]')->count()) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '3.3.1',
                    'message' => 'Form validation may not be properly implemented',
                    'element' => $form->outerHtml(),
                    'recommendation' => 'Implement proper error identification using aria-invalid and error messages'
                ];
            }
        });

        $this->results['form_validation'] = [
            'title' => 'Form Validation',
            'wcag' => '3.3.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkErrorSuggestion()
    {
        $forms = $this->crawler->filter('form');
        $issues = [];

        $forms->each(function (Crawler $form) use (&$issues) {
            if (!$form->filter('[role="alert"], [aria-errormessage]')->count()) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '3.3.3',
                    'message' => 'Form may not provide error suggestions',
                    'element' => $form->outerHtml(),
                    'recommendation' => 'Provide suggestions for correcting input errors'
                ];
            }
        });

        $this->results['error_suggestion'] = [
            'title' => 'Error Suggestion',
            'wcag' => '3.3.3',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkErrorPrevention()
    {
        $forms = $this->crawler->filter('form');
        $issues = [];

        $forms->each(function (Crawler $form) use (&$issues) {
            // Check for financial, legal, or data modification forms
            $sensitiveInputs = $form->filter('input[type="number"], input[name="payment"], input[name="legal"], input[name="agreement"]');

            if ($sensitiveInputs->count() > 0) {
                if (
                    !$form->filter('button[type="submit"]')->count() ||
                    !$form->filter('button[type="reset"]')->count()
                ) {
                    $issues[] = [
                        'type' => 'warning',
                        'code' => '3.3.4',
                        'message' => 'Form with sensitive data may not have proper error prevention',
                        'element' => $form->outerHtml(),
                        'recommendation' => 'Implement reversible submissions, data checking, and confirmation mechanisms'
                    ];
                }
            }
        });

        $this->results['error_prevention'] = [
            'title' => 'Error Prevention',
            'wcag' => '3.3.4',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkValidHTML()
    {
        $html = $this->crawler->html();
        $issues = [];

        // Check for common HTML validity issues
        if (strpos($html, '</') === false) {
            $issues[] = [
                'type' => 'error',
                'code' => '4.1.1',
                'message' => 'HTML may be malformed',
                'recommendation' => 'Ensure proper HTML closing tags'
            ];
        }

        // Check for unique IDs
        $elements = $this->crawler->filter('[id]');
        $ids = [];
        $elements->each(function (Crawler $element) use (&$issues, &$ids) {
            $id = $element->attr('id');
            if (isset($ids[$id])) {
                $issues[] = [
                    'type' => 'error',
                    'code' => '4.1.1',
                    'message' => "Duplicate ID found: '$id'",
                    'element' => $element->outerHtml(),
                    'recommendation' => 'Ensure all IDs are unique'
                ];
            }
            $ids[$id] = true;
        });

        $this->results['valid_html'] = [
            'title' => 'Valid HTML',
            'wcag' => '4.1.1',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    // Helper methods for WCAG 2.2 features
    private function getComputedStyles(Crawler $element): array
    {
        // This is a simplified version - in production, you'd want to use a proper CSS parser
        $style = $element->attr('style') ?? '';
        $computedStyles = [];

        // Parse inline styles
        $styles = explode(';', $style);
        foreach ($styles as $stylePair) {
            $parts = explode(':', $stylePair);
            if (count($parts) === 2) {
                $computedStyles[trim($parts[0])] = trim($parts[1]);
            }
        }

        return $computedStyles;
    }

    private function meetsFocusRequirements(array $styles): bool
    {
        // Check minimum contrast ratio (3:1) and size requirements
        $minWidth = $styles['width'] ?? '0';
        $minHeight = $styles['height'] ?? '0';

        // Convert to pixels if possible
        $width = (int) str_replace(['px', 'em', 'rem', '%'], '', $minWidth);
        $height = (int) str_replace(['px', 'em', 'rem', '%'], '', $minHeight);

        return $width >= 2 && $height >= 2; // Minimum focus indicator thickness
    }

    private function checkFocusNotObscured(): void
    {
        $focusableElements = $this->crawler->filter('a, button, input, select, textarea, [tabindex]');
        $issues = [];

        $focusableElements->each(function (Crawler $element) use (&$issues) {
            // Check if element might be obscured when focused
            if ($element->attr('style') && (
                strpos($element->attr('style'), 'overflow: hidden') !== false ||
                strpos($element->attr('style'), 'visibility: hidden') !== false
            )) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '2.4.12',
                    'message' => 'Focus indicator may be obscured',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['focus_not_obscured'] = [
            'title' => 'Focus Not Obscured',
            'wcag' => '2.4.12',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkDraggingMovements(): void
    {
        $draggableElements = $this->crawler->filter('[draggable="true"]');
        $issues = [];

        $draggableElements->each(function (Crawler $element) use (&$issues) {
            if (!$element->filter('[role="button"]')->count()) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '2.5.7',
                    'message' => 'Dragging movement should have alternative action method',
                    'element' => $element->outerHtml()
                ];
            }
        });

        $this->results['dragging_movements'] = [
            'title' => 'Dragging Movements',
            'wcag' => '2.5.7',
            'level' => 'AA',
            'issues' => $issues
        ];
    }

    private function checkConsistentHelp(): void
    {
        $helpElements = $this->crawler->filter('a:contains("help"), button:contains("help"), [aria-label*="help"]');
        $issues = [];

        if ($helpElements->count() > 1) {
            $firstHelpLocation = null;
            $helpElements->each(function (Crawler $element) use (&$issues, &$firstHelpLocation) {
                if (!$firstHelpLocation) {
                    $firstHelpLocation = $element->attr('class');
                } else if ($element->attr('class') !== $firstHelpLocation) {
                    $issues[] = [
                        'type' => 'warning',
                        'code' => '3.2.6',
                        'message' => 'Help mechanism not consistently located',
                        'element' => $element->outerHtml()
                    ];
                }
            });
        }

        $this->results['consistent_help'] = [
            'title' => 'Consistent Help',
            'wcag' => '3.2.6',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkRedundantEntry(): void
    {
        $forms = $this->crawler->filter('form');
        $issues = [];

        $forms->each(function (Crawler $form) use (&$issues) {
            $inputs = $form->filter('input[type="text"], input[type="email"], input[type="tel"]');
            $autofilledInputs = $inputs->reduce(function (Crawler $input) {
                return $input->attr('autocomplete') !== null;
            });

            if ($autofilledInputs->count() < $inputs->count()) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '3.3.7',
                    'message' => 'Form fields should use autocomplete where appropriate',
                    'element' => $form->outerHtml()
                ];
            }
        });

        $this->results['redundant_entry'] = [
            'title' => 'Redundant Entry',
            'wcag' => '3.3.7',
            'level' => 'A',
            'issues' => $issues
        ];
    }

    private function checkRedundantEntryEnhanced(): void
    {
        $forms = $this->crawler->filter('form');
        $issues = [];

        $forms->each(function (Crawler $form) use (&$issues) {
            $inputs = $form->filter('input:not([type="hidden"])');
            $savedDataInputs = $inputs->reduce(function (Crawler $input) {
                return $input->attr('data-saved') !== null || $input->attr('data-autofill') !== null;
            });

            if ($savedDataInputs->count() < $inputs->count()) {
                $issues[] = [
                    'type' => 'warning',
                    'code' => '3.3.9',
                    'message' => 'Previously entered information should be available',
                    'element' => $form->outerHtml(),
                    'recommendation' => 'Implement data persistence and auto-fill capabilities for form fields'
                ];
            }
        });

        $this->results['redundant_entry_enhanced'] = [
            'title' => 'Redundant Entry Enhanced',
            'wcag' => '3.3.9',
            'level' => 'AAA',
            'issues' => $issues
        ];
    }

    private function checkFlashing(array &$issues): void
    {
        $flashingElements = $this->crawler->filter('.flash, .blink, [style*="animation"]');
        $flashingElements->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Check element for flashing content',
                'element' => $element->outerHtml()
            ];
        });
    }

    private function checkMultimediaAccessibility(array &$issues): void
    {
        $multimedia = $this->crawler->filter('video, audio');
        $multimedia->each(function (Crawler $element) use (&$issues) {
            if (!$element->filter('track[kind="captions"]')->count()) {
                $issues[] = [
                    'type' => 'error',
                    'message' => 'Multimedia content missing captions',
                    'element' => $element->outerHtml()
                ];
            }
        });
    }

    private function checkColorCoding(array &$issues): void
    {
        $elements = $this->crawler->filter('[style*="color"]');
        $elements->each(function (Crawler $element) use (&$issues) {
            if (!$element->attr('aria-label') && !$element->attr('title')) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => 'Color-coded information may need alternative indication',
                    'element' => $element->outerHtml()
                ];
            }
        });
    }

    private function checkUserCustomization(): array
    {
        $issues = [];

        // Check for text resize support
        if ($this->crawler->filter('[style*="font-size"]')->count() > 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Ensure text can be resized without breaking functionality',
                'recommendation' => 'Use relative units (em, rem) for font sizes'
            ];
        }

        return $issues;
    }

    private function checkAssistiveTechnology(): array
    {
        $issues = [];

        // Check for ARIA landmarks
        if (!$this->crawler->filter('[role="main"]')->count()) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Page may not be properly structured for assistive technology',
                'recommendation' => 'Add ARIA landmarks for main content areas'
            ];
        }

        return $issues;
    }

    private function checkICTFunctionality(): array
    {
        $issues = [];

        // Check for proper input methods
        $inputs = $this->crawler->filter('input');
        if ($inputs->count() > 0) {
            $inputs->each(function (Crawler $input) use (&$issues) {
                if (!$input->attr('type')) {
                    $issues[] = [
                        'type' => 'warning',
                        'message' => 'Input element missing type attribute',
                        'element' => $input->outerHtml()
                    ];
                }
            });
        }

        return $issues;
    }

    private function checkBiometrics(): array
    {
        $issues = [];

        // Check for biometric alternatives
        $biometricElements = $this->crawler->filter('[data-biometric]');
        if ($biometricElements->count() > 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Ensure alternative authentication methods are available',
                'recommendation' => 'Provide non-biometric authentication options'
            ];
        }

        return $issues;
    }

    private function checkStandardization(): array
    {
        $issues = [];

        // Check for standard HTML elements
        $customElements = $this->crawler->filter(':not(standard)');
        if ($customElements->count() > 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Custom elements should follow standard patterns',
                'recommendation' => 'Use standard HTML elements where possible'
            ];
        }

        return $issues;
    }

    private function checkKeyboardAccess(array &$issues): void
    {
        $elements = $this->crawler->filter('a, button, input, select, [role="button"]');
        $elements->each(function (Crawler $element) use (&$issues) {
            if ($element->attr('tabindex') === '-1' || $element->attr('disabled') !== null) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => 'Element may not be keyboard accessible',
                    'element' => $element->outerHtml()
                ];
            }
        });
    }

    private function checkTimeouts(array &$issues): void
    {
        $elements = $this->crawler->filter('[data-timeout], meta[http-equiv="refresh"]');
        $elements->each(function (Crawler $element) use (&$issues) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'Page contains time limits that may need adjustment options',
                'element' => $element->outerHtml()
            ];
        });
    }

    private function saveScanResults(string $url, array $results, int $websiteId): void
    {
        $totalIssues = 0;
        $errors = 0;
        $warnings = 0;
        $notices = 0;
        $issueCategories = [];
        $wcagViolations = [];

        // Process scan results including nested page results
        if (isset($results['page_results']) && is_array($results['page_results'])) {
            // For site scans with multiple pages
            foreach ($results['page_results'] as $pageResult) {
                $this->processResultsForCounts($pageResult, $totalIssues, $errors, $warnings, $notices, $issueCategories, $wcagViolations);
            }
        } else {
            // For single page scans
            $this->processResultsForCounts($results, $totalIssues, $errors, $warnings, $notices, $issueCategories, $wcagViolations);
        }

        // Prepare compliance status for different standards
        $complianceStatus = [];
        foreach ($this->standards as $standard) {
            $complianceStatus[$standard] = [
                'checked' => true,
                'passing' => $errors === 0,
                'errors' => $errors,
                'warnings' => $warnings,
                'notices' => $notices
            ];
        }

        // Calculate scan duration
        $startTime = $results['start_time'] ?? null;
        $endTime = $results['end_time'] ?? date('Y-m-d H:i:s');
        $duration = $startTime ? (strtotime($endTime) - strtotime($startTime)) : null;

        // Create new scan record with enhanced details
        Scan::create([
            'website_id' => $websiteId,
            'scan_date' => Carbon::now(),
            'issues_found' => $totalIssues,
            'issues_resolved' => 0,
            'issues' => json_encode($results),
            'status' => 'completed',
            'wcag_version' => $this->standardVersion,
            'compliance_level' => $this->complianceLevel,
            'standards_checked' => json_encode($this->standards),
            'errors_count' => (int)$errors,
            'warnings_count' => (int)$warnings,
            'notices_count' => (int)$notices,
            'pages_scanned' => count($results['urls_scanned'] ?? [1]),
            'pages_with_issues' => $results['pages_with_issues'] ?? ($totalIssues > 0 ? 1 : 0),
            'scan_type' => $this->scanType,
            'scanned_url' => $url,
            'scan_options' => json_encode($results['scan_options'] ?? []),
            'completed_at' => Carbon::now(),
            'scan_duration' => $duration,
            'issue_categories' => json_encode($issueCategories),
            'wcag_violations' => json_encode($wcagViolations),
            'compliance_status' => json_encode($complianceStatus)
        ]);
    }

    /**
     * Helper method to process results and update counts
     */
    private function processResultsForCounts(array $results, int &$totalIssues, int &$errors, int &$warnings, int &$notices, array &$issueCategories, array &$wcagViolations): void
    {
        if (!isset($results['results']) || !is_array($results['results'])) {
            return;
        }

        foreach ($results['results'] as $category => $data) {
            if (!isset($data['issues']) || !is_array($data['issues'])) {
                continue;
            }

            $categoryCount = count($data['issues']);
            $totalIssues += $categoryCount;

            if (!isset($issueCategories[$category])) {
                $issueCategories[$category] = 0;
            }
            $issueCategories[$category] += $categoryCount;

            foreach ($data['issues'] as $issue) {
                // Count by severity
                switch ($issue['type'] ?? 'notice') {
                    case 'error':
                        $errors++;
                        break;
                    case 'warning':
                        $warnings++;
                        break;
                    default:
                        $notices++;
                }

                // Collect WCAG violations
                if (isset($issue['code'])) {
                    if (!isset($wcagViolations[$issue['code']])) {
                        $wcagViolations[$issue['code']] = 0;
                    }
                    $wcagViolations[$issue['code']]++;
                }
            }
        }
    }
}
