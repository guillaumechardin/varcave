<?php
//	require_once 'i18n.class.php';
require_once(__DIR__ . '/../php-i18n/i18n.class.php');
require_once(__DIR__ . '/varcave.class.php');
		
class varcavei18n extends i18n {

    /**
     * set behaviour of returned string by htmlentities
     */
    protected $htmlentitiesReturn = true;

    /* Custom language file
     * This can be use to add a custom lang file that can be merge into main file (this must be ini file for now)
     * It will be automatically setup on init if file exists by default : $langfiledir/custom/$langcode.ini
     * @var string
     */
    protected $customAppliedLang = false;		
    
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
    
    /*
     * extends  default init() method by merging a custom lang file that is not
     * when updating vacarve code with git
     */
    public function init() {
    if ($this->isInitialized()) {
        throw new BadMethodCallException('This object from class ' . __CLASS__ . ' is already initialized. It is not possible to init one object twice!');
    }

    $this->isInitialized = true;

    $this->userLangs = $this->getUserLangs();

    // search for language file
    $this->appliedLang = NULL;
    foreach ($this->userLangs as $priority => $langcode) {
        $this->langFilePath = $this->getConfigFilename($langcode);
        if (file_exists($this->langFilePath)) {
            $this->appliedLang = $langcode;

            //custom lang handling
            $customAppliedLang = dirname($this->langFilePath) . '/local/custom_' . $langcode . '.ini';
            if( file_exists( $customAppliedLang ) ){
                $this->customAppliedLang = $customAppliedLang;
            }
            
            break;
        }
    }
    if ($this->appliedLang == NULL) {
        throw new RuntimeException('No language file was found.');
    }

    // search for cache file        
    $this->cacheFilePath = $this->cachePath . '/php_i18n_' . md5_file(__FILE__) . '_' . $this->prefix . '_' . $this->appliedLang . '.cache.php';


    // whether we need to create a new cache file
    $outdated = !file_exists($this->cacheFilePath) ||
        filemtime($this->cacheFilePath) < filemtime($this->customAppliedLang) || //custom lang was updated
        filemtime($this->cacheFilePath) < filemtime($this->langFilePath) || // the language config was updated
        ($this->mergeFallback && filemtime($this->cacheFilePath) < filemtime($this->getConfigFilename($this->fallbackLang))); // the fallback language config was updated

    if ($outdated) {
        $config = $this->load($this->langFilePath);
        if ($this->mergeFallback)
            $config = array_replace_recursive($this->load($this->getConfigFilename($this->fallbackLang)), $config);

        $compiled = "<?php class " . $this->prefix . " {\n"
            . $this->compile($config)
            . 'public static function __callStatic($string, $args) {' . "\n"
            . '    return vsprintf(constant("self::" . $string), $args);'
            . "\n}\n}\n"
            . "function ".$this->prefix .'($string, $args=NULL) {'."\n"
            . '    $return = constant("'.$this->prefix.'::".$string);'."\n"
            . '    return $args ? vsprintf($return,$args) : $return;'
            . "\n}";

        if( ! is_dir($this->cachePath))
            mkdir($this->cachePath, 0755, true);

        if (file_put_contents($this->cacheFilePath, $compiled) === FALSE) {
            throw new Exception("Could not write cache file to path '" . $this->cacheFilePath . "'. Is it writable?");
        }
        chmod($this->cacheFilePath, 0755);
    }
    require_once $this->cacheFilePath;
}

    /*
     * extends  default load() method by merging a custom lang file that is not
     * when updating vacarve code with git
     */
    protected function load($filename) {
        $ext = substr(strrchr($filename, '.'), 1);
        switch ($ext) {
            case 'properties':
            case 'ini':
                $configMain = parse_ini_file($filename, true);
                
                //merge custom lang file and main file if needed
                if($this->customAppliedLang){
                    $configCustom = parse_ini_file($this->customAppliedLang, true);
                    $config = $this->mergeConfigs($configMain, $configCustom);
                }else{
                    $config = $configMain;
                }
                
                
                break;
            case 'yml':
            case 'yaml':
                $config = spyc_load_file($filename);
                break;
            case 'json':
                $config = json_decode(file_get_contents($filename), true);
                break;
            default:
                throw new InvalidArgumentException($ext . " is not a valid extension!");
        }
        return $config;
    }

    /*
     * Merge main ini config file and custom config file
     */
    private function mergeConfigs($main, $custom){
        foreach($custom as $section => $value)
        {
            if(is_array($value) ){
                foreach($value as $var => $val){
                    $main[$section][$var] = $val;
                }
            }
            else{
                //$section is not a section at all !
                // just a single item in top of ini file
                $main[$section] = $value;
            }
        }
        return($main);
    }


}
	
?>
