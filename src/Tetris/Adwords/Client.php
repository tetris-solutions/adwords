<?php

namespace Tetris\Adwords;

use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Common\Configuration;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\v201705\mcm\CustomerService;
use Google\AdsApi\AdWords\v201705\mcm\Customer;

use stdClass;
use Tetris\Adwords\Request\Read\TransientReadRequest;
use Tetris\Adwords\Request\Write\UpdateRequest;
use Tetris\Adwords\Request\Write\InsertRequest;
use Tetris\Adwords\AdWordsServicesList;

class Client
{
    const version = 'v201705';
    /**
     * @var array $config
     */
    private static $config = [];
    /**
     * @var Customer
     */
    protected $customer;
    /**
     * @var UserRefreshCredentials
     */
    protected $oauth2Info;
    /**
     * @var AdWordsSession
     */
    protected $adWordsSession;
    /**
     * @var AdWordsServices
     */
    protected $adWordsServices;
    /**
     * @var string $tetrisAccount
     */
    protected $tetrisAccount;

    function __construct(string $tetrisAccount, stdClass $token, $selectFirstCustomer = true)
    {
        $this->tetrisAccount = $tetrisAccount;

        $this->oauth2Info = (new OAuth2TokenBuilder())
        ->from(new Configuration([]))
        ->withClientId(self::$config['CLIENT_ID'])
        ->withClientSecret(self::$config['CLIENT_SECRET'])
        ->withRefreshToken($token->refresh_token)
        ->build();

        $this->adWordsSession = (new AdWordsSessionBuilder())
        ->from(new Configuration([]))
        ->withOAuth2Credential($this->oauth2Info)
        ->withDeveloperToken(self::$config['DEVELOPER_TOKEN'])
        ->withUserAgent('oDash')
        ->build();

        $this->adWordsServices = new AdWordsServices();
        $customerSvc = $this->adWordsServices->get($this->adWordsSession, CustomerService::class);

        if ($selectFirstCustomer) {
            /**
             * @var CustomerService $customerSvc
             */
            $customerSvc = $this->adWordsServices->get($this->adWordsSession, CustomerService::class);
            $this->customer = $customerSvc->getCustomers()[0];
            $this->makeSession($this->customer->getCustomerId());
        }
    }

    /**
     * @param string $customerId
     */
    public function makeSession($customerId){ 
        $this->adWordsSession = (new AdWordsSessionBuilder())
        ->from(new Configuration([]))
        ->withOAuth2Credential($this->oauth2Info)
        ->withDeveloperToken(self::$config['DEVELOPER_TOKEN'])
        ->withUserAgent('oDash')
        ->withClientCustomerId($customerId)
        ->build();
    }
    public function getSession()
    {
        return $this->adWordsSession;
    }
    public function getAdWordsServices($service)
    {
        return $this->adWordsServices->get($this->adWordsSession, AdwordsServicesList::get($service));
    }

    /**
     * Required values:
     *  - DEVELOPER_TOKEN
     *  - CLIENT_ID
     *  - CLIENT_SECRET
     * Required for reports:
     *  - GOOGLEADS_LIB_UTILS_DIR - which should probably look like: /path/to/vendor/googleads/googleads-php-lib/src/Google/Api/Ads/AdWords/Util/v201603
     * @param array $map
     */
    static function setup(array $map)
    {
        foreach ($map as $key => $value) {
            self::$config[$key] = $value;
        }
    }

    static function getConfig(string $key)
    {
        return isset(self::$config[$key]) ? self::$config[$key] : NULL;
    }

    /**
     * @param array $fieldMap
     * @return TransientReadRequest
     */
    function select(array $fieldMap): TransientReadRequest
    {
        return new TransientReadRequest($this, $fieldMap);
    }

    /**
     * @param string $className
     * @param string|null $serviceName
     * @return UpdateRequest
     */
    function update(string $className, $serviceName = null): UpdateRequest
    {
        return new UpdateRequest($this, $className, $serviceName);
    }

    /**
     * @param array $values
     * @return InsertRequest
     */
    function insert(array $values): InsertRequest
    {
        return new InsertRequest($this, $values);
    }
}