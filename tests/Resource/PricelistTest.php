<?php

use Beepsend\Client;
use Beepsend\Connector\Curl;

class PricelistTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * Test getting pricelist info for some connection
     */
    public function testGet()
    {
        $connector = \Mockery::mock(new Curl());
        $connector->shouldReceive('call')
                    ->with(BASE_API_URL . '/' . API_VERSION . '/connections/me/pricelists/current', 'GET', array())
                    ->once()
                    ->andReturn(array(
                        'info' => array(
                            'http_code' => 200,
                            'Content-Type' => 'application/json'
                        ),
                        'response' => json_encode(array(
                            'networks' => array(
                                'mccmnc' => array(
                                    'mnc' => 03,
                                    'mcc' => 648
                                ),
                                'price' => 0.006,
                                'country' => array(
                                    'name' => 'Zimbabwe',
                                    'prefix' => 263,
                                    'code' => 'ZW'
                                ),
                                'operator' => 'Telecel Zimbabwe (PVT) Ltd (TELECEL)'
                            ),
                            'id' => 280290,
                            'timestamp' => 1386085755,
                            'active' => true
                        ))
                    ));
        
        $client = new Client('abc123', $connector);
        $pricelist = $client->pricelist->get('me');
        
        $this->assertInternalType('array', $pricelist);
        $this->assertEquals(648, $pricelist['networks']['mccmnc']['mcc']);
        $this->assertEquals(0.006, $pricelist['networks']['price']);
        $this->assertEquals('Zimbabwe', $pricelist['networks']['country']['name']);
        $this->assertEquals('Telecel Zimbabwe (PVT) Ltd (TELECEL)', $pricelist['networks']['operator']);
        $this->assertEquals(280290, $pricelist['id']);
        $this->assertEquals(true, $pricelist['active']);
    }
    
    /**
     * Test getting revisions
     */
    public function testRevisions()
    {
        $connector = \Mockery::mock(new Curl());
        $connector->shouldReceive('call')
                    ->with(BASE_API_URL . '/' . API_VERSION . '/connections/me/pricelists/', 'GET', array())
                    ->once()
                    ->andReturn(array(
                        'info' => array(
                            'http_code' => 200,
                            'Content-Type' => 'application/json'
                        ),
                        'response' => json_encode(array(
                            'networks_count' => 980,
                            'id' => 280290,
                            'timestamp' => 1386085000,
                            'active' => true,
                            'first_viewed' => 1386228799
                        ))
                    ));
        
        $client = new Client('abc123', $connector);
        $revisions = $client->pricelist->revisions();
        
        $this->assertInternalType('array', $revisions);
        $this->assertEquals(980, $revisions['networks_count']);
        $this->assertEquals(280290, $revisions['id']);
        $this->assertEquals(1386085000, $revisions['timestamp']);
        $this->assertEquals(true, $revisions['active']);
        $this->assertEquals(1386228799, $revisions['first_viewed']);
    }
    
    /**
     * Test downloading pricelists
     */
    public function testDownload()
    {
        $connector = \Mockery::mock(new Curl());
        $connector->shouldReceive('call')
                    ->with(BASE_API_URL . '/' . API_VERSION . '/pricelists/me.csv', 'GET', array())
                    ->once()
                    ->andReturn(array(
                        'info' => array(
                            'http_code' => 200,
                            'Content-Type' => 'application/json'
                        ),
                        'response' => 'mcc;mnc;operator;price'
                                    . '240;;Default;0.08'
                                    . '240;01;"TeliaSonera Mobile Networks AB Sweden (TeliaSonera Mobile Networks)";0.068'
                    ));
        
        $client = new Client('abc123', $connector);
        $pricelist = $client->pricelist->download('me', 'me');
        
        $this->assertEquals(null, $pricelist);
    }
    
    /**
     * Test getting comperation of pricelists revisions and returning thair diff. 
     */
    public function testDiff()
    {
        $connector = \Mockery::mock(new Curl());
        $connector->shouldReceive('call')
                    ->with(BASE_API_URL . '/' . API_VERSION . '/pricelists/1/4321..4371/diff', 'GET', array())
                    ->once()
                    ->andReturn(array(
                        'info' => array(
                            'http_code' => 200,
                            'Content-Type' => 'application/json'
                        ),
                        'response' => json_encode(array(
                            'country' => array(
                                'name' => 'Zimbabwe',
                                'prefix' => 263,
                                'code' => 'ZW'
                            ),
                            'operator' => 'Telecel Zimbabwe (PVT) Ltd (TELECEL)',
                            'mccmnc' => array(
                                array(
                                    'mnc' => '03',
                                    'mcc' => '648'
                                )
                            ),
                            'comment' => '',
                            'price' => 0.006,
                            'old_price' => 0.022,
                            'diff' => 'price'
                        ))
                    ));
        
        $client = new Client('abc123', $connector);
        $diff = $client->pricelist->diff(4321, 4371, 1);
        
        $this->assertInternalType('array', $diff);
        $this->assertEquals('Zimbabwe', $diff['country']['name']);
        $this->assertEquals(263, $diff['country']['prefix']);
        $this->assertEquals('ZW', $diff['country']['code']);
        $this->assertEquals('Telecel Zimbabwe (PVT) Ltd (TELECEL)', $diff['operator']);
        $this->assertEquals('', $diff['comment']);
        $this->assertEquals(0.006, $diff['price']);
        $this->assertEquals(0.022, $diff['old_price']);
        $this->assertEquals('price', $diff['diff']);
    }
    
    /**
     * Test getting compared pricelist revisions from given connection and return their diff as csv file.
     */
    public function testDownloadDiff()
    {
        $connector = \Mockery::mock(new Curl());
        $connector->shouldReceive('call')
                    ->with(BASE_API_URL . '/' . API_VERSION . '/pricelists/1/4321..4371/diff.csv', 'GET', array())
                    ->once()
                    ->andReturn(array(
                        'info' => array(
                            'http_code' => 200,
                            'Content-Type' => 'application/json'
                        ),
                        'response' => 'mcc;mnc;operator;price;price_diff;diff'
                                    . '240;;Default;0.08;0.011;price'
                                    . '240;01;"TeliaSonera Mobile Networks AB Sweden (TeliaSonera Mobile Networks)";;0;removed'
                    ));
        
        $client = new Client('abc123', $connector);
        $diff = $client->pricelist->downloadDiff(4321, 4371, 1);
        
        $this->assertEquals(null, $diff);
    }
    
    public function tearDown()
    {
        \Mockery::close();
    }
    
}