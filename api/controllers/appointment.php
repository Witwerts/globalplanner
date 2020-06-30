<?php

/**
 * Appointment controller, made for the /appointment endpoint
 */
class Appointment extends Controller{
	
	function __construct(){
		parent::__construct();
	}

	function index($id = null){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $this->model->getAppointment($id);
        }else if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->model->addAppointment();
        }else if($_SERVER['REQUEST_METHOD'] === 'PUT'){
            $this->model->updateAppointment($id);
        }
        $this->view->httpResponseCode = $this->model->getResponse();
        $this->view->output = $this->model->getOutput();
        
        $this->view->render("index/index",false);
    }

    function type($id = null){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $this->model->getAppointmentType($id);
        }else if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->model->addAppointmentType();
        }
        $this->view->httpResponseCode = $this->model->getResponse();
        $this->view->output = $this->model->getOutput();
        
        $this->view->render("index/index",false);
    }
	
}