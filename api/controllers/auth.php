<?php

/**
 * 
 */
class Auth extends Controller{

	function __construct(){
		parent::__construct();
	}

    //Login array contains (loginId,email,type) or (loginId,password,type)
    //Register array contains (email,type)
	function index(){
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //REGISTER
            $this->model->attemptRegister();
        }else if($_SERVER['REQUEST_METHOD'] === 'GET'){
            //LOGIN
            $this->model->attemptLogin();
        }
        $this->view->httpResponseCode = $this->model->getResponse();
        $this->view->output = $this->model->getOutput();
        
        $this->view->render("index/index",false);
    }
    
    function info(){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $this->model->getInfo();
        }
        $this->view->httpResponseCode = $this->model->getResponse();
        $this->view->output = $this->model->getOutput();
        
        $this->view->render("index/index",false);
    }

    function user($id = null){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $this->model->displayUser($id);
        }
        $this->view->httpResponseCode = $this->model->getResponse();
        $this->view->output = $this->model->getOutput();
        
        $this->view->render("index/index",false);
    }

}