<?php

namespace Beepsend\Resource;

use Beepsend\Request;

/**
 * Beepsend pricelist resource
 * @package Beepsend
 */
class Pricelist 
{
    
    /**
     * Beepsend request handler
     * @var Beepsend\Request
     */
    private $request;
    
    /**
     * Actions to call
     * @var array
     */
    private $actions = array(
        'connections' => '/connections/',
        'pricelists' => '/pricelists/current',
        'download' => '/pricelists/'
    );
    
    /**
     * Init customer resource
     * @param Beepsend\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Get customer data
     * @return array
     */
    public function get($conection = 'me')
    {
        $response = $this->request->execute($this->actions['connections'] . $conection . $this->actions['pricelists'], 'GET');
        return $response;
    }
    
    /**
     * Download pricelists as csv
     */
    public function download($connection)
    {
        $response = $this->request->download($connection . '.csv', $this->actions['download'] . $connection . '.csv', 'GET');
        return $response;
    }
    
}