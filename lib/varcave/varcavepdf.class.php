<?php

require_once (__DIR__ . '/varcave.class.php');
require_once (__DIR__ . '/varcaveCave.class.php');
require_once(__DIR__ . '/../tcpdf/tcpdf.php');

//set init of i18n without htmlentities
$i18n = new varcavei18n(__DIR__ . '/../../lang/lang_{LANGUAGE}.ini',  __DIR__ . '/../../langcache/');
$i18n->setPrefix('LNE'); //Lang Not Escaped
$i18n->setFallbackLang(); //set fallback lang to default one specified in config(by using no args)
$i18n->setMergeFallback(true); 
$i18n->setHtmlEntities(false);
$i18n->init();

class VarcavePdf extends TCPDF {
	
	/**
	 * Some static var to handle text size easily
	 */
	 
	const sizeXS = 6;
	const sizeS = 8;
	const sizeM = 10;
	const sizeL = 12;
	const sizeXL = 14;
	const sizeXXL = 18;
	
	/**
	 * Default font
	 */
	protected $defaultFont = 'dejavusans';
	
	/**
	 * Font used in document. Can be change by setFont()
	 */
	protected $font = '';
	
	/**
	 * path to the header image file
	 */
	protected $headerImg = __DIR__ . '/../../img/pdfheader.png';
	
	/**
	 * enable/disable default header on top of page
	 */
	public $noheader = false;
	
	/**
	 * Show footer on bottom of page
	 */
	public $nofooter = true;
 
    /**
	 * Cave data given by user
	 */
	protected $cavedata = false;
	
	/**
	 * Handle page numbering on cave 1st page.
	 * If false a global pdf page number is used. Can be set by setpagegroup().
	 */
	protected $pagegroups = true;
	
	
	/**
	 * margins top margin in mm
	 */
	protected $margintop   = 18;
	protected $marginleft  = 7;
	protected $marginright = 7;
	protected $marginbottom = 7;
	
    //varcave object
    private $varcave = null;
	
	//some constants to process mm to px convertion
	// 1px = 0.264583333 mm
	// 1mm = 3.779527559 px
	const PXTOMM = 0.264583333;
	const MMTOPX = 3.779527559;
	
	// Page footer
	public function Footer() {
	}
	
	
	function __construct($cavedata, $font = false)
	{	
		parent::__construct();
		
        //varcaveObj for log and so on
        $this->varcave = new varcave();
        $this->varcave->logger->debug('Build new PDF env');
        
		//storing cave data
        if($cavedata != false)
        {
            $this->cavedata = $cavedata;
        }
        
		//default doc margins
		$this->SetMargins($this->marginleft, $this->margintop, $this->marginright);
		
		//set page autobreak
		$this->SetAutoPageBreak(TRUE, $this->marginbottom);
		
		//start new default pagegroup on init
		$this->startPageGroup();
		
		//start a new page
		$this->addpage();
		
		//set font
		if( $font == false ){
			$this->font = $this->defaultFont;	
		}
		else{
			$this->font = $font ;
		}
        
	}
	
	/**
	 * add automatic default header to new page
	 */
    public function Header() {
        $this->varcave->logger->debug('Create PDF top header');
		if ($this->noheader){
			return true;
		}
        // Logo
		$this->setFont($this->font, 'BI', 8, '', 'false');
		$this->Image($this->headerImg,4,4,170);
		
		//text box after header image
		$this->RoundedRect(172,4,35,10,3.5,'D');
		$this->SetXY(173,5);
		$this->cell(0,3, LNE::pdf_caveRef . ': ' . $this->cavedata['caveRef'],0);
		$this->SetXY(173,9);
		//If pagegroup is on set group page number, or set a global PDF page number 
		if ( $this->pagegroups == false )
		{
			$this->cell(0,3,LNE::pdf_page. ': '. $this->getAliasNumPage() . '/' .  $this->getAliasNbPages(),0);
		}
		else
		{
			$this->cell(0,3,LNE::pdf_page .': '. $this->getPageGroupAlias(). '-'.$this->getPageNumGroupAlias() ,0);
		}
		
    }

