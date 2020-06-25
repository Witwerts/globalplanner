<?php

class Session {

    /**
     * Initiate Session if not already set
     */
    public static function init(){
        if(!isset($_SESSION)){
            session_start();
        }
    }
    
    /**
     * 
     * Get a session variable
     * 
     * @desc Returns session variable if set, otherwise returns false
     * @param string $identifier - The session variable
     * @return mixed
     * 
     */
    public static function get($identifier){
        if(isset($_SESSION[$identifier])){
            return $_SESSION[$identifier];
        }else{
            return false;
        }
    }

    /**
     * 
     * Set a session variable
     * 
     * @param string $identifier - Name of the session variable
     * @param mixed $value - Value of the session variable
     */
    public static function set($identifier,$value){
        $_SESSION[$identifier] = $value;
    }
    
    /**
     * 
     * Check if a session variable is set
     * 
     * @param string $identifier - Session identifier
     * @return boolean
     */
    public static function isset($identifier){
        if(isset($_SESSION[$identifier])){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 
     * Unset a session variable if it's set
     * 
     * @param string $identifier
     * @return boolean
     */
    public static function unset($identifier){
        if(isset($_SESSION[$identifier])){
            unset($_SESSION[$identifier]);
        }
        return true;
    }
    
    /**
     * Destroy the whole session
     */
    public static function destroy(){
        session_unset();
        session_destroy();
    }
    
}

