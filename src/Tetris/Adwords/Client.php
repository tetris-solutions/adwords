<?php

namespace Tetris\Adwords;

use AdWordsUser;
use CustomerService;
use Customer;
use stdClass;
use Tetris\Adwords\Request\Read\TransientRequest as ReadRequest;
use Tetris\Adwords\Request\Write\UpdateRequest;
use Tetris\Adwords\Request\Write\InsertRequest;

class Client extends AdWordsUser
{
    /**
     * @var array $config
     */
    private static $config = [];
    /**
     * @var Customer
     */
    protected $customer;
    /**
     * @var string $tetrisAccount
     */
    protected $tetrisAccount;

    function __construct(string $tetrisAccount, stdClass $token)
    {
        parent::__construct();
        $this->tetrisAccount = $tetrisAccount;
        $this->SetUserAgent('oDash');
        $this->SetDeveloperToken(self::$config['DEVELOPER_TOKEN']);
        $this->SetOAuth2Info(array_merge([
            'client_id' => self::$config['CLIENT_ID'],
            'client_secret' => self::$config['CLIENT_SECRET']
        ], (array)$token));

        /**
         * @var CustomerService $customerSvc
         */
        $customerSvc = $this->GetService('CustomerService');
        $this->customer = $customerSvc->get();

        $this->SetClientCustomerId($this->customer->customerId);
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
     * @return ReadRequest
     */
    function select(array $fieldMap): ReadRequest
    {
        return Request::select($this, $fieldMap);
    }

    /**
     * @param string $className
     * @param string|null $serviceName
     * @return UpdateRequest
     */
    function update(string $className, $serviceName = null): UpdateRequest
    {
        return Request::update($this, $className, $serviceName);
    }

    /**
     * @param array $values
     * @return InsertRequest
     */
    function insert(array $values): InsertRequest
    {
        return Request::insert($this, $values);
    }
}