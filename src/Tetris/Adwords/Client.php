<?php

namespace Tetris\Adwords;

use AdWordsUser;
use CustomerService;
use Customer;
use stdClass;
use Tetris\Adwords\Request\Read\TransientReadRequest;
use Tetris\Adwords\Request\Write\UpdateRequest;
use Tetris\Adwords\Request\Write\InsertRequest;

class Client extends AdWordsUser
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
     * @var string $tetrisAccount
     */
    protected $tetrisAccount;

    function __construct(string $tetrisAccount, stdClass $token, $selectFirstCustomer = true)
    {
        parent::__construct();
        $this->tetrisAccount = $tetrisAccount;
        $this->SetUserAgent('oDash');
        $this->SetDeveloperToken(self::$config['DEVELOPER_TOKEN']);
        $this->SetOAuth2Info(array_merge([
            'client_id' => self::$config['CLIENT_ID'],
            'client_secret' => self::$config['CLIENT_SECRET']
        ], (array)$token));

        if ($selectFirstCustomer) {
            /**
             * @var CustomerService $customerSvc
             */
            $customerSvc = $this->GetService('CustomerService');
            $this->customer = $customerSvc->getCustomers()[0];

            $this->SetClientCustomerId($this->customer->customerId);
        }
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