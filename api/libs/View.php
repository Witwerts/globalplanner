<?php

/**
 * The Main View Class
 */
class View{
	
        private $_customStyles = array();
        private $_metaTags = array();
        private $_jsScriptsTop = array();
        private $_jsScriptsBottom = array();
        private $_jsStrings = array();
    
	function __construct(){
                $this->output = "";
                $this->httpResponseCode = "404 Not Found";
	}
        
        /**
         * 
         * Add a short js script in the footer
         * 
         * @param string $javascript - Javascript language
         */
        
        public function addJsString($javascript){
            array_push($this->_jsStrings,$javascript);
        }
        
        /**
         * 
         * Load a custom js file in page header
         * 
         * @param string $file - File located in public/js folder. Eg: 'custom.js'
         */
        
        public function addJsScriptTop($file){
            array_push($this->_jsScriptsTop,$file);
        }
        
        /**
         * 
         * Load a custom js file in page footer
         * 
         * @param string $file - File located in public/js folder. Eg: 'custom.js'
         */
        
        public function addJsScriptBottom($file){
            array_push($this->_jsScriptsBottom,$file);
        }
        
        /**
         * 
         * Add a Stylesheet to page
         * 
         * @param string $file - The file located in the public/css folder. Eg: 'custom.css'
         * 
         */
        public function addCustomStyle($file){
            array_push($this->_customStyles,$file);
        }
        
        /**
         * 
         * Add a metatag to the page
         * 
         * @param string $name - The meta name
         * @param string $desc - The meta description
         * 
         */
        public function addMetaTag($name,$desc){
            $this->_metaTags[$name] = $desc;
        }

	public function render($name, $noInclude = null){
            
                if($noInclude === null){
                        require 'views/head.php';
                        require 'views/header.php';
			require 'views/'.$name.'.php';
			require 'views/footer.php';
                        return false;
                }
		if($noInclude == true){
                        require 'views/head.php';
			require 'views/'.$name.'.php';
                        return false;
		}
                if($noInclude == false){
			require 'views/'.$name.'.php';
                        return false;
		}

	}
}