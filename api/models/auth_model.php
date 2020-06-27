<?php

//Use JWT library
use \Firebase\JWT\JWT;

class Auth_Model extends Endpoint{
    
    private function _checkIfUserIsRole($roleName){
        if($this->jwtData != null){
            if(strcmp(strtoupper($this->jwtData->data->type),strtoupper($roleName)) == 0){
                return true;
            }
        }
        return false;
    }

    function __construct(){
        parent::__construct();
    }

    function getUsers($type = null){
        if($type == null){
            $query = $this->db->select("SELECT user_id,email,type FROM user");
        }else{
            $query = $this->db->select("SELECT user_id,email,type FROM user WHERE type=:type",array(
                "type" => $type
            ));
        }
        if(count($query) > 0){
            foreach($query as $key => $value){
                $query[$key]['workhours'] = $this->getUserWorkhours($value['user_id']);
            }
            return $query;
        }
        return array();
    }

    function getUserById($user_id = null){
        if($user_id != null){
            $query = $this->db->select("SELECT user_id,email,type FROM user WHERE user_id=:uid",array(
                "uid" => $user_id
            ));
            if(count($query) == 1){
                return $query[0];
            }
        }
        return null;
    }

    function getUserWorkhours($user_id = null){
        $result = array();
        if($user_id != null){
            $query = $this->db->select("SELECT * FROM workhour WHERE user_id=:uid", array(
                "uid" => $user_id
            ));
            if(count($query) > 0){
                foreach($query as $key => $value){
                    $result[$this->dayIndex[$key]] = array(
                        "start_time" => $value['start_time'],
                        "end_time" => $value['end_time']
                    );
                }
            }
        }
        return $result;
    }

    function getInfo(){
        $this->output['message'] = "No JWT supplied";
        $this->responseCode = "400 Bad Request";
        if($this->jwtData != null){
            $this->output['message'] = "Displaying info";
            $this->output['success'] = true;
            $this->output['data'] = $this->jwtData->data;
            $this->responseCode = "200 OK";
        }
    }

    function usersData($type = null){
        $users = $this->getUsers($type);
        $this->output['message'] = "Displaying all " . count($users) . " users";
        if($type != null){
            $this->output['message'] .= " with type $type";
        }
        $this->output['success'] = true;
        $this->responseCode = "200 OK";
        $this->output['data'] = $users;
    }

    function userData($id = null){
        if($id == null){
            $this->output['message'] = "Displaying user info for given auth token";
            $this->output['success'] = true;
            $this->responseCode = "200 OK";
            $this->output['data'] = $this->jwtData->data;
            $this->output['data']->workhour = $this->getUserWorkhours($this->jwtData->data->id);
        }else{
            $user = $this->getUserById($id);
            if($user != null){
                $this->output['message'] = "Displaying user $id";
                $this->output['success'] = true;
                $this->responseCode = "200 OK";
                $this->output['data'] = $user;
                $this->output['data']['workhour'] = $this->getUserWorkhours($id);
            }else{
                $this->output['message'] = "User with given id ($id) does not exist";
                $this->output['success'] = false;
                $this->responseCode = "404 Not Found";
            }
        }
    }

    function displayUser($id = null){
        $this->output['message'] = "User not found";
        if($this->jwtData != null){
            if($id != null){
                if(($this->_checkIfUserIsRole("EMPLOYEE") && $this->getSettingValue("employee_view_all_user")) ||
                    ($this->_checkIfUserIsRole("MODERATOR"))){
                    $this->userData($id);
                }else{
                    $this->output['message'] = "Not allowed to view this";
                    $this->responseCode = "401 Unauthorized";
                }
            }else{
                $this->userData();
            }
        }else{
            $this->output['message'] = "No JWT supplied";
        }
    }

    function displayUsers($type = null){
        $this->output['message'] = "No users found";
        if($this->jwtData != null){
            if(($this->_checkIfUserIsRole("EMPLOYEE") && $this->getSettingValue("employee_view_all_user")) ||
                ($this->_checkIfUserIsRole("MODERATOR"))){
                $this->usersData($type);
            }else{
                $this->output['message'] = "Not allowed to view this";
                $this->responseCode = "401 Unauthorized";
            }
        }else{
            $this->output['message'] = "No JWT supplied";
        }
    }

    //Email
    //Password
    function attemptLogin(){
        $this->output['message'] = "Unknown login information";
        if(isset($this->data['email'],$this->data['password'])){

            $this->responseCode = "200 OK";

            $query = $this->db->select("SELECT * FROM user WHERE email=:mail",
                array(
                    "mail" => $this->data['email']
                )
            );

            if(count($query) == 1){
                $user = $query[0];
                $this->output['message'] = "Invalid password";
                if(password_verify($this->data['password'],$user['password']) == true){
                    $this->output['message'] = "Login successful!";
                    $this->output['success'] = true;
                    $info = array(
                        "iss" => JWT_ISSUER,
                        "iat" => time(),
                        "nbf" => time() + JWT_NOT_USABLE_FOR_SECONDS,
                        "exp" => time() + JWT_USABLE_TIME_SECONDS,
                        "data" => array(
                            "id" => $user['user_id'],
                            "email" => $user['email'],
                            "type" => $user['type'],
                            "expires_at" => time() + JWT_USABLE_TIME_SECONDS
                        )
                    );
                    $this->output['data']['jwt'] = JWT::encode($info,JWT_SECRET_TOKEN);
                    $this->output['data']['expires_at'] = date("Y-m-d H:i:s", (time() + JWT_USABLE_TIME_SECONDS));
                }
            }

        }else{
            $this->responseCode = "400 Bad Request";
        }
    }

    function doRegister($email,$password,$type){
        $query = $this->db->insert("user",
            array(
                "email" => $email,
                "password" => password_hash($password,PASSWORD_BCRYPT),
                "type" => strtoupper($type)
            )
        );
        $this->output['message'] = "User registered";
        $this->output['success'] = true;
        $this->responseCode = "200 OK";
    }

    function attemptRegister(){
        $this->output['message'] = "Unable to register user";
        if(isset($this->data['type'])){
            switch($this->data['type']){
                case "CUSTOMER":
                    //Create new customer
                    //Check if setting allows this or if the user is a moderator
                    if($this->getSettingValue("allow_signup") == true || $this->_checkIfUserIsRole("MODERATOR") == true){
                        $this->doRegister($this->data['email'], $this->data['password'], $this->data['type']);
                    }else{
                        $this->responseCode = "401 Unauthorized";
                    }
                    break;
                case "EMPLOYEE":
                    //Create new employee
                    //Check if this user is a moderator before allowing
                    if($this->_checkIfUserIsRole("MODERATOR") == true){
                        $this->doRegister($this->data['email'], $this->data['password'], $this->data['type']);
                    }else{
                        $this->responseCode = "401 Unauthorized";
                    }
                    break;
                case "MODERATOR":
                    //Create new moderator
                    //Check if this user is a moderator before allowing
                    if($this->_checkIfUserIsRole("MODERATOR") == true){
                        $this->doRegister($this->data['email'], $this->data['password'], $this->data['type']);
                    }else{
                        $this->responseCode = "401 Unauthorized";
                    }
                    break;
                default:
                    $this->responseCode = "400 Bad Request";
                    break;
            }
        }else{
            $this->responseCode = "400 Bad Request";
        }
    }
    
}