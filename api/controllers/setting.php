<?php

/**
 * 
 */
class Setting extends Controller{

	function __construct(){
		parent::__construct();
	}

	function index($id = null){
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->model->getSetting($id);
        }else if($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $this->model->updateSetting($id);
        }
        $this->view->httpResponseCode = $this->model->getResponse();
        $this->view->output = $this->model->getOutput();
        
        $this->view->render("index/index",false);
    }

}