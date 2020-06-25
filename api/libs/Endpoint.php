<?php

//Use JWT library
use \Firebase\JWT\JWT;

/**
 * The Endpoint Controller
 */
class Endpoint extends Model{
    
    protected $jwt = null;
    protected $jwtData = null; 

    public $responseCode = "404 Not Found";
    public $output = array(
        "message" => "Unknown error",
        "success" => false,
        "data" => array()
    );

    protected $data = null;

	function __construct(){
        parent::__construct();
        $this->data = json_decode(file_get_contents("php://input"),true);
        $this->handleJWT();
    }
    
    function getResponse(){
        return $this->responseCode;
    }

    function getOutput(){
        return $this->output;
    }

    //Auth Headers should be added in the following format: 'JWT {TOKEN}'
    function handleJWT(){
        $headers = getallheaders();
        if(isset($headers['Authorization'])){
            $explodedHeader = explode(" ", $headers['Authorization']);
            if(count($explodedHeader) == 2){
                $jwtHeader = $explodedHeader[1];
                try {

                    $this->jwt = $jwtHeader;
                    $this->jwtData = JWT::decode($jwtHeader, JWT_SECRET_TOKEN, array('HS256'));

                }catch (Exception $e){
            
                    $this->jwt = null;
                    $this->jwtData = null;
                    
                }
            }
        }
    }

}