<?php
	//	require_once 'i18n.class.php';
	require_once(__DIR__ . '/../php-i18n/i18n.class.php');
	require_once(__DIR__ . '/varcave.class.php');
	
	class varcavei18n extends i18n {

		/**
		 * set behaviour of returned string by htmlentities
		 */
		protected $htmlentitiesReturn = true;

		
		
		/**
		 * Override the default function to execute htmlentities on each elements before
		 * sending back strings to main program
		 */
		protected function compile($config, $prefix = '') {
			$code = '';
            $once = true;
			foreach ($config as $key => $value) {
				if (is_array($value)) {
					$code .= $this->compile($value, $prefix . $key . $this->sectionSeparator);
				} else {
					$fullName = $prefix . $key;
					if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $fullName)) {
						throw new InvalidArgumentException(__CLASS__ . ": Cannot compile translation key " . $fullName . " because it is not a valid PHP identifier.");
					}
					if ($this->htmlentitiesReturn == true){
						$code .= 'const ' . $fullName . ' = \'' . str_replace('\'', '\\\'', htmlentities($value,ENT_QUOTES) ) . "';\n";
					}
					else{
						$code .= 'const ' . $fullName . ' = \'' . str_replace('\'', '\\\'', $value ) . "';\n";
					}
				}
			}
			return $code;
		}
		
		public function setFallbackLang($fallbackLang = null) {
			$varcave = new varcave();
			
			$this->fail_after_init();
			if( $fallbackLang == null ){
				$this->fallbackLang = $varcave->getConfigElement('fallbackLanguage');
			}
			else{
				$this->fallbackLang = $fallbackLang;
			};
		}
		
		/**
		 * This enable or disable the htmlentities escaping on init.
		 * @param bool $value true to activate (default) false to disable. 
		 */
		public function setHtmlEntities($value){
			$this->htmlentitiesReturn = $value;
			
			
		}
		
		protected function getConfigFilename($langcode) {
            return str_replace('{LANGUAGE}', strtolower($langcode), $this->filePath);
        }
        
        public function getFilePath()
        {
            return $this->filePath;
        }
        
        //getter for appliedLang
        public function getAppliedLang(){
            return $this->appliedLang;
            
        }
	

	}
	
?>
