<?php

/**
 * 
 */
class PageError extends Controller{
	
	function __construct(){
		parent::__construct();
	}

	function index(){
		$this->view->render('index/index',false);
	}

}