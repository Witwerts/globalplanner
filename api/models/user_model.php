<?php

class User_Model extends Model{
    
    private $pages = "";
    private $tekst = array();
    
    function __construct(){
        parent::__construct();
    }

    function getUser($uid = null){
        if($uid == null){
            //Get all users

        }else{
            //Get user with id

        }
        return null;
    }
    
}