	public 	function caveinfo() {
        $this->varcave->logger->debug('Create PDF cave info');
		//get i18n fields name
		$cave = new varcavecave();
		$i18nfields = $cave->getI18nCaveFieldsName('ONPDF');

		$last =  end($i18nfields);
		//reset($i18nfields);
		
		// iterate throught all i18n fields informations and get
		// corresponding data from the cave obj ()
		$this->SetFont('helvetica', 'B', self::sizeM);
		$this->write(0,LNE::display_caveSpeleometry);
		$this->ln();
		$startPos = $this->getY();
		
		$tbl = '<table cellspacing="0" cellpadding="1" border="0">';
		$tr = true;
		$i = 0;

		foreach($i18nfields as $field)
		{
            
			if($tr)
			{
				$tbl .= '<tr>';
				$tr = false;
			}
			
			if( empty( $this->cavedata[ $field['field'] ] ) )
			{ 
				//skip empty fields
				continue;
			}
            
			//show only 3 item/row
			if ($i < 2)
			{
				if ( strstr( $field['type'] , 'bool') )
				{
					if ( $this->cavedata[ $field['field'] ] == 1 ){
						$this->cavedata[ $field['field'] ]  = LNE::_yes;
					}else{
						$this->cavedata[ $field['field'] ]  = LNE::_no;
					}
				}

                //change editDate format
                if ( $field['field'] == 'editDate' ){
                    $date = new DateTime();
                    $date->setTimestamp($this->cavedata['editDate']);
                    $this->cavedata['editDate']  = $date->format('d/m/Y') ;
                }
				
				$tbl .= '<td><i>' . $field['display_name'] . '</i>: ' .$this->cavedata[ $field['field'] ] . '</td>';
				if ($i == 1 || $last['field'] == $field['field'] ) //if at end of 3rd row or if at end of i18n array
				{
					$tbl .='</tr>';
					$tr = true;
					$i = 0;
					continue;
				}
				$i++;
			}
			
			
		}
		
		//last </tr> tag not added. adding one
		if ($tr == false)
		{
			$tbl .='</tr>';
		}
		$tbl .= '</table>';
        
		$this->SetFont('dejavusans', '',self::sizeS);
		$this->writeHTML($tbl, true, false, false, false, '');
        $text = $cave->getConfigElement('disclaimer');
        $this->MultiCell(190,4,$text,0,'C');
		$endPos = $this->getY();
        
		$this->RoundedRect(5,$startPos,202,$endPos - $startPos,3.5,'D');
		//$this->RoundedRectXY( 5, $startPos, 150, $endPos-$startPos , 0.5, 0.5, $round_corner = '1111');
		
	}
	
