<?php

/**
 * Main Bootstrap Class
 */
class Bootstrap
{
    
        private $_url = null;
        private $_controller = null;
	
	function __construct(){

        $this->_getUrl();
            
		if(empty($this->_url[0])){
            $this->_loadDefaultController();
            return false;
		}

		$this->_loadPageController();

        $this->_callMethod();
                
	}
        
        private function _loadDefaultController(){
            require 'controllers/index.php';
            $this->_controller = new Index();
            $this->_controller->loadModel('index');
            $this->_controller->index();
        }
        
        private function _callMethod(){
            $length = count($this->_url);
                
            if($length > 1){

                if(!method_exists($this->_controller, $this->_url[1])){
                    $this->_throwError();
                }

                switch($length){

                    case 6:
                        $this->_controller->{$this->_url[1]}($this->_url[2],$this->_url[3],$this->_url[4],$this->_url[5]);
                        break;
                    case 5:
                        $this->_controller->{$this->_url[1]}($this->_url[2],$this->_url[3],$this->_url[4]);
                        break;
                    case 4:
                        $this->_controller->{$this->_url[1]}($this->_url[2],$this->_url[3]);
                        break;
                    case 3:
                        $this->_controller->{$this->_url[1]}($this->_url[2]);
                        break;
                    case 2:
                        $this->_controller->{$this->_url[1]}();
                        break;
                    default:
                        $this->_throwError();
                        break;

                }

            }else{
                $this->_controller->index();
            }
        }
        
        private function _loadPageController(){
            $file = 'controllers/' . $this->_url[0] . '.php';
            if(file_exists($file)){
                require $file;
                $this->_controller = new $this->_url[0];
                $this->_controller->loadModel($this->_url[0]);
            }else{
                $this->_throwError();
            }
        }
        
        private function _getUrl(){
            $url = isset($_GET['url']) ? $_GET['url'] : null;
            $url = filter_var($url,FILTER_SANITIZE_URL);
            $url = rtrim($url,"/");
            $this->_url = explode("/", $url);
        }

	private function _throwError(){
		require 'controllers/pageError.php';
		$this->_controller = new PageError();
		$this->_controller->index();
		exit;
	}
}