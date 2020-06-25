<?php

class Hash {

    /**
     * 
     * Hashing a string
     * 
     * @param string $data - The string to hash
     * @param string $algo - Optional algorithm
     * @param string $key - Optional hashing key
     * @return string
     */
    public static function Create($data, $algo = 'sha256', $key = LOGIN_HASH_KEY){
        
        $context = hash_init($algo, HASH_HMAC, $key);
        hash_update($context, $data);
        
        return hash_final($context);
        
    }
    
    /**
     * 
     * Match a string to a hash
     * 
     * @param string $string - The normal string to match
     * @param string $hash - The already hashed string
     * @param string $algo - Optional algorithm
     * @param string $key - Optional key
     * @return boolean
     */
    public static function Match($string,$hash, $algo = 'sha256', $key = LOGIN_HASH_KEY){
        if($string == Hash::Create($hash, $algo, $key)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 
     * Create a hashed password
     * 
     * @param string $string - The string password to create
     * @return string
     */
    public static function createPassword($string){
        return password_hash(Hash::Create($string), PASSWORD_BCRYPT);
    }
    
    /**
     * 
     * Match a string to a hashed password
     * 
     * @param string $string - The string to match
     * @param string $hash - The password hash
     * @return boolean
     */
    public static function matchPassword($string,$hash){
        return password_verify(Hash::Create($string), $hash);
    }
    

}