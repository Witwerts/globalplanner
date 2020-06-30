<?php

//Use JWT library
use \Firebase\JWT\JWT;

class Setting_Model extends Endpoint{

    function __construct(){
        parent::__construct();
    }

    function getSetting($id = null){
        $this->output['message'] = "Unauthorized to view settings";
        $this->responseCode = "401 Unauthorized";
        if($this->jwtData != null){
            if($this->_checkIfUserIsRole("MODERATOR")){
                $this->output['message'] = "No settings found";
                $this->responseCode = "404 Not Found";
                if($id != null){
                    $settingsQuery = $this->db->select("SELECT * FROM setting WHERE name = :name",array(
                        "name" => $id
                    ));
                    $this->output['message'] = "Setting $id not found";
                    $this->responseCode = "404 Not Found";
                    if(count($settingsQuery) > 0){
                        $this->output['message'] = "Displaying $id";
                        $this->responseCode = "200 OK";
                        $this->output['success'] = true;
                        $this->output['data'] = $settingsQuery;
                    }
                }else{
                    $allSettingsQuery = $this->db->select("SELECT * FROM setting");
                    if(count($allSettingsQuery) > 0){
                        $this->output['message'] = "Displaying " . count($allSettingsQuery) . " setting(s)";
                        $this->responseCode = "200 OK";
                        $this->output['success'] = true;
                        $this->output['data'] = $allSettingsQuery;
                    }
                }
            }
        }
    }

    function updateSetting($id = null){
        $this->output['message'] = "Unauthorized to view settings";
        $this->responseCode = "401 Unauthorized";
        if($this->jwtData != null){
            if($this->_checkIfUserIsRole("MODERATOR")){
                $this->output['message'] = "No setting name supplied";
                $this->responseCode = "400 Bad Request";
                if($id != null){
                    $settingsQuery = $this->db->select("SELECT * FROM setting WHERE name = :name",array(
                        "name" => $id
                    ));
                    $this->output['message'] = "Setting $id not found";
                    $this->responseCode = "404 Not Found";
                    if(count($settingsQuery) > 0){
                        $this->output['message'] = "No updateable field supplied supplied";
                        $this->responseCode = "400 Bad Request";
                        if(isset($this->data['bool_value']) || isset($this->data['string_value'])){
                            $columField = (isset($this->data['bool_value'])) ? "bool_value" : "string_value";
                            $valueField = (isset($this->data['bool_value'])) ? $this->data['bool_value'] : $this->data['string_value'];
                            $updateQuery = $this->db->update("setting",array(
                                "name" => $id,
                                $columField => $valueField
                            ),"name= :name");
                            $this->output['message'] = "Setting updated";
                            $this->responseCode = "201 Created";
                            $this->output['success'] = true;
                        }
                    }
                }
            }
        }
    }
    
}