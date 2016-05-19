<?php

namespace Tetris\Adwords;

use AdWordsUser;
use CustomerService;
use Customer;
use stdClass;
use Tetris\Adwords\Request\Read\TransientRequest as ReadRequest;

class Client extends AdWordsUser
{
    /**
     * @var array $config
     */
    private static $config = [];
    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var string $tetrisAccount
     */
    private $tetrisAccount;

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
     *  - GOOGLEADS_LIB_UTILS_DIR - which should look like: vendor/googleads/googleads-php-lib/src/Google/Api/Ads/AdWords/Util/v201603
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

    function select(array $fields): ReadRequest
    {
        return Request::select($this, $fields);
    }
}