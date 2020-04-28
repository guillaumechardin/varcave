<?php
include_once ('config.inc.php');




class Image
{
    private $imageFile,$imageIndexid,$currentImageTopo = null;
    public $typeTopo,$topoFileName,$topoA4FileName,$topoA4bisFileName,$CRFileName; //topoA4, A4bis, croquis, etc...
    
    function __construct($caviteIndex) 
    {
        if (empty($caviteIndex)) 
        {
            exit('erreur lors de l creation de l\'objet image, aucun index fourni');
        }
        else
        {
            $config = new Configuration();
            $this->imageIndexid =$caviteIndex;
            $this->topoFileName =$config->dossier_racine_topos_cavite . '/' .$this->imageIndexid . '/' .$this->imageIndexid .$config->topographie_prefix;
            $this->topoA4FileName =$config->dossier_racine_topos_cavite . '/' .$this->imageIndexid . '/' .$this->imageIndexid .$config->topographieA4_prefix;
            $this->topoA4bisFileName =$config->dossier_racine_topos_cavite . '/' .$this->imageIndexid . '/' .$this->imageIndexid .$config->topographieA4bis_prefix;
            $this->croquisReperageFileName =$config->dossier_racine_topos_cavite . '/' .$this->imageIndexid . '/' .$this->imageIndexid .$config->croquis_reperage_prefix;
            unset($config);
        }
    }
    
    function rotate($direction)
    {
        if (empty($direction) | empty($this->currentImageTopo))
        {
            exit ('Class/image : Erreur ou ressource sens de rotation invalide');
        }
        if (! file_exists($this->currentImageTopo))
        {
            exit('Class/image : File not found : ' .$this->currentImageTopo);
        }
        
        switch ($direction)
        {
            case 'left';
               $res = imagecreatefromjpeg($this->currentImageTopo);
               $rotate = imagerotate ($res, 90,0);
                if (!imagejpeg($rotate,$this->currentImageTopo,100))
                {
                    exit('Class/image : Erreur lors de la rotation de l\'image left');
                }
            break;
            
            case 'right';
               $res = imagecreatefromjpeg($this->currentImageTopo);
               $rotate = imagerotate ($res, 270,0);
                if (!imagejpeg($rotate,$this->currentImageTopo,100))
                {
                    exit('Class/image : Erreur lors de la rotation de l\'image right');
                }
            break;
            
            default;
                exit('Class/image : Erreur de la demande de rotation de l\'image : ' .$direction);
            break;
        }
    }
    
    function createRessource($typeTopo)
    {
        /*
         * cette fonction permet de définir la variable$currentImageTopo
         * afin de traiter le fichier à l'aide d'une autre Méthode ex: rotate()
         * La fonction 
         */
        if (!$typeTopo)
        {
            exit('Class/image : la methode `createRessource` necessite un argument:
                   topographie, topographieA4, topographieA4bis, croquisReperage');        
        }
       $config = new Configuration();
        switch ($typeTopo)
        {
            case 'topographie':
               $this->currentImageTopo =$this->topoFileName;
            break;
            case 'topographieA4':
               $this->currentImageTopo =$this->topoA4FileName;
            break;
            case 'topographieA4bis':
               $this->currentImageTopo =$this->topoA4bisFileName;
            break;
            case 'croquisReperage':
               $this->currentImageTopo =$this->croquisReperageFileName;
            break;
            default:
                exit('Class/image : la methode `createRessource` necessite un argument:
                   topographie, topographieA4, topographieA4bis, croquisReperage');
            break;
            
        }
    }
    function getCurrentImageTopo()
    {
        /*
         * cette fonction renvoi un nom de fichier/path du
         * pour un fichier sur lequel un action doit être réalisée
         * (rotation, suppression etc...)
         */ 
        return ($this->currentImageTopo);
    }
}
/*
 *$mysqli = new mysqli($config->dbhost,$config->dbuser,$config->dbpasswd,$config->dbname) or exit('Erreur count : ' . mysqli_error());
               $query = 'SELECT ' .$dbField . ' FROM cavites WHERE indexid=' .$pathOrDBindex;
               $result =$mysqli->query($query);
               $mysqliQueryResult->fetch_array(MYSQLI_NUM);
*/
