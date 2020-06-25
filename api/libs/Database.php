<?php

/**
 * 
 */
class Database extends PDO
{
	/**
         * Set Database connection
         */
	public function __construct()
	{
		parent::__construct(DB_TYPE.':host='.DB_URL.';dbname='.DB_NAME, DB_USER, DB_PASS);
                parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}
        
        /**
         * 
         * Do a SELECT statement
         * 
         * Placeholders are made using ":"
         * 
         * An example call would be:
         * 
         * $data = array('name' => 'mobieljoy');
         * 
         * $sql = $this->db->select("SELECT id FROM user WHERE username=:name",$data);
         * 
         * print_r($sql) geeft:
         * Array([0] => Array ('id' => 0))
         * [0] Because it's the first result, second result would have index [1]
         * 
         * @param string $sql - The SQL string for the select
         * @param array $data - Optional array in the following format: ("placeholder" => "data")
         * @return Array with arrays
         */
        public function select($sql,$data = array()){
            $sth = $this->prepare($sql);
            foreach($data as $key => $value){
                $sth->bindValue(":$key",$value);
            }
            $sth->execute();
            return $sth->fetchAll();
        }
        
        /**
         * 
         * Do an INSERT statement
         * 
         * An example call would be:
         * 
         * $data = array('username' => 'mobieljoy', 'phonenumber' => '0651622615');
         * 
         * $this->db->insert("user",$data);
         * 
         * @param string $table - The table to insert to
         * @param array $data - Array in the following format: ("placeholder" => "data")
         */
        public function insert($table,$data){
            ksort($data);
            
            $fieldNames = implode('`, `',array_keys($data));
            $fieldValues = ":".implode(', :', array_keys($data));
            
            $sth = $this->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");
            
            foreach($data as $key => $value){
                $sth->bindValue(":$key",$value);
            }
            $sth->execute();
        }
        
        /**
         * 
         * Do an UPDATE statement
         * 
         * Placeholders are made using ":"
         * 
         * An example call would be:
         * 
         * $data = array('userid' => 1, 'phonenumber' => '0612345678');
         * 
         * $this->db->update("user",$data,"id = :userid");
         * 
         * @param string $table - The table to update
         * @param array $data - Array in the following format: ("placeholder" => "data")
         * @param string $where - Where clause in the SQL statement
         */
        public function update($table,$data,$where){
            ksort($data);
            
            $fieldDetails = null;
            foreach($data as $key => $value){
                $fieldDetails .= "`$key`=:$key,";
            }
            $fieldDetails = rtrim($fieldDetails,',');
            
            $sth = $this->prepare("UPDATE $table SET $fieldDetails WHERE $where");
            
            foreach($data as $key => $value){
                $sth->bindValue(":$key",$value);
            }
            $sth->execute();
        }
        
        /**
         * 
         * Do a DELETE statement
         * 
         * Placeholders are made using ":"
         * 
         * An example call would be:
         * 
         * $data = array('userid' => 1);
         * 
         * $this->db->delete("user",$data,"id = :userid");
         * 
         * @param string $table - The table to delete from
         * @param array $data - Array in the following format: ("placeholder" => "data")
         * @param string $where - Where clause in the SQL statement
         * @param int $limit - Optional LIMIT, to prevent deletion of all the records
         */
        public function delete($table,$data,$where,$limit = 1){
            
            $sth = $this->prepare("DELETE FROM $table WHERE $where LIMIT $limit");
            
            foreach($data as $key => $value){
                $sth->bindValue(":$key",$value);
            }
            
            $sth->execute();
            
        }
}