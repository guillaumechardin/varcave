<?php
class VarcavePdf extends TCPDF {
	
	//$caviteid = '';
	
	var $addFooterOnPage = true;
	
	var $addHeaderOnPage = true;

    //Page header
    public function Header() 
    {
		if ($this->addHeaderOnPage == true)
		{
        // Logo
			$this->SetFont('dejavusans', 'BI', 9, '', 'false');
            $this->Image('img/entetePDF.png',4,4,170);
			// $this->RoundedRect(5, 5, 170, 10, 3.5, 'D');
			//affichage l'information après l'entete de page
            $this->RoundedRect(172,4,35,10,3.5,'D');
            $this->SetXY(173,5);
            $this->cell(0,4,'Fiche n°: ' . $this->caviteCouranteNum,0);
            $this->ln(4	);
            $this->SetX(173);
            //si on est en mode page group on affiche les groupe de page
			if (empty($this->pagegroups))
			{
				$this->cell(0,4,'Page : '. $this->getAliasNumPage() . '/' .  $this->getAliasNbPages(),0);
			}
			else
			{
				$this->cell(0,4,'Page : '. $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias(),0);
			}

		}
		else
		{
			//pas d'header
		}
    }	

    // Page footer
    public function Footer()
    {
        /*Position at 15 mm from bottom
        //$this->SetY(-15);
        // Set font
        //$this->SetFont('helvetica', '', 10);
        // Page number
        //$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        */
        if ($this->addFooterOnPage == true)
		{
			if ($this->livretPdf == true)
			{
				$this->SetY(-7);
				$this->SetFont('dejavusans', '', 5);
				$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'C', 'C');
			}
		}
		else
		{
			//pas de bas de page
			//$this->addFooterOnPage = true;
		}
    }
    
 
    
    function tailleMaxImage($imagePath, $xMax, $yMax)
    {
            /*
             * cette fonction renvoi une largeur maximum pour une image 
             * afin qu'elle rentre dans un cadre de taille X*Y dans le fichier PDF
             * Les $xMax et $yMax sont en mm une aproxmation en px est réalisée :
             * 1mm = 3.779528 px
             */

            if (!file_exists($imagePath))
            {
                exit('Erreur image : ' . $imagePath . 'non existante : {' . $imagePath . '}');
            }
            
            $imgSize = getimagesize($imagePath);
            
            //conversion des px en mm
            $imgSize['X'] = $imgSize[0];
            $imgSize['Y'] = $imgSize[1];

            // Determine aspect ratio
            $ratio0['XY'] = $imgSize['X']/ $imgSize['Y'] ;
            $ratio0['YX'] = $imgSize['Y'] / $imgSize['X'];
            
            // on conserve le ration le plus faible afin de faire une réduction proportionnelle
            $ratio = min($ratio0);
            
            $sensReduc = '';
            
            if ( $imgSize['X'] > $xMax ) 
			{
				$t_x = $xMax;
				$t_y = $xMax * $imgSize['Y'] / $imgSize['X'];
				$sensReduc .= ' sens X ';
				
				//Si la seule reduc de Y ne permet pas d'avoir la
				//taille max désirée on réduit aussi par Y
				if ($t_y > $yMax)
				{
					$t_x = $yMax * $t_x / $t_y	;
					$t_y = $yMax;
					$sensReduc .= ' et sens Y ';
				}
				
				$x = $t_x;
				$y = $t_y;	
			}

			// seul l'axe Y est > à Ymax
			elseif ($imgSize['Y'] > $yMax) 
			{
				$x = $yMax * $imgSize['X'] / $imgSize['Y'];
				$y = $yMax;
				$sensReduc .= ' sens Y ';
			}
			
			//image déjà à la bonne taille
			else
			{
				$x = $imgSize['X'];
				$y = $imgSize['Y'];
				$sensReduc .= ' aucune réduction ';
			}

			
            $imgTailleReduite['X'] = intval($x);
            $imgTailleReduite['Y'] = intval($y);
            
           
           if ( false ) 
           {
				echo 'Fichier : ' . $imagePath . '<br>
				Taille max : x : ' . $xMax . ' y : ' . $yMax . '<br>
				Taille origine : x : ' . $imgSize['X'] . 'px - y : ' . $imgSize['Y'] . 'px <br>
				ratioXY : ' . $ratio0['XY'] . ' ratioYX : ' . $ratio0['YX'] . '<br>
				ratio : ' . $ratio . '<br>
				x : ' . $imgTailleReduite['X'] . ' y : ' . $imgTailleReduite['Y'] . '<br>
				sens de reduction' . $sensReduc . '<br><br>';
			}
            return $imgTailleReduite;
        }
    
    /*
     * créé la première page :
     * 
     * 
     */
    //public function premierePage(mixed $caviteCourante)
    public function pageInfoCavite($caviteCourante,$config)
    {
		$this->addpage('P','A4',true);
		/*
		 * première partie de la page concernant la cavité.
		 * Le cadre noir sera créé à la fin entre 
		 * Y = 16mm et (getY() + 1mm) en fin d'affichage
		 */
		
		//on redéfini la marge à 7mm du bord droit pour éviter de faire un setX sur toutes les lignes
		$this->SetXY(7,18);
		$YdebutCadreInformation = $this->getY();
		
		$YwarningInaccessible = 0;  //pour augmenter la taille du cadre si le champ inaccessible est défini
		$YwarningHaut = $this->getY();
		if($caviteCourante->inaccessible | $caviteCourante->coord_aleat)
		{
			$this->SetFont('dejavusans','B',9);
			$msgAleat = '';
			if ($caviteCourante->coord_aleat)
			{
				$msgAleat = '. Les coordonnées indiquées sont erronnées.';
			}
			$this->SetTextColor(255,0,0); //en rouge
			$this->multiCell(0,4,$config->inacessibleDisclaimer . '' . $msgAleat,0,'C',false);
			$this->ln(round($this->getFontSize(),2));
			$this->setX(7); //on défini une marge à 7mm sinon la rubrique "nom" est décalée.
			$YwarningInaccessible = $this->getY() - $YwarningHaut ;  //pour augmenter la taille du cadre si le champ inaccessible est défini
		}
		
		
		$this->SetTextColor(0);
		//marge à 109 pour forcer les retour chariots de Multicell au milieu de la page
		$this->SetMargins(7,4,109,4);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Nom : ');
		if ($this->livretPdf == true)
		{
			$this->Bookmark($caviteCourante->nom,'1');
		}
		$this->SetFont('dejavusans','B',10);
		$this->multiCell(0,4,$caviteCourante->nom,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Commune : ');
		$this->SetFont('dejavusans','B',10);
		$this->multiCell(0,4,$caviteCourante->commune,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Massif : ');
		$this->SetFont('dejavusans','B',10);
		$this->multiCell(0,4,$caviteCourante->massif,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Secteur : ');
		$this->SetFont('dejavusans','B',10);
		$this->multiCell(0,4,$caviteCourante->secteur,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Lieu-dit : ');
		$this->SetFont('dejavusans','B',10);
		$this->multiCell(0,4,$caviteCourante->lieu_dit,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Géologie : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->geologie,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Hydrologie : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->hydrologie,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Inventeurs : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->inventeur,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'CO² : ');
		$this->SetFont('dejavusans','',10);
		if($caviteCourante->CO2)
		{
			$co2 = 'oui';
		}
		else
		{
			$co2 = 'non';
		}
		$this->multiCell(0,4,$co2,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Cavité brochée : ');
		$this->SetFont('dejavusans','',10);
		if($caviteCourante->brochage)
		{
			$brochage = 'oui';
		}
		else
		{
			$brochage = 'non';
		}
		$this->multiCell(0,4,$brochage,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Zone natura 2000 : ');
		$this->SetFont('dejavusans','',10);
		if($caviteCourante->zone_natura_2000)
		{
			$zone_natura_2000 = 'oui';
		}
		else
		{
			$zone_natura_2000 = 'non';
		}
		$this->multiCell(0,4,$zone_natura_2000,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Courant d\'air : ');
		$this->SetFont('dejavusans','',10);
		if($caviteCourante->courant_air)
		{
			$air = 'oui (' . $caviteCourante->date_courant_air . ')' ;
		}
		else
		{
			$air = 'non';
		}
		$this->multiCell(0,4,$air,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Explorateurs : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->explorateurs,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Bibliographie : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->bibliographie,0,'L',false);
		
		$hauteurColonne1 = $this->getY() - $YdebutCadreInformation;
		/*
		 * deuxieme colonne de page
		 */
		$this->SetXY(110,18 + $YwarningInaccessible);
		$this->SetMargins(110,4,7,4);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Profondeur : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,(int)$caviteCourante->profondeur_max . 'm' ,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Developpement : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,(int)$caviteCourante->developpement . 'm',0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Topographe : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->topographe,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Carte IGN : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->carteIGN,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Coordonnées UTM/WGS84 : ' . "\n");
		/*
		 * on affiche les coordonnées UTM et/ou Lambert 
		 * police de caractère réduite :
		 */
		 
		$this->SetFont('dejavusans','',8);
		
		$nbrMaxCoord = 3;
		// on cronstruit un tableau avec toutes les coordonnées afin de les implementer
		// dans le gpx/kml
		for ($i=0; $i < $nbrMaxCoord ; $i++)
		{
			$xname = 'X_UTM_WGS84_'.$i; 
			$yname = 'Y_UTM_WGS84_'.$i;
			$zonename = 'zone_UTM_'.$i;
			$zname = 'Z_'.$i;
			if ($caviteCourante->$xname && $caviteCourante->$xname)
			{
				$this->multicell(0,3,'  Zone=' . $caviteCourante->$zonename . ' X=' . str_pad($caviteCourante->$xname, 7, "0", STR_PAD_LEFT)  . ' Y=' . str_pad($caviteCourante->$yname, 8, "0", STR_PAD_LEFT) .' Z=' .  $caviteCourante->$zname,0,'L',false);
			}
		}	
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Coordonnées LambertIII : ' . "\n");
		$this->SetFont('dejavusans','',8);
		
		$nbrMaxCoord = 3;
		// on cronstruit un tableau avec toutes les coordonnées afin de les implementer
		// dans le gpx/kml
		for ($i=0; $i < $nbrMaxCoord ; $i++)
		{
			$xname = 'X_lambert_'.$i; 
			$yname = 'Y_lambert_'.$i;
			$zname = 'Z_'.$i;
			if ($caviteCourante->$xname && $caviteCourante->$xname)
			{
				$this->multicell(0,3,'  X=' . str_pad($caviteCourante->$xname, 7, "0", STR_PAD_RIGHT) . '   Y=' . str_pad($caviteCourante->$yname, 7, "0", STR_PAD_RIGHT)  .' Z=' .  $caviteCourante->$zname,0,'L',false);
			}
		}
		
		$this->SetFont('dejavusans','',10);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Date d\'exploration : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->date_exploration,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Documents d\'origine : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->document_origine,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Dernière modification : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4, substr($caviteCourante->fiche_modif_le,0,10) ,0,'L',false);
		
		$this->SetFont('dejavusans','',10);
		$this->write(4,'Additif : ');
		$this->SetFont('dejavusans','',10);
		$this->multiCell(0,4,$caviteCourante->additif,0,'L',false);
		
		$hauteurColonne2 = $this->getY() - $YdebutCadreInformation;
		
		
		//dessin du rectangle de contour
		if($hauteurColonne1 > $hauteurColonne2)
		{   
			$hCadreInformation = $hauteurColonne1 + round($this->getFontSize(),2);
			$this->RoundedRect(5,16,202,$hCadreInformation,3.5,'1000');
		}
		else
		{
			$hCadreInformation = $hauteurColonne2 + round($this->getFontSize(),2);
			$this->RoundedRect(5,16,202,$hCadreInformation,3.5,'1000');
		}
		
		
		/*+--------------------------------------------------------------------------------------------+*/
		
		/*
		 * Cadre 2 situation/accès
		 * Contient l'image croquis de repérage si elle existe et
		 * le texte croquis de repérage.
		 */
		//le début du cadre se fait à quelques 1 mm au dessous du précédent
		$YDebutSituationAcces = $YdebutCadreInformation + $hCadreInformation ;
		
		/*
		 * La nouvelle position verticale du texte est définie depuis la fin du cadre
		 * entourant la descrption de la cavité
		 */
		$this->setXY(7,$YDebutSituationAcces);
		
		$this->SetMargins(7,4,4,4);
		$this->SetFont('dejavusans','BU',10);
		$this->multicell(0,4,'Situation/accès : ',0,'L',false);
		$YcroquisReperageEtTxt = $this->getY() ;// $this->getfontsize() ;
		
		/*
		 * modification des marges du document
		 * 85mm correspond à la longeur  d'une image de croquis
		 */    
		$this->SetMargins(85,4,7,4);
		//affichage de l'image du croquis de repérage
		
		if(!$caviteCourante->croquis_reperage_path)
		{
			/*
			 * l'image n'existe pas on affiche une image particuelière
			 */
			$this->image('img/pasDeCroquis_pdf.png',18,$YcroquisReperageEtTxt);
			$tailleImage = getimagesize('img/pasDeCroquis_pdf.png');
			//saut de ligne pour ne pas afficher l'image sur du texte
			$this->ln($tailleImage['1'] * 0.35);
			$YfinSituationAccesImg = $this->getY() ;
			
		}
		else
		{
			/*
			 * on affiche l'image du croquis qui existe et on limite à une largeur de 80mm
			 * et une  hauteur de 40mmm
			 *
			 * conversion px vers mm   1px = 0.264583333mm
			 *  80mm / 0.264583333 = 302px
			 *	40mm / 0.264583333 = 151px
			 */
			$tailleMaxX = 302;
			$tailleMaxY = 151;
			
			$myRatioCroquis = $this->tailleMaxImage($caviteCourante->croquis_reperage_path, $tailleMaxX, $tailleMaxY);
			
			//conversion des px en mm
			$myRatioCroquis['X'] = intval($myRatioCroquis['X'] * 0.264583333);
			$myRatioCroquis['Y'] = intval($myRatioCroquis['Y'] * 0.264583333);
			
			
			//print_r($myRatioCroquis);
			//exit('');
			// Centrage image 
			$longMax = 80 + 8  ;//80 + 4mm marge 
			//$centrageImageLongueur = long. max - marge - tailleImage/2;
			$centrageImageX = ($longMax - $myRatioCroquis['X'])/2;
			$this->image($caviteCourante->croquis_reperage_path,$centrageImageX,$YcroquisReperage,$myRatioCroquis['X'],$myRatioCroquis['Y']);
			$this->ln($myRatioCroquis['Y']);
			$YfinSituationAccesImg = $this->getY() ;
		
			
		}
		$this->setY($YcroquisReperageEtTxt);
		$this->SetFont('dejavusans','',8);
		$this->multiCell(0,4,$caviteCourante->croquis_de_reperage_txt,0,'L',false);
		$YfinSituationAccesTxt = $this->getY();
		
		
		//dessin du rectangle de contour
		if($YfinSituationAccesImg > $YfinSituationAccesTxt)
		{   
			$hCadreSituationAcces = $YfinSituationAccesImg - $YDebutSituationAcces + round($this->getFontSize(),2);
			$this->RoundedRect(5,$YDebutSituationAcces,202,$hCadreSituationAcces,3.5,'1000');
		}
		else
		{
			$hCadreSituationAcces = $YfinSituationAccesTxt - $YDebutSituationAcces + round($this->getFontSize(),2);
			$this->RoundedRect(5,$YDebutSituationAcces,202,$hCadreSituationAcces,3.5,'1000');
		}
		
		//on défini la suite du document au bas du cadre situation/acces.
		$h = $YDebutSituationAcces + $hCadreSituationAcces + 1;
		$this->setY($h);
		
		/*
		 * section description sommaire de la cavité
		 */
		//remise a la normale des marges :
		$this->SetMargins(7,4,4,4);
		//définition des coordonées verticale du début du cadre 
		$YdebutCadreDescriptionSommaire = $this->getY();
		$this->setXY(7,$YdebutCadreDescriptionSommaire );
		
		//debut du contenu du cadre
		$this->SetFont('dejavusans','BU',10);
		$this->multicell(0,4,'Description cavité :',0,'L',false);
		
		$this->SetFont('dejavusans','',8);
		$this->multicell(0,4,$caviteCourante->description_sommaire_cavite,0,'L',false);
		
		//Tracé du cadre 
		//$hauteur avec 1mm en plus pour eviter le chevauchement du texte et du cadre
		$hCadreDescriptionSommaire = $this->getY() - $YdebutCadreDescriptionSommaire + round($this->getFontSize(),2);
		$this->RoundedRect(5,$YdebutCadreDescriptionSommaire,202,$hCadreDescriptionSommaire,3.5,'1000');
		
		//on défini la suite du document au bas du cadre description sommaire de la cavité
		$h = $YdebutCadreDescriptionSommaire + $hCadreDescriptionSommaire +1 ;
		$this->setY($h);
		
		/*************************** TOPOGRAPHIE ************************/
		/*
		 * image topo de la cavité
		 */
		//le cadre n'est pas tracé
		$YdebutCadreTopographie = $this->getY();
		//echo $YdebutCadreTopographie;
		if($caviteCourante->topographie_path)
		{
			//$this->setY($YdebutCadreTopographie);
			//debut du contenu du cadre
			$this->SetFont('dejavusans','BU',10);
			$this->multicell(0,4,'Topographie :',0,'L',false);
			
		
			/*
			 * calcul de la place restante pour afficher la topo
			 * 297 - margeBas (pas celle du haut car comprise dans YdebutCadreTopographie  - $YdebutCadreTopographie
			 * approximation à 297 - 7  - $YdebutCadreTopographie
			 * et largeur 210 - 10
			 */
			$hMaxTopographie = 290 - $YdebutCadreTopographie;
			
			/*
			 * on affiche l'image qui existe et on limite sa largeur X et Y .
			 *   X = 194mm
			 *   Y = $hMaxTopographie
			 * 
			 * conversion px vers mm   1px = 0.264583333mm
			 *  194mm / 0.264583333 = 733px
			 *	$hMaxTopographie / 0.264583333 = tailleMaxY
			 */
			$tailleMaxX = 733;
			$tailleMaxY = intval($hMaxTopographie / 0.264583333);
			
			$myRatioTopographie = $this->tailleMaxImage($caviteCourante->topographie_path, $tailleMaxX, $tailleMaxY );
			
			//on converti à nouveau les px en mm:
			$myRatioTopographie['X'] = intval($myRatioTopographie['X'] * 0.264583333);
			$myRatioTopographie['Y'] = intval($myRatioTopographie['Y'] * 0.264583333);
			
			$centrageTopoX = (210 - $myRatioTopographie['X']) /2 ;
			/*
			 * $centrageTopoY = $YdebutCadreTopographie + 5mm + (($tailleMaxY - $myRatio['Y']) /2);
			 *                                           +5mm pour eviter d'ecrire sur "Topographie :"
			 */
			$centrageTopoY = $YdebutCadreTopographie + 5 + (($hMaxTopographie - $myRatioTopographie['Y']) /2);
			
			
			$this->image($caviteCourante->topographie_path,$centrageTopoX,$centrageTopoY,$myRatioTopographie['X'],$myRatioTopographie['Y']);
			//sety +1 pour ne pas écrire sur la ligne du cadre
			
			//fin du cadre qq milimetre au dessous de la topo
			$YmaxCadreTopographie = ($tailleMaxY - $myRatioTopographie['Y']) + $myRatioTopographie['Y']  + 4;
			//texte des credit topo 2mm au dessus du cadre topo
			$YcreditTopographe = $YdebutCadreTopographie + $tailleMaxY ;
			$this->sety($YcreditTopographe);
			$this->SetFont('dejavusans','',7);
			$this->multicell(0,4,'Topographes : ' . $caviteCourante->topographe,0,'R',false);
			$hfinCadreTopographie = $hMaxTopographie + 5 ; //le +5mm correspond au décalage de $centrageTopoY
			$this->RoundedRect(5,$YdebutCadreTopographie,202,$hfinCadreTopographie,3.5,'1000');
		}
		
		
		
		
		/******************************************DEBUG**********************************/
		
		if (false)
		{
		
		$this->SetFontsize(12);
		$imgSizeTopographie = getimagesize($caviteCourante->topographie_path );
		$this->debugPdfTxt = '
		 **** DESCRIPTION ****
		YdebutCadreInformation : ' . round($YdebutCadreInformation,2) . "
		hauteur colonne1 calculée : " . round($hauteurColonne1,2) . "
		hauteur derniere ligne colonne2 : " . $myvar2 . "
		hauteur colonne2 calculée : " . round($hauteurColonne2,2) . "
		taille de la police : " . round($this->getFontSize(),2) . "mm
		h cadre description cavité : " . round($hauteurCadreDescription,2) . "
		
		**** Situation/accès ****
		YDebutSituationAcces : " . $YDebutSituationAcces . "
		YfinSituationAccesTxt : " . $YfinSituationAccesTxt . "
		YfinSituationAccesImg : " . $YfinSituationAccesImg . "
		h cadre SituationAcces : " . $hCadreSituationAcces. "
		myRatioCroquis : Xmax:" . $myRatioCroquis['X'] . '  Ymax:' . $myRatioCroquis['Y'] . "
		
		
		**** DESCRIPTION  SOMMAIRE CAVITE****
		YdebutCadreDescriptionSommaire : " . $YdebutCadreDescriptionSommaire . "
		
		**** TOPOGRAPHIE  *********
		Chemin fichier : " . $caviteCourante->topographie_path . "
		YdebutCadreTopographie : " . $YdebutCadreTopographie . "
		hfinCadreTopographie : " . $hfinCadreTopographie . "
		hMaxTopographie : " . $hMaxTopographie . "
		tailleMaxX = " . $tailleMaxX . "
		tailleMaxY = " . $tailleMaxY . "
		myRatioTopographie['X'] = " . $myRatioTopographie['X'] . "
		myRatioTopographie['Y'] = " . $myRatioTopographie['Y'] . "
		centrageTopoX : " . $centrageTopoX . "
		centrageTopoY : " . $centrageTopoY . "
		myRatioTopographie : Xmax:" . $myRatioTopographie['X'] . '  Ymax:' . $myRatioTopographie['Y']  . "<br>".   
		"$centrageTopoY = $YdebutCadreTopographie + 5 + (($hMaxTopographie - $myRatioTopographie[Y]) /2);
		
		";
		$this->addpage();
		$this->ln(20);
		$this->setX(8);
		$this->write(1,$this->debugPdfTxt);
		}
		
	}
	
	
	public function pageTopoA4($caviteCourante,$config)
	{
		if($caviteCourante->topographieA4_path)
		{
			/*
			 * Détermination de l'orientation de la topo
			 *   si x>y orientation Paysage (L) 
			 *   si x<y orientation portrait (P)
			 * + définition de la hauteur xMaxTopo
			 */
			$tailleTopo = getimagesize($caviteCourante->topographieA4_path);
			$x = $tailleTopo[0];
			$y = $tailleTopo[1];
			if($x>$y)
			{
				//format paysage
				$orientation = 'L';
				$xMaxTopo = 291 ; // 297 - 6
				$yMaxTopo = 177; //210 - 16 - 11 - 6;
				$Xcentrage = 297;
				$Ycentrage = 210;
			}
			else
			{
				//la topo est au format portrait
				$orientation = 'P';
				$xMaxTopo = 204 ;//210 - 6
				$yMaxTopo = 264; //297−16−11−6
				$Xcentrage = 210;
				$Ycentrage = 297;
			}
			//la page est en mode paysage pour avoir un maximum d'espace d'affichage
			//AddPage ($orientation='', $format='', $keepmargins=false, $tocpage=false)
			$this->addpage($orientation,'A4',true);

			//conversion des mm en px 1px = 0.264583333mm
			$xMaxTopo = intval($xMaxTopo / 0.264583333); 
			$yMaxTopo = intval($yMaxTopo / 0.264583333);
			$myRatioA4 = $this->tailleMaxImage($caviteCourante->topographieA4_path, $xMaxTopo, $yMaxTopo);
			
			//on converti à nouveau les px en mm:
			$myRatioA4['X'] = intval($myRatioA4['X'] * 0.264583333);
			$myRatioA4['Y'] = intval($myRatioA4['Y'] * 0.264583333);
			
			
			//centrage de la topo au niveau horizontal
			$XcentrageTopoA4 = ($Xcentrage - $myRatioA4['X'])/2 ;
			$YcentrageTopoA4 = ($Ycentrage - $myRatioA4['Y'])/2 ;
			//$pdf->multicell(0,4,$XcentrageTopoA4 . ' -- ' . $YcentrageTopoA4,0,'R',false);
			//echo $XcentrageTopoA4 . ' -- ' . $YcentrageTopoA4;
			
			$this->image($caviteCourante->topographieA4_path,$XcentrageTopoA4,$YcentrageTopoA4,$myRatioA4['X'],$myRatioA4['Y']);
			
			$YcreditTopographeA4 = -12;
			$this->sety($YcreditTopographeA4);
			$this->multicell(0,4,'Topographes : ' . $caviteCourante->topographe,0,'R',false);
		}
	}
	
	public function pageTopoA4Bis($caviteCourante,$config)
	{
		if($caviteCourante->topographieA4bis_path)
		{
			/*
			 * Détermination de l'orientation de la topo
			 *   si x>y orientation Paysage (L) 
			 *   si x<y orientation portrait (P)
			 * + définition de la hauteur xMaxTopo
			 */
			$tailleTopo = getimagesize($caviteCourante->topographieA4bis_path);
			$x = $tailleTopo[0];
			$y = $tailleTopo[1];
			if($x>$y)
			{
				//format paysage
				$orientation = 'L';
				$xMaxTopo = 291 ; // 297 - 6
				$yMaxTopo = 177; //210 - 16 - 11 - 6;
				$Xcentrage = 297;
				$Ycentrage = 210;
			}
			else
			{
				//la topo est au format portrait
				$orientation = 'P';
				$xMaxTopo = 204 ;//210 - 6
				$yMaxTopo = 264; //297 − 16 − 11 − 6
				$Xcentrage = 210;
				$Ycentrage = 297;
			}
			//la page est en mode paysage pour avoir un maximum d'espace d'affichage
			$this->addpage($orientation,'A4',true);
			
			//conversion des mm en px 1px = 0.264583333mm
			$xMaxTopo = intval($xMaxTopo / 0.264583333); 
			$yMaxTopo = intval($yMaxTopo / 0.264583333);
			$myRatioTopoA4bis = $this->tailleMaxImage($caviteCourante->topographieA4bis_path, $xMaxTopo, $yMaxTopo);
			
			//on converti à nouveau les px en mm:
			$myRatioTopoA4bis['X'] = intval($myRatioTopoA4bis['X'] * 0.264583333);
			$myRatioTopoA4bis['Y'] = intval($myRatioTopoA4bis['Y'] * 0.264583333);
			
			//centrage de la topo au niveau horizontal
			$XcentrageTopoA4bis = ($Xcentrage - $myRatioTopoA4bis['X'])/2 ;
			$YcentrageTopoA4bis = ($Ycentrage - $myRatioTopoA4bis['Y'])/2 ;
			
			
			$this->image($caviteCourante->topographieA4bis_path,$XcentrageTopoA4bis,$YcentrageTopoA4bis,$myRatioTopoA4bis['X'],$myRatioTopoA4bis['Y']);
			
			$YcreditTopographeA4bis = -12;
			$this->sety($YcreditTopographeA4bis);
			$this->multicell(0,4,'Topographes : ' . $caviteCourante->topographe,0,'R',false);
		}
		
	}
	
	public function pageAnnexe($caviteCourante,$config)
	{
		if($caviteCourante->annexe)
		{
			$this->addpage($orientation,'A4',true);
			$this->SetXY(7,16);
			$YdebutCadreAnnexe = $this->gety();
			//echo $pdf->gety();
			$this->ln($this->getFontSize());
			$this->multicell(0,4,$caviteCourante->annexe,0,'J',false);
			//echo $pdf->gety();
			$YfinCadreAnnexe = $YdebutCadreAnnexe + $this->gety();
			$this->RoundedRect(5,$YdebutCadreAnnexe,202,$YfinCadreAnnexe - 30,3.5,'D');
		}
	}
}	
?>
