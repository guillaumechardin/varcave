<?php
  /**
  * GoogleKML is PHP class used for create Google KML file.
  * @package php_geoclasses
  * @name phpGoogleKML
  *
  * @author Peter Misovic - Thailon
  * @copyright GPL licence
  * @link http://internetgis.net/projects/geoclasses/phpGoogleKML
  * @version 0.2
  *
  * HISTORY
  * ver 0.3
  * + support for adding icon styles
  * ver 0.2 - first oficial version
  * + generate line placemarks added
  * + set of encoding added
  * + set of xmlns version added
  * ver 0.1 - initial version
  * + generate only point palcemarks
  *
  **/


class phpGoogleKML {

  // PROPERTIES
  /**
   * This property defines xmlns version of the KML file. Set the value of this variable only in case when the xmlns version is different than default. Default value is 2.1.
   * @var string
   **/
  var $xmlns_version = "2.1";
  /**
   * This property defines url path to xmlns version specification of the KML file. Set the value of this variable only in case when the url is different than default. Default value is http://earth.google.com/kml/.
   * @var string
   **/
  var $xmlns_url = "http://earth.google.com/kml/";
  /**
   * This property defines encoding of the KML file. Set the value of this variable only in case when is different than default. Default value is UTF-8.
   * @var string
   */
  var $xml_encoding = "UTF-8";
  /**
   * This property defines version of the XML specification of KML file. Set the value of this variable only in case when is different than default. Default value is 1.0.
   * @var string
   */
  var $xml_version = "1.0";
  /**
   * This property defines the name of output folder where KML file will be created. Default directory is script root directory.
   * @var string
   */
  var $outputDirectory = "./";
  /**
   * This property defines the name of KML feed (applies for <name></name> tag).
   * @var string
   */
  var $KML_name = "KML name was not set.";
  /**
   * This property defines the description of KML feed (applies for <description></description> tag).
   * @var string
   */
  var $KML_description = "KML description was not set.";
  /**
  * This property defines the name of KML output file. Default name is GoogleKML.kml.
  * @var string
  */
  var $filename = "GoogleKML_export.kml";
  /**
   * This property contains the resource ID of opened file.
   * @internal
   * @var identifier
   */
  var $resource; 
   
  
  var $errorMessage = ""; var $file_content = ""; var $pointPlacemark = ""; var $linePlacemark = ""; var $footer = ""; var $header = "";
  var $xml_tag = ""; var $xmlns_tag = ""; var $name_tag = ""; var $description_tag;
  
  
  // styled points
  var $stylePointPlacemark = ""; var $style_tag = ""; var $enable_style_tag = false;
  
  // CONSTRUCTOR
  /**
   * Constructor of the class
   *
   * @return void
  */
  function GoogleKML() {}
  
  
  // INTERNAL METHODS
  /**
   * This method validates output directory wheter is writeable and exists.
   * @internal
   */
  function ValidateOutputDirecotry() {
    if (!file_exists($this->outputDirectory)) die('Output directory does not exist! Please create valid directory.');
    if (!is_dir($this->outputDirectory)) die('Not an directory! Please enter valid directory.');
    if (!is_writable($this->outputDirectory)) die('Direcotry is not writable! Please set appertaining permissions.');
  }

  /**
  * This method creates the xml definition tag of Google KML file.
  * @todo to implement validation of all possible encodings to avoid typo.
  * @internal
  */
  function GetXmlTag() {
      $this->xml_tag = "<?xml version=\"".strip_tags(trim($this->xml_version))."\" encoding=\"".strip_tags(trim($this->xml_encoding))."\"?>\n";
    return $this->xml_tag;
  }

  /**
   * This method creates the starting kml tag of Google KML file.
   * @todo to implement validation of all possible xmlns to avoid typo.
   * @internal
   */
  function GetKmlTag() {
    $this->xmlns_tag = "<kml xmlns=\"".strip_tags(trim($this->xmlns_url))."".strip_tags(trim($this->xmlns_version))."\">\n";
    return $this->xmlns_tag;
  }

  /**
   * This method creates the main name tag of Google KML file.
   * @todo to implement validation of all possible xmlns to special chars.
   * @internal
   */
  function GetNameTag() {
    $this->name_tag = "<name>".strip_tags(trim($this->KML_name))."</name>\n";
    return $this->name_tag;
  }

  /**
  * This method creates the main description tag of Google KML file.
  * @todo to implement validation of all possible xmlns to avoid special chars.
  * @internal
  */
  function GetDescriptionTag() {
    $this->description_tag = "<description><![CDATA[".strip_tags(trim($this->KML_description))."]]></description>\n";
    return $this->description_tag;
  }
  
  /**
  * This method converts the latitude from ... to ...
  * @todo to implement ranges.
  * @internal
  */
  function ConvertLatitude($convert_type) {}
  
  /**
  * This method converts the longitude from ... to ...
  * @todo to implement.
  * @internal
  */
  function ConvertLongitude($convert_type) {}
  