	public 	function caveaccess()
	{
        $this->varcave->logger->debug('Add cave access part');
		$cave = new varcavecave();
		
		$this->SetFont('helvetica', 'B', self::sizeM);
		$this->write(0,LNE::display_caveAccessTitle);
		//get start Y position to a later border
		
        $this->ln(4);
		$startPos = $this->getY();
		$this->SetFont('dejavusans', '', self::sizeS);
        
		$sketchAccessArr = $cave->getCaveFileList($this->cavedata['guidv4'],'sketch_access');

        // ** SKETCH ACCESS IMAGE **
        // add an image if one exists or if Google maps api can be used
        // user images have priority over gMaps API. You need to enable the 'Google static MAPS API'
        $maxImgWidth = 70; // resiz img to to 7cm width
        $maxImgHeigth = 50; // 5cm img heigth because of aspect ratio
        
        //get coords to display on img
        $coordsObj = json_decode($this->cavedata['json_coords']);
        $coordCenter = $coordsObj->features[0]->geometry->coordinates;
        
        if( !empty($sketchAccessArr['sketch_access'][0]['file_path']) ) {
            $this->varcave->logger->debug('Add access sketch image from user file');
            $this->Image($sketchAccessArr['sketch_access'][0]['file_path'],$this->marginleft,$this->gety()+1,$maxImgWidth,$maxImgHeigth);
		}
        elseif( !empty( $cave->getConfigElement('use_geoapi_dyn_map_img_pdf') ) ) {
            $this->varcave->logger->debug('Add access sketch image from geo API');

			$caveMarkers = '';
            $i=1;
			$max = count($coordsObj->features) ;
            foreach($coordsObj->features as $coords)
            {
                $caveMarkers .= $coords->geometry->coordinates[1] . ',' . $coords->geometry->coordinates[0] . ',ol-marker' . $i ;
                
                //harcoded limit of 5 markers on small map
                if($i == $max)
                {
                    break;
                }
				$caveMarkers .= '|';
                $i++;
            }


			$center = '&center=' . $coords->geometry->coordinates[1] . ',' . $coords->geometry->coordinates[0];
			$zoom = '&zoom=' . $cave->getConfigElement('ol_zoom_map_lvl'); 
			$size = '&size=370x265';
			$maptype = '&maptype=' . $cave->getConfigElement('select_geoapi_src_img_pdf');
			$marker = '&markers=' . $caveMarkers;
			$url = $cave->getConfigElement('static_map_service_url') . '?' . $center  . $zoom . $size . $maptype . $marker;
			$this->varcave->logger->debug( '  Static map url : [' . $url . ']' );
			
			$imgStaticName = $this->cavedata['guidv4'] . '_static.jpg';
			$imgStaticFilePath = __DIR__ . '/../../' . $cave->getConfigElement('cache_dir') . '/' . $imgStaticName ;
			
		
			if( !file_exists( $imgStaticFilePath ))
			{
				$this->varcave->logger->debug('inexistent file cache [ '. $imgStaticFilePath . '], creating one from url:[' . $url . ']');
                
				if( ! $cave->createCacheFile('url',$url,$imgStaticFilePath) )
				{
					//fail with error message !
					echo 'Fail to create static map cache file</br>';
				}
				
			}
			else{
                if( filesize($imgStaticFilePath) == 0 ){
                    echo 'Cache file empty. Unable to load pdf file<br/>' ;
                }
				$this->varcave->logger->debug('file cache exists');
				$this->varcave->logger->debug('[' . $imgStaticFilePath. ']');
			}


            $this->Image($imgStaticFilePath,$this->marginleft,$this->gety() +1,$maxImgWidth);
        }
        else
        {
            $this->varcave->logger->debug('"No access sketch" image added');
            $this->Image(__DIR__ . '/../../img/nocaveaccesssketch.jpg',$this->marginleft,$this->gety()+1,$maxImgWidth,$maxImgHeigth);
        }
        
        //compute left margin. Some space is use by access sketch
        $this->ln(1);
        $leftMargin = $this->marginleft + $maxImgWidth +1;
        $this->setx($leftMargin);
        //$remWidth = 210 - $this->marginleft - $this->marginright - $maxImgWidth ; //210- marginleft - marginright - imagewidth - imgmargins = 

        $remWidth = 126;
        
        
        //display no access to cave disclaimer
        if($this->cavedata['noAccess']){
            $this->SetTextColor(255, 0, 0); //red
            $this->multicell($remWidth, 0, $cave->getConfigElement('noAccessDisclaimer'),0,'L');
            $this->SetTextColor(0, 0, 0); 
            $this->ln(1);
        }
        
		//display sketch access text
        $this->setx($leftMargin);
        //access text can be long, we use the longest value either height of img or size of txt
        
        $startAccessSketchY = $this->gety();
		$this->multicell($remWidth, 0, $this->cavedata['accessSketchText'],0,'L');
        
        // show coordinates
        $this->ln(1);
        $xCoords = $this->marginleft + $maxImgWidth +1;
        $this->setx($xCoords);
        
        // select the right coordinates system and write coords
        $pdfCoordSystem = $cave->getConfigElement('pdf_coords_system');
        //get alist of available systems
        $geoCoordSysList = $cave->getCoordsSysList();
        $cave->logger->debug('Default coord system is : ' . $pdfCoordSystem);
        
        //load correct System using canonical filename
        $convCoords = array();
        foreach( $geoCoordSysList as $system ) {
            if( $system['name'] == $pdfCoordSystem ){ //load only the required one
                $systemLibName = './lib/varcave/' . $system['php_lib_filename'];
                if( file_exists($systemLibName) ) {
                    $cave->logger->debug('Load lib : [' . $systemLibName . ']');
                    if( include_once($systemLibName) )
                    {
                        //convert from longlat (geographical is the default stored in geoJson) to desired coordinate system
                        $funcName = 'convert2' . $system['name'];
                        $cave->logger->debug('start converting coords on target coordSystem');
                        foreach($coordsObj->features as $coord){
                            $convCoords[] = $funcName( $coord->geometry->coordinates);
                        }
                    }else{
                        $cave->logger->debug('Failed to load library : [' . $systemLibName . ']');
                    }
                    
                }
            }
            else{
                $cave->logger->debug( $system['name'] . ' is not the default coord. system, do not load lib.' );
            }
        }            

        //Sources coords format is long/lat
        //show coords in the right system
        $this->multicell($remWidth, 0, LNE::display_caveCoords . ':',0,'L');
        
        $cave->logger->debug('display coords');
        foreach($convCoords as $key => $coords)  {
            $this->multicell($remWidth, 0, $coords['string'], 0, 'L', false, 1, $xCoords + 3);
        }
        
        
        $textSize = $this->gety() - $startAccessSketchY;
        if( $textSize > $maxImgHeigth)
        {
            $this->sety($startAccessSketchY +$textSize);  //last know Y pos + 50mm size  img
        }
        else
        {
            $this->sety($startAccessSketchY + $maxImgHeigth);  //last know Y pos + 50mm size  img
        }
        
        $endPos = $this->gety() + 3;
        
		$this->RoundedRect(5,$startPos,202,$endPos - $startPos,3.5,'D');
		
		//insert cave description
		$this->ln(3);
		$this->SetFont('helvetica', 'B', self::sizeM);
		$this->write(0,LNE::display_caveDescription);
		//get start Y position to a later border
		
        $this->ln(4);
		$startPosCaveDescr = $this->getY();
		$this->SetFont('dejavusans', '', self::sizeS);
		$borders = array('TLRB' => array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		$this->multicell(216 - $this->marginleft - $this->marginright, 0, $this->cavedata['shortDescription'] ."\n\r\n\r", $borders,'L', false,1,$this->marginleft - 2);
		//$this->multicell(215 - $this->marginleft - $this->marginright, 0, $this->cavedata['annex'],1,'L', false,1,$this->marginleft - 1);
		$endPosCaveDescr = $this->gety() + 3;
		//$this->RoundedRect(5,$startPosCaveDescr,202,$endPosCaveDescr - $startPosCaveDescr,3.5,'D');
	}
	
    public 	function addcavemaps()
    {
        // This function add pages for cave maps as needed
        $cave = new varcavecave();
        $sketchAccessArr = $cave->getCaveFileList($this->cavedata['guidv4'],'cave_maps');
        
		if (!empty($sketchAccessArr['cave_maps']) )
		{
			foreach($sketchAccessArr['cave_maps'] as $index => $imgFile)
			{
				
				//adapt rotate im to fit into page
				$imgInfo = getimagesize($imgFile['file_path']);
			
				//w to h ratio : 
				$imgRatio = $imgInfo[0]/$imgInfo[1];
				
				//if img width > img heigth we use a landscape format for the page
				if($imgInfo[0]  > $imgInfo[1]  )
				{
					$this->addpage('L');
				}
				else
				{
					$this->addpage('P');
				}
				
				//img left over space
				$imgLeftOverMm = array(
								$this->getPageHeight() - $this->margintop - $this->marginbottom - 2 ,// 2mm for space over img
								$imgLeftOver = $this->getPageWidth() - $this->marginleft - $this->marginright - 2 // 2mm for space over img
								);
				//convert to px
				$imgLeftOverPx = array(
									$this->convMmToPx($imgLeftOverMm[0]),
									$this->convMmToPx($imgLeftOverMm[1])
								);
				
				//compute resize
				if ($imgInfo[0] > $imgLeftOverPx[0] || $imgInfo[1] > $imgLeftOverPx[1] )
				{
					//echo 'img to big: '.$imgInfo[0].'x'.$imgInfo[1]. '(max='.$imgLeftOverPx[0].'x'.$imgLeftOverPx[1].')<hr>';
					
					//try to resize img on width
					$resizeRatio = $imgLeftOverPx[0] / $imgInfo[0] ;
					
					//check if computed resized img is to big in this case we resize from height
					if($imgInfo[1] * $resizeRatio < $imgLeftOverPx[1] )
					{
						//resize on width is ok
						$x = $imgLeftOverPx[0];
						$y = $imgInfo[1] * $resizeRatio;
					}
					else
					{
						//resize by heigth
						$resizeRatio = $imgLeftOverPx[1] / $imgInfo[1] ;
						$x = $imgInfo[0] * $resizeRatio;
						$y = $imgLeftOverPx[1];
					}
					
					
					
				}
				else
				{
					//echo 'img ok';
					$x = $imgInfo[0];				
					$y = $imgInfo[1];				
				}
				
				//#######echo 'max size :' .$imgLeftOverPx[0].'x'.$imgLeftOverPx[1].'<br>';
				//#######echo 'new size ;' .$x.'x'.$y.'<br><br>';
				
				//$this->Image($imgFile,$this->marginleft,$this->margintop,  $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
				$this->Image($imgFile['file_path'],$this->marginleft,$this->margintop, $x, $y,  '',  '', '', '', '', '', false,  false, 0,  false,  false, /*fit on page*/ true,  false,  array());
			}
		}
    }
	
	/* convPxToMm
	 * this function convert n px to mm
	 * @param    $px  : int number of px to convert  in mm
     *           
	 * @return   : int number of mm
	 */
	public function convPxToMm($px)
	{
		
		return $px * self::PXTOMM;
	}
	
	/* convMmToPx
	 * this function convert n mm to px
	 * @param    $mm  : int number of mm to convert  in px
     *           
	 * @return   : int number of px
	 */
	public function convMmToPx($mm)
	{
		return $mm * self::MMTOPX;
	}
    
    /*
     * set cavedata
     */
     public function setCavedata($cavedata)
     {
            $this->cavedata = $cavedata;
     }
}	

?>
