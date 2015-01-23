<?php
/**
 * Lightweight Dummy ILS Driver -- Always returns hard-coded sample values.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  ILS_Drivers
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_an_ils_driver Wiki
 */
namespace VuFind\ILS\Driver;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;
use ArrayObject, VuFind\Exception\Date as DateException,
    VuFind\Exception\ILS as ILSException,
    VuFindSearch\Query\Query, VuFindSearch\Service as SearchService,
    Zend\Session\Container as SessionContainer;

/**
 * Lightweight Dummy ILS Driver -- Always returns hard-coded sample values.
 *
 * @category VuFind2
 * @package  ILS_Drivers
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_an_ils_driver Wiki
 */
class Integral extends AbstractBase implements LoggerAwareInterface, \VuFindHttp\HttpServiceAwareInterface
{
    protected $logger;
    /**
     * HTTP service
     *
     * @var \VuFindHttp\HttpServiceInterface
     */
    protected $httpService = null;

    /**
     * Initialize the driver.
     *
     * Validate configuration and perform all resource-intensive tasks needed to
     * make the driver active.
     *
     * @throws ILSException
     * @return void
     */
    public function init()
    {
        // Sample driver doesn't care about configuration.

    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Set the HTTP service to be used for HTTP requests.
     *
     * @param HttpServiceInterface $service HTTP service
     *
     * @return void
     */
    public function setHttpService(\VuFindHttp\HttpServiceInterface $service)
    {
        $this->httpService = $service;
    }

    /**
     * Log a debug message.
     *
     * @param string $msg Message to log.
     *
     * @return void
     */
    protected function debug($msg)
    {
        if ($this->logger) {
            $this->logger->debug($msg);
        }
    }

    
    public function getJsonArray($urlParams) {
	// Create Proxy Request
        try {
	    $client = $this->httpService->createClient($urlParams, 'GET', 60);
            $mode = 'GET';
            $result = $client->setMethod($mode)->send();
            if (!$result->isSuccess()) {
                $this->debug(
                    "$mode request for '$urlParams' with contents '$xml' failed: "
                    . $result->getStatusCode() . ': ' . $result->getReasonPhrase()
                );
                throw new ILSException('Problem with RESTful API.');
	    }
        } catch(Exception $e) {
           $this->debug("Error in getJsonArray");
           throw new ILSException('Login failed or problem with RESTFUL Api');
        }
        $jsonResponse = $result->getBody();
        $arrayResponse = json_decode($jsonResponse, true);
	 
        return $arrayResponse;
    }

    public function postJsonArray($urlParams, $body) {
        $this->debug("Entering postJsonArray");
        $this->debug("urlParams = ".$urlParams);
        //$this->debug("body = ".var_dump($body));
        //$urlParams = "http://localhost:8080/integral-mystic/rest/services/vufind/renewMyItems";
	 // Create Proxy Request
        try {
	     $client = $this->httpService->createClient($urlParams);
            $mode = 'POST';
            
	     //$client->setEncType('application/x-www-form-urlencoded');
            //$client->setEncType('application/json');
            
	     $http_headers = array(
                "Content-Type: application/json"
                //"Accept: application/json",
		  //"Connection: Keep-Alive"
		  //"User-Agent: curl/7.23.1 (x86_64-pc-win32) libcurl/7.23.1 OpenSSL/0.9.8r zlib/1.2.5",
		  //"Authorization: Basic bGltc3llbmllOmxpbXN5ZW5pZQ=="
		
            );
	     
            $client->setHeaders($http_headers);
	     //$body = "{\"patronId\": \"limsyenie\",\"cicircIds\": [\"3\"]}";
       
	     $client->setRawBody(json_encode($body));
	     //$this->debug("Content-Type = ".$client->getHeader("Content-Type")) ;
            
            $result = $client->setMethod($mode)->send();
            
	     //("result = ".$result);
            if (!$result->isSuccess()) {                
                $this->debug(
                    "$mode request for '$urlParams' with contents '$xml' failed: "
                    . $result->getStatusCode() . ': ' . $result->getReasonPhrase()
                );
                throw new ILSException('Problem with RESTful API.');
	    }
        } catch(Exception $e) {
           throw new ILSException('Login failed or problem with RESTFUL Api');
        }
        $jsonResponse = $result->getBody();
        $arrayResponse = json_decode($jsonResponse, true);
        return $arrayResponse;
    }

    public function postJsonArrayNoComments($urlParams, $body) {
        
        try { 
	     $client = $this->httpService->createClient($urlParams, 'POST', 60);
            //$mode = 'POST';
	     $http_headers = array(
                "Content-Type: application/json"                
            );
	     
            $client->setHeaders($http_headers);	     
	     $client->setRawBody(json_encode($body));
	     //$this->debug("get from ".json_encode($body));
            $result = $client->send();
            //$this->debug("get result");
	     if (!$result->isSuccess()) {           
                throw new ILSException('Problem with RESTful API.');
	     }
        } catch(Exception $e) {   
		throw new ILSException('Login failed or problem with RESTFUL Api');
        }
        $jsonResponse = $result->getBody();
        $arrayResponse = json_decode($jsonResponse, true);
        return $arrayResponse;
    }

/**
     * Get Status
     *
     * This is responsible for retrieving the status information of a certain
     * record.
     *
     * @param string $id The record id to retrieve the holdings for
     *
     * @return mixed     On success, an associative array with the following keys:
     * id, availability (boolean), status, location, reserve, callnumber.
     */
    public function getStatus($id)
    {   
 	 $holding = array();
        
        $holding[0] = array('availability' => true,
                           'status' => 'Lost',
                           'location' => '3rd Floor Main Library',
                           'reserve' => 'Yes',
                           'callnumber' => 'XXA1234.567',
                           'duedate' => '',
                           'number' => 1,
			      'item_id' => 'aaaaaaa',
			      'summary' => 'this is summary');

	$holding[1] = array('availability' => true,
                           'status' => 'Missing',
                           'location' => 'Ground Floor Main Library',
                           'reserve' => 'Yes',
                           'callnumber' => 'A1234.567',
                           'duedate' => '',
                           'number' => 1,
			      'item_id' => '000000003',
			      'summary' => 'this is summary for 0000000003');

        return $holding;
    }


    /**
     * Get Statuses
     *
     * This is responsible for retrieving the status information for a
     * collection of records.
     *
     * @param array $ids The array of record ids to retrieve the status for
     *
     * @return mixed     An array of getStatus() return values on success.
     */
    public function getStatuses($ids)
    {
	//mock data
	//$this->debug("Entering getStatuses");
	/* 
        $urlParamsJson = "http://localhost/vufind/data/getStatuses.json";
        $json = $this->getJsonArray($urlParamsJson);
        return $json;
	*/ 
	
        
	
        //$username = $this->config['IntegralMystic']['username'];
	//$password = $this->config['IntegralMystic']['password'];	
        //$urlParams = "http://".$username.":".$password."@".$integralUrl."/accession/getStatuses";
	//mysticUrl = localhost:8080/integral-mystic
        //vufindRestUrl = localhost:8080/integral-mystic/rest/services
	
        $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	$urlParams = "http://".$integralUrl."/accession/getStatuses";
	$body = $ids;
        $items = $this->postJsonArrayNoComments($urlParams, $ids);
	//$this->debug(var_dump($items));
        return $items;
     	    

        
   
	/*
        $status = array();
        foreach ($idList as $id) {
            $status[] = $this->getStatus($id);
        }
        return $status;
   	*/     
       

    }

    /**
     * Get Holding
     *
     * This is responsible for retrieving the holding information of a certain
     * record.
     *
     * @param string $id     The record id to retrieve the holdings for
     * @param array  $patron Patron data
     *
     * @return mixed     On success, an associative array with the following keys:
     * id, availability (boolean), status, location, reserve, callnumber, duedate,
     * number, barcode.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getHolding($id, array $patron = null)
    {
         $this->debug("Entering getHolding");

	 //mock data
	 /*	 
	 $holding = array();
	 $holding[0] = array(
                'id'                => $id,
                'item_id'           => 'VUFIND0001',
                'availability'      => false,
                'status'            => 'Available',
                'location'          => 'Campus A',
                'reserve'           => 'Y',
                'callnumber'        => 'A.265.265',
                'duedate'           => '08/05/2014 00:00:00',  
                'returndate'        => false, 
                'number'            => null,   
                'requests_placed'   => null,
                'notes'		 => array("The status of the item is Available. You may get the item from the shelf."),
                'summary'		 => [],
                'indexes'		 => [],
                'supplements'	 => null,
                'barcode'		 => 'XXXXXXX',              
                'is_holdable'       => true                
            );

          $holding[1] = array(
                'id'                => $id,
                'item_id'           => 'VUFIND0002',
                'availability'      => true,
                'status'            => 'circulated',
                'location'          => 'Campus C',
                'reserve'           => 'Y',
                'callnumber'        => 'z30-call-no',
                'duedate'           => '05/05/2014 00:00:00',  
		  'number'		 => 2,
                'barcode'		 => 'XXXXX',              
                'notes'             => null,
                'is_holdable'       => true,
                'holdtype'          => 'recall'
                
            );

	   $holding[2] = array(
                'id'                => $id,
                'item_id'           => 'VUFIND0003',
                'availability'      => true,
                'status'            => 'circulated',
                'location'          => 'Campus C',
                'reserve'           => 'N',
                'callnumber'        => 'z30-call-no',
                'duedate'           => '06/06/2014 00:00:00',  
		  'number'		 => 2,
                'barcode'		 => 'XXXXX',              
                'notes'             => null,
                'is_holdable'       => true,
                'holdtype'          => 'recall'
                
            );

	    $holding[2] = array(
                'id'                => '0000000001',
                'item_id'           => '0000000001',
                'availability'      => false,
                'status'            => 'NEW',
                'location'          => null,
                'reserve'           => null,
                'callnumber'        => null,
                'duedate'           => null,
		'returnDate'	    => null,  
		'number'	    => null,
                'requests_placed'   => null,
                'barcode'	    => 'XXXXX',              
                'notes'             => null,
		'summary'	    => null,
		'supplements'	    => null,
		'indexes'	    => null,
                'is_holdable'       => null,
                'holdtype'          => null,
		'holdOverride'	    => null
                
            );

	    
	    $this->debug(var_dump($holding));
	    
	 */
	
       
	  

	//$username = $this->config['IntegralMystic']['username'];
	//$password = $this->config['IntegralMystic']['password'];	
        //$urlParams = "http://".$username.":".$password."@".$integralUrl."/accession/getStatuses";
	//mysticUrl = localhost:8080/integral-mystic
        //vufindRestUrl = localhost:8080/integral-mystic/rest/services
	
	
        $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	$urlParams = "http://".$integralUrl."/accession/".$id."/holding";
	$holding = $this->getJsonArray($urlParams);
        $this->debug($urlParams);
	//$this->debug(var_dump($holding));
        



	$this->debug("Leaving getHolding");
	return $holding; 
 	

    }

