<?php

/**
 * The Base Controller
 */
class Controller{
	
	function __construct(){
		//echo 'Main Controller';
		$this->view = new View();
                Session::init();
	}

	public function loadModel($name){
		$path = 'models/'.$name.'_model.php';

		if(file_exists($path)){
			require 'models/'.$name.'_model.php';
			$modelName = $name.'_model';
			$this->model = new $modelName;
		}

	}

}