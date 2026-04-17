<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 *
 * Rewritten 2026: Migrated from Amazon PAAPI 5 SDK + Guzzle to Amazon Creators
 * API (Jakiboy/apaapi v2.0.5 vendored in lib/apaapi/).
 *
 * This class is a thin adapter that keeps the historical call surface
 * (`searchItems($args)` / `getItems($asins)`) used by the 5 `api_*.php` call
 * sites, while internally talking to the Creators API through apaapi.
 *
 * The OAuth2 access token is cached per AmazonApi instance because apaapi
 * caches it on the Request object; we therefore reuse ONE Request instance
 * across multiple operations for the lifetime of this adapter.
 */

namespace Endcore;

use Apaapi\lib\Request;
use Apaapi\lib\Response;
use Apaapi\operations\SearchItems;
use Apaapi\operations\GetItems;

class AmazonApi
{
    /**
     * apaapi Request instance, reused across operations so the OAuth token
     * stays cached for the lifetime of this adapter instance.
     *
     * @var Request
     */
    private $request;

    /**
     * Amazon partner tag (associate tag) used for all operations.
     *
     * @var string
     */
    private $partnerTag;

    /**
     * Locale/TLD (e.g. 'de', 'com', 'co.uk').
     *
     * @var string
     */
    private $country;

    /**
     * Credentials cached so we can rebuild the Request on 401 (forced token refresh).
     */
    private $credentialId;
    private $credentialSecret;
    private $version;

    /**
     * @param string      $credentialId     Creators API credential ID
     * @param string      $credentialSecret Creators API credential secret
     * @param string      $partnerTag       Amazon Associates partner tag
     * @param string      $country          TLD part after 'amazon.' (de, com, co.uk, ...)
     * @param string|null $version          Optional explicit version override (2.1/2.2/2.3/3.1/3.2/3.3)
     */
    public function __construct(
        string $credentialId,
        string $credentialSecret,
        string $partnerTag,
        string $country,
        ?string $version = null
    ) {
        self::bootstrapAutoloader();

        $this->partnerTag       = $partnerTag;
        $this->country          = $country;
        $this->credentialId     = $credentialId;
        $this->credentialSecret = $credentialSecret;

        if ($version === null) {
            $version = self::detectVersion($country, $credentialId);
        }
        $this->version = $version;

        $this->buildRequest();
    }

    /**
     * (Re)build the Request instance. Used on construction and on forced
     * token refresh after a 401 response.
     */
    private function buildRequest() : void
    {
        $this->request = new Request($this->credentialId, $this->credentialSecret, $this->version);
        $this->request->setLocale($this->country);
    }

    /**
     * Build an AmazonApi instance from WordPress options.
     * Reads: amazon_credential_id, amazon_credential_secret,
     *        amazon_partner_id, amazon_country.
     *
     * @return self
     */
    public static function fromWpOptions() : self
    {
        $credentialId     = (string) get_option('amazon_credential_id');
        $credentialSecret = (string) get_option('amazon_credential_secret');
        $partnerTag       = (string) get_option('amazon_partner_id');
        $country          = (string) get_option('amazon_country');

        if ($country === '') {
            $country = 'de';
        }

        return new self($credentialId, $credentialSecret, $partnerTag, $country);
    }

    /**
     * Detect the Creators API credential version from country + credential format.
     *
     * Amazon issues two credential families:
     *   - Cognito (v2.x) — pre-Feb-2026, no specific prefix
     *   - LWA     (v3.x) — since Feb 2026, credential ID starts with
     *                       "amzn1.application-oa2-client."
     *
     * We pick the credential family from the ID prefix, then map the country
     * to the regional sub-version (NA/EU/FE).
     *
     * @param  string $country      TLD (without 'amazon.' prefix)
     * @param  string $credentialId Credential ID from Associates Central
     * @return string '2.1'/'2.2'/'2.3' (Cognito) or '3.1'/'3.2'/'3.3' (LWA)
     * @throws \InvalidArgumentException for unsupported locales (e.g. cn)
     */
    public static function detectVersion(string $country, string $credentialId) : string
    {
        $isLwa = strpos($credentialId, 'amzn1.application-oa2-client.') === 0;
        return self::countryToVersion($country, $isLwa);
    }

    /**
     * Map an Amazon country/TLD to the Creators API credential version.
     *
     * @param  string $country TLD (without 'amazon.' prefix)
     * @param  bool   $isLwa   True for LWA (v3.x) credentials, false for Cognito (v2.x)
     * @return string '2.1'/'3.1' (NA) | '2.2'/'3.2' (EU) | '2.3'/'3.3' (FE)
     * @throws \InvalidArgumentException for unsupported locales (e.g. cn)
     */
    public static function countryToVersion(string $country, bool $isLwa = false) : string
    {
        $country = strtolower($country);

        $na = ['com', 'ca', 'com.mx', 'com.br'];
        $eu = ['de', 'fr', 'co.uk', 'it', 'es', 'nl', 'se', 'in', 'ae', 'com.tr', 'sa', 'eg', 'pl'];
        $fe = ['co.jp', 'com.au', 'sg'];

        $major = $isLwa ? '3' : '2';

        if (in_array($country, $na, true)) {
            return "{$major}.1";
        }
        if (in_array($country, $eu, true)) {
            return "{$major}.2";
        }
        if (in_array($country, $fe, true)) {
            return "{$major}.3";
        }

        if ($country === 'cn') {
            throw new \InvalidArgumentException(
                'Amazon China (cn) is not supported by the Creators API.'
            );
        }

        throw new \InvalidArgumentException(
            sprintf('Unsupported Amazon country/TLD: "%s".', $country)
        );
    }