    /**
     * Get Purchase History
     *
     * This is responsible for retrieving the acquisitions history data for the
     * specific record (usually recently received issues of a serial).
     *
     * @param string $id The record id to retrieve the info for
     *
     * @return mixed     An array with the acquisitions data on success.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPurchaseHistory($id)
    {
        return array();
    }

    /**
     * Get New Items
     *
     * Retrieve the IDs of items recently added to the catalog.
     *
     * @param int $page    Page number of results to retrieve (counting starts at 1)
     * @param int $limit   The size of each page of results to retrieve
     * @param int $daysOld The maximum age of records to retrieve in days (max. 30)
     * @param int $fundId  optional fund ID to use for limiting results (use a value
     * returned by getFunds, or exclude for no limit); note that "fund" may be a
     * misnomer - if funds are not an appropriate way to limit your new item
     * results, you can return a different set of values from getFunds. The
     * important thing is that this parameter supports an ID returned by getFunds,
     * whatever that may mean.
     *
     * @return array       Associative array with 'count' and 'results' keys
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getNewItems($page, $limit, $daysOld, $fundId = null)
    {
        return array('count' => 0, 'results' => array());
    }

    /**
     * Find Reserves
     *
     * Obtain information on course reserves.
     *
     * @param string $course ID from getCourses (empty string to match all)
     * @param string $inst   ID from getInstructors (empty string to match all)
     * @param string $dept   ID from getDepartments (empty string to match all)
     *
     * @return mixed An array of associative arrays representing reserve items.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function findReserves($course, $inst, $dept)
    {
        return array();
    }

    /**
     * Patron Login
     *
     * This is responsible for authenticating a patron against the catalog.
     *
     * @param string $username The patron username
     * @param string $password The patron password
     *
     * @return mixed           Associative array of patron info on successful login,
     * null on unsuccessful login.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function patronLogin($vufindUsername, $vufindPassword)
    {
	
        $this->debug("Entering patronLogin(username=".$barcode.")"); 
        $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	 $username = $vufindUsername;
	 $password = $vufindPassword;

	 $urlParams = "http://".$username.":".$password."@".$integralUrl."/patronLogin"; 
	 $this->debug("Url = ".$urlParams);
	 $body = array();
        $body['username'] = $vufindUsername;
        $body['password'] = $vufindPassword;
        $user = $this->postJsonArray($urlParams, $body);
        $user['id']           = trim($vufindUsername);
        $user['cat_username'] = trim($vufindUsername);
        $user['cat_password'] = trim($vufindPassword);
        $user['email']        = trim($vufindUsername."@myriadeas.com.my");
        $user['major']        = null;
        $user['college']      = null;

 
	 $this->debug("Login successfully");
        return $user;
    }

    /**
     * Get Patron Profile
     *
     * This is responsible for retrieving the profile for a specific patron.
     *
     * @param array $patron The patron array
     *
     * @return array        Array of the patron's profile data on success.
     */
    public function getMyProfile($patron)
    {
        $this->debug("Entering getMyProfile");

        /*
        $patron = array(
            'firstname' => $patron['firstname'],
            'lastname'  => $patron['lastname'],
            'address1'  => trim("Somewhere ..."),
            'address2'  => trim("Other the Rainbow"),
            'zip'       => trim("12345"),
            'phone'     => trim("1900 CALL ME"),
            'group'     => trim("Library Staff")
        );*/

	 $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];
	 $urlParams = "http://".$username.":".$password."@".$integralUrl."/".$patron['id']."/myProfile";
        $this->debug($urlParams);
        //$this->debug(var_dump($patron));
        $patron = $this->getJsonArray($urlParams);
        //$this->debug(var_dump($patron));
        $this->debug("Leaving getMyProfile");
        return $patron;
    }
    
    /**
     * Get Patron Transactions
     *
     * This is responsible for retrieving all transactions (i.e. checked out items)
     * by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return mixed        Array of the patron's transactions on success.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function getMyTransactions($patron)
    {
        $this->debug("Entering getMyTransactions");
        $holding = array();
        $holding[0] = array('duedate' => '11/3/2014',
                           'id' => '0000000001',
                           'barcode' => '0000000001',
                           'renew' => 3,
                           'renewLimit' => 5,
                           'request' => 1,
                           'volume' => 2,
                           'publication_year' => 2014, 
                           'renewable' => true,
                           'message' => 'Nothing',
                           'title' => 'Managing Software Date',
                           'item_id' => '0000000001',
                           'institution_name' => 'UMS Library');
        $holding[1] = array('duedate' => '11/3/2014',
                           'id' => '0000000002',
                           'barcode' => '0000000002',
                           'renew' => 3,
                           'renewLimit' => 5,
                           'request' => 1,
                           'volume' => 2,
                           'publication_year' => 2014, 
                           'renewable' => true,
                           'message' => 'special item',
                           'title' => 'What yo Expect when you are expecting',
                           'item_id' => '0000000002',
                           'institution_name' => 'UMS Library');
        //$this->debug(var_dump($holding));
        //$urlParams = "http://localhost/vufind/data/myTransactions/".$patron["id"].".json";
        $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];
	 $urlParams = "http://".$username.":".$password."@".$integralUrl."/".$patron['id']."/myTransactions";
        $this->debug($urlParams);
       
        $items = $this->getJsonArray($urlParams);
        //$this->debug(var_dump($items));
        //return $holding;
        $this->debug("Leaving getMyTransactions");

        return $items;
    }
    

    public function getMyFines($patron)
    {
        $this->debug("Entering getMyFines");
	 $fines = array();
	 $fines[0] = array('amount' => '20',
			     'checkout' => '01/04/2014',
			     'fine' => '4 days late return',
			     'balance' => '10',
			     'createdate' => '01/04/2014',
			     'duedate' => '27/03/2014',
			     'id' => '0000000012');

	 //$urlParams = "http://localhost/vufind/data/myFines/".$patron["id"].".json";
 	 //$json = $this->getJsonArray($urlParams);
         //$this->debug(var_dump($json));
	 //$this->debug(var_dump($fines));
	 $this->debug("Leaving getMyFines");

	 return $fines;
    }

    public function getMyHolds($patron)
    {
	 $this->debug("Entering getMyHolds");
        $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];
	 $urlParams = "http://".$username.":".$password."@".$integralUrl."/".$patron['id']."/myHolds";
        $this->debug($urlParams);
	 $json = $this->getJsonArray($urlParams);
	 //$this->debug(var_dump($json));


 	 //$json = $this->getJsonArray($urlParams);

        //$this->debug(var_dump($json));
        $this->debug("Leaving getMyHolds");
	 return $json;
    }

    public function renewMyItems($renewDetails)
    {
        $this->debug("Entering renewMyItems");
        //$this->debug(var_dump($renewDetails));
        
	 $items = array('blocks' => array(), 'details' => array());
        $items["details"]["0000000001"] = array(
	     "success" => true,
	     "new_date" => "01/05/2014",
	     "new_time" => "14:01:01",
	     "item_id" => "0000000001",
	     "sysMessage" => "successfully renewed.",
        );
	 $items["details"]["0000000002"] = array(
	     "success" => false,	     
	     "item_id" => "0000000002",
	     "sysMessage" => "Fail to renew.",
        );
	 //$this->debug(var_dump($items ));


	 $patron = $renewDetails['patron'];	 
        $details = $renewDetails['details']; //List<CicircId>;
	
        $body = array();
        $body['username'] = $patron["id"];
        $body['circulationTransactionIds'] = $details;
        //$this->debug(var_dump($body));

	 
	 $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];
	 $urlParams = "http://".$username.":".$password."@".$integralUrl."/renewMyItems";

 	 $json = $this->postJsonArray($urlParams, $body);
	 //$this->debug(var_dump($json));
        $this->debug("Leaving renewMyItems");
	 
        //return $items;
        return $json;
    }

    public function renewMyItemsLink($checkOutDetails){
        $this->debug("Entering renewMyItemsLink"); 
 	 $itemId = $checkOutDetails['item_id'];
    	 $urlParams = "http://localhost/vufind/data/renewMyItemsLink/".$itemId.".json";
 	 $json = $this->getJsonArray($urlParams);
        $this->debug("Leaving renewMyItemsLink"); 
	 return $json;
    }

    public function getRenewDetails($checkOutDetails){
        $this->debug("Entering getRenewDetails"); 
	 $itemId = $checkOutDetails['item_id'];
        //$this->debug("itemId=".$itemId);

	 //$urlParams = "http://localhost/vufind/data/renewDetails/".$itemId.".json";
        $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];
	
        $urlParams = "http://".$username.":".$password."@".$integralUrl."/".$itemId."/renewDetails";
 	 $json = $this->getJsonArray($urlParams);
        $this->debug("Leaving getRenewDetails");
        return $json;
    }

    public function getConfig($function){
	 $this->debug("Entering getConfig"); 
        $this->debug($function);

	 if ($function == 'Holds') {
            return array(
                'HMACKeys' => 'id:item_id:level:holdtype',
                'extraHoldFields' => 'pickUpLocation' //'comments:pickUpLocation:requiredByDate',
                //'defaultRequiredDate' => '0:0:7'
            );
        }
        if ($function == 'StorageRetrievalRequests'
            && $this->storageRetrievalRequests
        ) {
            return array(
                'HMACKeys' => 'id',
                'extraFields' => 'comments:pickUpLocation:requiredByDate:item-issue',
                'helpText' => 'This is a storage retrieval request help text'
                    . ' with some <span style="color: red">styling</span>.'
            );
        }
        return array();




	 /*
        $result = array();

	 //$functionToCheck can be “cancelHolds”, “Holds”, “Renewals”.

        if ($functionToCheck== 'Holds'){
            if (isset($this->config[$functionToCheck]) ) {
                $functionConfig = $this->config[$functionToCheck];
                $this->debug("bbbbbbbbbb");

                $result['HMACKeys'] = $this->config['Holds']['HMACKeys'];
                $result['extraHoldFields'] = $this->config['Holds']['extraHoldFields'];
                $result['defaultRequiredDate'] = $this->config['Holds']['defaultRequiredDate'];
 	
            } else {
		    $this->debug("aaaaaaaaaaaaa");

                $functionConfig = false;
            }
        }

        if ($functionToCheck== 'StorageRetrievalRequests'){
            if (isset($this->config[$functionToCheck]) ) {
                $functionConfig = $this->config[$functionToCheck];
                $this->debug("ccccc");

                $result['HMACKeys'] = $this->config['Holds']['HMACKeys'];
                $result['extraHoldFields'] = $this->config['Holds']['extraHoldFields'];
                $result['defaultRequiredDate'] = $this->config['Holds']['defaultRequiredDate'];
 	
            } else {
		    $this->debug("dddddd");

                $functionConfig = false;
            }
        }




        $this->debug(var_dump($result));
        $this->debug("Leaving getConfig");
        //return $functionConfig;                
	 return $result;                
	*/
    }

    public function getCancelHoldDetails($holdDetails)
    {
        $this->debug("Entering getCancelHoldDetails"); 

        $position = $holdDetails["position"];
        $id = $holdDetails["id"];
        $item_id = $holdDetails["item_id"]; 
	 $reqnum = $holdDetails["reqnum"]; //ciresvId
	 $this->debug("ciresvId=".$reqnum);
	 //no need to call, just take the reqnum
        //$urlParams = "http://localhost/vufind/data/cancelHoldDetails/".$item_id.".json";
	 //$json = $this->getJsonArray($urlParams);
        $this->debug("Leaving getCancelHoldDetails");
        //return $json;
	return $reqnum;
    }

    public function getCancelHoldLink($holdDetails)
    {
        $this->debug("Entering getCancelHoldLink");

        $position = $holdDetails["position"];
        $id = $holdDetails["id"];
        $item_id = $holdDetails["item_id"]; 

	 
        $urlParams = "http://localhost/vufind/data/cancelHoldLink/".$item_id.".json";
        $json = $this->getJsonArray($urlParams);

        $this->debug("Leaving getCancelHoldLink");
        return $json;
    }
 
    public function placeHold($holdDetails){
        $this->debug("Entering placeHold"); 
        $this->debug($holdDetails["patron"]['id']);
        $this->debug($holdDetails["HMACKeys"]);
        $this->debug($holdDetails["item_id"]);
        $this->debug($holdDetails["holdtype"]);
        $this->debug($holdDetails["pickUpLocation"]);
        $this->debug($holdDetails["requiredByDate"]);
        $this->debug($holdDetails["level"]);



        $patron = $holdDetails["patron"];
	 $id = $holdDetails["id"];
	 $item_id  = $holdDetails["item_id"];
	 $holdtype = $holdDetails["holdtype"];
	 $level = $holdDetails["level"];
	 $pickUpLocation = $holdDetails["pickUpLocation"];
	 $requiredByDate = $holdDetails["requiredByDate"];
	 $comments = $holdDetails["comments"];
	
        $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];

        $urlParams = "http://".$username.":".$password."@".$integralUrl."/placeHold?username=".$patron["id"]."&itemId=".$item_id."&pickUpBranch=".$pickUpLocation; 
	 //$urlParams = "http://localhost/vufind/data/placeHold/placeHoldForPatron_".$patron["id"]."_onItem_".$item_id.".json";
        $this->debug("$urlParams");

        $json = $this->getJsonArray($urlParams);
        //$this->debug(var_dump($json));

        $this->debug("Leaving placeHold");
        return $json;
    }

    public function cancelHolds($cancelDetails)
    {
        $this->debug("Entering cancelHolds");
        $patron = $cancelDetails["patron"];
        $details = $cancelDetails["details"];//List of CiresvId
        
	 
	 $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];

	 $urlParams = "http://".$username.":".$password."@".$integralUrl."/cancelHolds"; 
	 $this->debug($urlParams );

        $body = array();
        $body['username'] = $patron["id"];
        $body['reservationTransactionIds'] = $details;
        $json = $this->postJsonArray($urlParams, $body);
	 

	 //$urlParams = "http://localhost/vufind/data/cancelHolds/cancelHolds.json";
        //$json = $this->getJsonArray($urlParams);
        $this->debug("Leaving cancelHolds");
        return $json;
    }

    public function getPickUpLocations($patron = false, $holdDetails = null)
    {
	 $this->debug("Entering getPickUpLocations");

	 $integralUrl = $this->config['IntegralMystic']['vufindRestUrl'];        
	 $username = $this->config['IntegralMystic']['username'];
	 $password = $this->config['IntegralMystic']['password'];

	 $urlParams = "http://".$username.":".$password."@".$integralUrl."/pickUpLocations"; 
	 $json = $this->getJsonArray($urlParams);
        //$this->debug(var_dump($json));

	 $this->debug("Leaving getPickUpLocations");
        return $json;
	 /*	
        return array(
            array(
                'locationID' => 'A',
                'locationDisplay' => 'Campus A'
            ),
            array(
                'locationID' => 'B',
                'locationDisplay' => 'Campus B'
            ),
            array(
                'locationID' => 'C',
                'locationDisplay' => 'Campus C'
            )
        );
	 */
	

    }

    public function getDefaultPickUpLocation($patron = false, $holdDetails = null)
    {
        return "MAIN";
    }

    
}