  /**
  * This method converts the altitude from ... to ...
  * @todo to implement.
  * @internal
  */
  function ConvertAltitude($convert_type) {}
  
  
  /**
   * This internal method returns KML file header based on user defined (or pre-defined) kml or kml parameters
   *
   * @return string
   */
  function CreateHeader() {
      $this->header .= $this->GetXmlTag(); 
    $this->header .= $this->GetKmlTag();
    $this->header .= "<Document>\n";
    $this->header .= $this->GetNameTag(); 
    $this->header .= $this->GetDescriptionTag();
    $this->header .= $this->GetStyleTag();
      return $this->header;
  }
  
  /**
   * This internal method returns KML file footer (close tags).
   *
   * @return string
   */
  function CreateFooter() {
      $this->footer .= "</Document>\n</kml>";
      return $this->footer;
  }
  
  
  // EXTERNAL METHODS
  /**
   * This external method adds point based placemark into KML file
   *
   * @param string $name the name of the point
   * @param string $description the description of the point
   * @param number $latitude the latitude of the point (valid coordinate format is 48.749123. Do not use N 48�44.946')
   * @param number $longitude the longitude of the point (valid coordinate formats is 21.229502. Do not use E 021�13.771')
   * @param number $altitude the altitude of the point (valid coordinate formats is 125)
   */
  function addPointPlacemark($name,$description,$latitude,$longitude,$altitude) {
    $this->pointPlacemark .= "<Placemark>\n";
    $this->pointPlacemark .= "<name>".$name."</name>\n";
    $this->pointPlacemark .= "<description><![CDATA[".$description."]]></description>\n";
    $this->pointPlacemark .= "<Point><coordinates>".$longitude.",".$latitude.",".$altitude."</coordinates></Point>\n";
    $this->pointPlacemark .= "</Placemark>\n";
    return $this->pointPlacemark;
  }
  
   function addStylePointPlacemark($name,$description,$latitude,$longitude,$altitude,$style_id) {
       if ($this->enable_style_tag) {
        $this->stylePointPlacemark .= "<Placemark>\n";
        $this->stylePointPlacemark .= "<name>".$name."</name>\n";
        $this->stylePointPlacemark .= "<description><![CDATA[".$description."]]></description>\n";
        $this->stylePointPlacemark .= "<styleUrl>#$style_id</styleUrl>";
        $this->stylePointPlacemark .= "<Point><coordinates>".$longitude.",".$latitude.",".$altitude."</coordinates></Point>\n";
        $this->stylePointPlacemark .= "</Placemark>\n";
        return $this->stylePointPlacemark;
       }
  }
  
  /**
   * This external method adds line based placemark into KML file
   * @todo to implement.
   */
  function addLinePlacemark() {}
  
  
  function AddStyle($path_to_icon_file,$style_id) {
      $this->style_tag .= "<Style id=\"$style_id\">\n";
    $this->style_tag .= "<IconStyle>\n";
    $this->style_tag .= "<scale>1.1</scale>\n";
    $this->style_tag .= "<Icon><href>$path_to_icon_file</href></Icon>\n";
    $this->style_tag .= "</IconStyle>\n";
    $this->style_tag .= "</Style>\n";
  }
  
  function GetStyleTag() {
      if ($this->enable_style_tag) {
          return $this->style_tag;
      }
  }
  
  /**
   * This external method creates the KML file
   * @todo add ValidateFile method.   
   *    
   */
  function CreateKMLfile() {
      $this->ValidateOutputDirecotry();
    $this->resource = fopen($this->outputDirectory.$this->filename,"w+");
    if ($this->resource) {
        $this->file_content .= $this->CreateHeader();
        $this->file_content .= $this->pointPlacemark;
        $this->file_content .= $this->stylePointPlacemark;
        $this->file_content .= $this->linePlacemark;
        $this->file_content .= $this->CreateFooter();
          if (!fputs($this->resource, $this->file_content, strlen($this->file_content))) {die('Error during KML file content writing.'); unlink($this->outputDirectory.$this->filename);}
          fclose($this->resource);
      } else {
          die('File resource does not exists.');
      }
  }
  

  /**
   * This external method sends created KML file for download through Content-type header.
   *
   * @param string $download_type Defines the type of download. Possible values are TXT or KML.
   * @todo add str_replace for file extension (from kml to txt)   
   */
  function DownloadKMLfile($download_type) {
      switch ($download_type) {
          case "KML":
            header("Content-type: application/vnd.google-earth.kml+xml");
              header("Content-Disposition: attachment; filename=\"".$this->filename."\"");
              echo $this->CreateHeader();
              echo $this->pointPlacemark;
              echo $this->linePlacemark;
              echo $this->CreateFooter();
          break;
          
          case "TXT":
              header("Content-type: text/text");
              header("Content-Disposition: attachment; filename=\"".$this->filename.".txt\"");
              echo $this->CreateHeader();
              echo $this->pointPlacemark;
              echo $this->linePlacemark;
              echo $this->CreateFooter();
          break;
      }
  }
  
  /**
   * This external method displays created KML file in browser.
   *
   */
  function DisplayKMLfile() {
    print highlight_string($this->CreateHeader().$this->pointPlacemark.$this->linePlacemark.$this->CreateFooter(),1);
  }
}
?>
