<?php

/**
 * The Base Model
 */
class Model{
	
	public $dayIndex = array(
        0 => "Maandag",
        1 => "Dinsdag",
        2 => "Woensdag",
        3 => "Donderdag",
        4 => "Vrijdag",
        5 => "Zaterdag",
        6 => "Zondag"
    );

	function __construct(){
		$this->db = new Database();
	}

	function formatEndpointTime($time){
		return $time;
		//return date(ENDPOINT_TIME_FORMAT,$time);
	}

	function getSettingValue($settingName){
		$query = $this->db->select("SELECT * FROM setting WHERE name=:name",
			array(
				"name" => $settingName
			));
		if($query[0]['string_value'] != null){
			return $query[0]['string_value'];
		}else{
			return $query[0]['bool_value'];
		}
	}

}