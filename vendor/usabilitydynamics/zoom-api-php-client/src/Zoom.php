<?php

namespace UDX\Zoom;
use Exception;

class Zoom {

    /**
     * @var null
     */
    private $accountId = null;

    /**
     * @var null
     */
    private $clientId = null;

    /**
     * @var null
     */
    private $clientSecret = null;

    /**
     * Zoom constructor.
     * @param $apiKey
     * @param $apiSecret
     */
    public function __construct( $accountId, $clientId, $clientSecret ) {

        $this->accountId = $accountId;

        $this->clientId = $clientId;

        $this->clientSecret = $clientSecret;
    }

    /**
     * __call
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call( $method, $args ) {
        return $this->make( $method );
    }

    /**
     * __get
     *
     * @param $name
     * @return mixed
     */
    public function __get( $name ) {
        return $this->make( $name );
    }
    /**
     * Make
     *
     * @param $resource
     * @return mixed
     * @throws Exception
     */
    public function make($resource) {
        $className = 'UDX\\Zoom\\Endpoint\\' . ucfirst(strtolower($resource));

        if (class_exists($className)) {
            return new $className($this->accountId, $this->clientId, $this->clientSecret);
        } else {
            throw new Exception('Method ' . $resource . ' not found.');
        }
    }
}