    /**
     * Run a SearchItems operation.
     *
     * Accepted $args keys (all optional unless noted):
     *   keywords     (string)  search keywords
     *   title        (string)  search by title
     *   searchIndex  (string)  category, default 'All'
     *   itemPage     (int)     1-based page, default 1
     *   itemCount    (int)     items per page, default 10
     *   sortBy       (string)  Relevance | Featured | NewestArrivals |
     *                          Price:LowToHigh | Price:HighToLow |
     *                          AvgCustomerReviews
     *   merchant     (string)  All | Amazon, default 'All'
     *   availability (string)  Available | IncludeOutOfStock
     *   minPrice     (int)     min price (currency minor units, i.e. cents)
     *   maxPrice     (int)     max price (currency minor units, i.e. cents)
     *   brand        (string)
     *   browseNodeId (string)
     *   resources    (array)   optional override; if omitted apaapi uses its
     *                          own sensible SearchItems defaults
     *
     * @param  array $args
     * @return array Raw JSON-decoded Creators API response
     * @throws \Exception on API error (code = HTTP status or 500)
     */
    public function searchItems(array $args = []) : array
    {
        $op = new SearchItems();
        $op->setPartnerTag($this->partnerTag);

        if (isset($args['keywords']) && $args['keywords'] !== '') {
            $op->setKeywords((string) $args['keywords']);
        }
        if (isset($args['title']) && $args['title'] !== '') {
            $op->setTitle((string) $args['title']);
        }
        if (isset($args['searchIndex']) && $args['searchIndex'] !== '') {
            $op->setSearchIndex((string) $args['searchIndex']);
        }
        if (isset($args['itemPage'])) {
            $op->setItemPage((int) $args['itemPage']);
        }
        if (isset($args['itemCount'])) {
            $op->setItemCount((int) $args['itemCount']);
        }
        if (isset($args['sortBy']) && $args['sortBy'] !== '') {
            $op->setSortBy((string) $args['sortBy']);
        }
        if (isset($args['merchant']) && $args['merchant'] !== '') {
            $op->setMerchant((string) $args['merchant']);
        }
        if (isset($args['availability']) && $args['availability'] !== '') {
            $op->setAvailability((string) $args['availability']);
        }
        if (isset($args['minPrice']) && $args['minPrice'] !== '') {
            $op->setMinPrice((int) $args['minPrice']);
        }
        if (isset($args['maxPrice']) && $args['maxPrice'] !== '') {
            $op->setMaxPrice((int) $args['maxPrice']);
        }
        if (isset($args['brand']) && $args['brand'] !== '') {
            $op->setBrand((string) $args['brand']);
        }
        if (isset($args['browseNodeId']) && $args['browseNodeId'] !== '') {
            $op->setBrowseNodeId((string) $args['browseNodeId']);
        }
        if (isset($args['resources']) && is_array($args['resources']) && !empty($args['resources'])) {
            $op->setResources($args['resources']);
        }

        return $this->execute($op);
    }

    /**
     * Run a GetItems operation.
     *
     * @param  array $asins List of ASINs
     * @return array Raw JSON-decoded Creators API response
     * @throws \Exception on API error (code = HTTP status or 500)
     */
    public function getItems(array $asins) : array
    {
        $op = new GetItems();
        $op->setPartnerTag($this->partnerTag);
        $op->setItemIds(array_values($asins));

        return $this->execute($op);
    }

    /**
     * Execute an apaapi operation and return the decoded response data.
     * Throws \Exception with the response error message on failure.
     *
     * @param  object $op apaapi Operation
     * @return array
     * @throws \Exception
     */
    private function execute($op) : array
    {
        $this->request->setPayload($op);
        // Force a fresh client per operation (payload/headers change),
        // but the OAuth token cached on $this->request is reused.
        $this->request->setClient();

        // NOCACHE because the Cache::getKey signature requires a RequestInterface
        // and we want predictable per-call freshness for writes & paginated reads.
        $response = new CodedResponse($this->request, false, Response::NOCACHE);

        // On 401 (expired/invalid access token) force a token refresh by
        // rebuilding the Request and retry the call exactly once.
        if ($response->getStatusCode() === 401) {
            $this->buildRequest();
            $this->request->setPayload($op);
            $this->request->setClient();
            $response = new CodedResponse($this->request, false, Response::NOCACHE);
        }

        if ($response->hasError()) {
            $message = $response->getError();
            if ($message === '') {
                $message = 'Unknown Amazon Creators API error.';
            }
            $code = self::extractHttpCode($response);
            throw new \Exception($message, $code);
        }

        return $response->get();
    }

    /**
     * Extract HTTP status code from response for exception propagation.
     * Falls back to 500 if unavailable.
     *
     * @param  CodedResponse $response
     * @return int
     */
    private static function extractHttpCode(CodedResponse $response) : int
    {
        $code = $response->getStatusCode();
        return $code > 0 ? $code : 500;
    }

    /**
     * Ensure apaapi's standalone autoloader is registered.
     * Idempotent — safe to call multiple times.
     */
    private static function bootstrapAutoloader() : void
    {
        if (!class_exists('\\Apaapi\\Autoloader', false)) {
            $file = dirname(__DIR__) . '/apaapi/Autoloader.php';
            if (is_file($file)) {
                require_once $file;
            }
        }
        if (class_exists('\\Apaapi\\Autoloader', false)) {
            \Apaapi\Autoloader::init();
        }
    }
}
