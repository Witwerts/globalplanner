<?php

/**
 * Appointment controller, made for the /appointment endpoint
 */
class Appointment extends Controller{
	
	function __construct(){
		parent::__construct();
	}

	function index($include_others = false, $id = null){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $this->model->getAppointment($include_others,$id);
        }
        $this->view->httpResponseCode = $this->model->getResponse();
        $this->view->output = $this->model->getOutput();
        
        $this->view->render("index/index",false);
    }
	
}