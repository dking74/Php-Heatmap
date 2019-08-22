<?php

/**
 * Class for communication with Solarwinds API
 */
class Solarwinds {		
    private $url = "";
    private $username = "";
    private $password = "";
		
    /**
     * Constructor for Solarwinds Object
     *	- $url is the url to connect to
     *  - $username is the username making the bind
     *	- $password is the password associated to username
     */
    public function __construct($url, $username, $password) {
        $this->url = "https://" . $url . ":17778/SolarWinds/InformationService/v3/Json/";
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Query makes a curl request on a user inputted 
     * query via REST call
     *	- $query: The string query to call
     */
    public function query($query) {
        return Request::post(
			$this->url . "Query",
			$this->username,
			$this->password,
			json_encode(array('query' => $query)));
    }

    /**
     * Invoke makes a curl request on a specific entity and verb
     *	- $entity: The specific entity to invoke on
     *	- $verb: The verb to call on entity
     *	- $args: The arguments you wish to pass to invoke request
     */
    public function invoke($entity, $verb, $args) {
        return Request::post(
			$this->url . "Invoke/" . $entity . "/" . $verb,
			$this->username,
			$this->password,
			string($args));
    }

    /**
     * Creates a specific entity
     * - $entity: The entity to create, e.g. Orion.Nodes
     * - $properties: The properties to pass in for creation
     */
    public function create($entity, $properties) {
        return Request::post(
			$this->url . "Create/" . $entity,
			$this->username,
			$this->password,
			$properties);
    }

    /**
     * Read a specific $uri from console
     */
    public function read($uri) {
        return Request::get(
			$this->url . $uri,
			$this->username,
			$this->password);
    }

    /**
     * Update a specific entity
     * - $uri: The specific uri of the entity to update
     * - $properties: The new properties of the entity
     */
    public function update($uri, $properties) {
        return Request::post(
			$this->url . $uri,
			$this->username,
			$this->password,
			$properties);
    }

    /**
     * Delete specific uri from solarwinds
     * - $uri: The uri of the entity to delete
     */
    public function delete($uri) {
        return Request::delete(
			$this->url . $uri,
			$this->username,
			$this->password);
    }
}

/**
 * Class built on libcurl that helps submitting Solarwinds requests
 */
class Request {		
    /**
     * Sends a 'Post' request via curl
     * Takes in the uri and properties of request
     * - $properties is the data to go in post request
     */
    static public function post($uri, $username, $password, $properties) {
        // Initialize curl session and send request
        $curl_connect = curl_init();
        curl_setopt($curl_connect, CURLOPT_URL, $uri);
        curl_setopt($curl_connect, CURLOPT_POST, True); 
        curl_setopt($curl_connect, CURLOPT_POSTFIELDS, $properties);
        curl_setopt($curl_connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connect, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connect, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_connect, CURLOPT_HTTPHEADER, array(
                                 'Authorization: Basic ' . base64_encode($username . ":" . $password),
                                 'Content-Type: application/json',
                                 'Accept: */*'
        ));
				
        // Execute the command and get the data --> return it
        $execution = curl_exec($curl_connect);
        $data = json_decode($execution, true);
        curl_close($curl_connect);
        return $data;
    }
		
    /**
     * Sends a 'get' request via curl
     * Takes in the uri of request, username and password of requester
     */
    static public function get($uri, $username, $password) {
        $curl_connect = curl_init();
        curl_setopt($curl_connect, CURLOPT_URL, $uri);
        curl_setopt($curl_connect, CURLOPT_FRESH_CONNECT, True);
        curl_setopt($curl_connect, CURLOPT_HTTPHEADER, array(
                                 'Authorization: Basic ' . base64_encode($username . ":" . $password),
                                 'Content-Type: application/json',
                                 'Accept: */*'
        ));
        curl_setopt($curl_connect, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($curl_connect, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connect, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_connect, CURLOPT_HTTPGET, True);
			
        // Execute the command and get the data --> return it
        $execution = curl_exec($curl_connect);
        $data = json_decode($execution, true);
        curl_close($curl_connect);
        return $data;
    }
		
    /**
     * Sends a 'delete' request via curl
     * Takes in the uri of request
     */
    static public function delete($uri, $username, $password) {
        $curl_connect = curl_init();
        curl_setopt($curl_connect, CURLOPT_URL, $uri);
        curl_setopt($curl_connect, CURLOPT_FRESH_CONNECT, True);
        curl_setopt($curl_connect, CURLOPT_HTTPHEADER, array(
                                 'Authorization: Basic ' . base64_encode($username . ":" . $password),
                                 'Content-Type: application/json',
                                 'Accept: */*'
        ));
        curl_setopt($curl_connect, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($curl_connect, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connect, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_connect, CURLOPT_CUSTOMREQUEST, "DELETE");
			
        // Execute the command and get the data --> return it
        $execution = curl_exec($curl_connect);
        $data = json_decode($execution, true);
        curl_close($curl_connect);
        return $data;
    }
}

?>