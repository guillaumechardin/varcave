<?php
    /*
     * this page is used to build a pdf book from all caves
     * data.
     */
    require_once(__DIR__ . '/lib/varcave/varcaveCave.class.php');
    require_once(__DIR__ . '/lib/varcave/varcavepdf.class.php');
    //require_once(__DIR__ . '/lib/varcave/varcaveAuth.class.php');
    //require_once(__DIR__ . '/lib/varcave/functions.php');
	
    //Valeurs par défaut de PHP modifiée pour permettre la bonne execution du script
        error_reporting(E_ALL);
        set_time_limit(-1);
        ini_set("memory_limit","2048M");
        //ini_set('display_errors',1);	
    
    //phpinfo();     
	
	

        $today = date('d/m/Y');
		$startTime = time();
		
		$caveObj = new VarcaveCave();

		
		//choix de l'export des données de cavités 
        $search [0] =  [
                      'field' => 'indexid',
                      'type' => '>',
                      'value' => 0,
                    ];
			
		//echo 'Début de la création du document le ' . date('d/m/Y H:i:s') . '<br>';
		$cavesResults = $caveObj->search($search,'name', 'ASC',  0, 9999999, true, 'indexid,guidv4');
        
        $caveList = $cavesResults[0]->fetchall(PDO::FETCH_ASSOC);
        
        $vPdf = new VarcavePdf("Varcave PDF Book") ;
		
		//quelques param par défaut	
		//variable utilisée pour les pages et options propres au livret au format PDF
		$vPdf->livretPdf = true;
		
		$vPdf->SetCreator('Fichier créé avec ' . PDF_CREATOR);
		$vPdf->SetAuthor('CDS 83');
		$vPdf->SetTitle('livret des cavites du Var (' . $today  . ')');
		//$vPdf->SetSubject('Fiche de cavité');
		$keywords = 'cavité, cds83';
		$vPdf->SetKeywords($keywords);
		
        /*
		//marges du doc et autobreak désactivé car on à une gestion fine des pages
		$vPdf->SetMargins(4,4,4);
		$vPdf->SetAutoPageBreak(FALSE, 4);
		
		//$vPdf->SetFont('helvetica','',15);
		$vPdf->SetFont('helvetica', 'BI', 20, '', 'false');
		*/
		
		/*
		 * Page de garde
		 */
		//les premieres pages ont une numérotation différente
		$vPdf->noheader = true;
		$vPdf->addpage();
		
		$vPdf->SetFont('dejavusans','B',30);
		$vPdf->setY(100);
		//$vPdf->Image('img/logo_CDS83_grey.png',65,70);
		$vPdf->multiCell(0,10,'CDS83',0,'C',false);
		$vPdf->multiCell(0,10,'FICHIER DES CAVITÉS DU VAR',0,'C',false);
		
		$vPdf->SetY(210);
		$vPdf->SetFont('dejavusans','B',12);
		$vPdf->multiCell(0,5,'Version du ' . $today ,0,'L',false);
		$vPdf->multiCell(0,5,'Nombre de cavités : '  . $cavesResults[1],0,'L',false);


		
		/*
		 * FIN Page de garde
		 */
		 
		/*
		 * Préface
		 */
		 //contenu de l'article spelunca 99 parut en 2005
$articleSpelunca99 = <<<EOD
Nous ne pouvons passer sous silence les œuvres magistrales de Martel : 
Les Abîmes (1894) où sont cités le Ragas de Dardennes et les pertes de l’Argens, puis, surtout 
La France ignorée (1928) qui nous permet de rêver sur le Verdon, le Plan de Canjuers et l’arrière-pays toulonnais.
Cependant, la première liste et description des cavités du Var digne du nom fut réalisée par Louis Henseling, conservateur de la bibliothèque municipale de Toulon. Dans la revue Zigzags à travers le Var, 8e série, 1938, il publie dix pages concernant la description et la localisation de 52 gouffres et 56 grottes, sous le titre : « Abîmes, avens, garagaïs, gouffres, ragages, trous, baumes et grottes ». Mais Henseling n’était pas un spéléologue, c’était un randonneur, très curieux de tout ce qui touchait notre département.
Quand je suis entré au clan Eole, en 1953, la spéléologie était pratiquée par un grand nombre de groupes affiliés aux Eclaireurs de France (EDF !). Ils n’avaient pas le nom de clubs, mais de clans. A Toulon, il y avait le clan Eole et le clan des Scialets, à Paris, le clan Claude Sommer, à Lyon le clan des Tritons et, surtout, celui de la Verna qui participait alors aux expéditions de la Pierre-Saint-Martin. A Toulon, le clan Eole était dirigé par Jean Colombier, sous-officier de la Marine nationale. Je ne me souviens plus de quelle manière il s’était mis en rapport avec le BRGM (Bureau de recherches géologiques et minières), lequel avait lancé la constitution d’un fichier national des cavités. Aussi, au clan Eole, chaque nouvelle cavité explorée était-elle topographiée et faisait l’objet de la rédaction d’une fiche BRGM qui nous était payée. Aujourd’hui, le BRGM a abandonné depuis très longtemps l’entretien de ce fichier. Dans les années 1960, le clan Eol et le clan des Scialets avaient disparu et Jean Colombier s’était inscrit à la Société des sciences naturelles de Toulon et du Var où il continuait à pointer systématiquement toutes les cavités connues pour remplir des fiches identiques à celles du BRGM. Vers 1970, Jean Colombier se tuait en tombant d’un toit. Alain Lebas put récupérer toutes ses fiches, au nombre de 600 environ et, vers 1980, il me demanda de constituer un répertoire des cavités du Var du même modèle que ce que faisait Yves Créac’h dans les Alpes-Maritimes. Mais, cela demandait beaucoup de travail et tombait à un moment où je quittais l’administration pour m’établir à mon propre compte. Ne trouvant aucun collaborateur, je laissai tomber. Il faut dire aussi, qu’à cette époque, nous n’avions pas encore les facilités apportées par l’informatique et le traitement de texte. Taper, dessiner, mettre en pages et imprimer était beaucoup plus long et compliqué. Quant aux modifications à apporter à un texte qui n’était pas gardé en mémoire, n’en parlons pas.
Ce fichier a disparu de la filière spéléologique, sans doute se trouve-t-il encore au musée de Toulon. Mais,
des photocopies de fiches ont permis qu’il ne soit pas complètement perdu. Vers 1976, Richard Zinck et Michel Lopez, aidés de Gérard Dou, entreprennent un fichier du plateau de Siou-Blanc, à partir de leurs documents personnels et de ceux qu’ils peuvent récupérer auprès d’autres spéléologues. A partir de 1984, après le congrès FFS de Toulon, Alain Franco et la nouvelle commission fichier du CDS Var,
s’attellent à l’extension du fichier de Siou-Blanc à tout le département du Var. Alain Paillier participe à la rédaction des fiches dont Jean Thomas obtient le tirage gratuit par le SDIS (Service départemental de l’incendie et des secours). De 1986 à 1988, 282 nouvelles fiches sont créées. En 1988 est aussi publiée
la deuxième édition du fichier de Siou-Blanc (380 fiches). À partir de 1989 apparaissent chaque année des additifs généraux du fichier du Var, ainsi que le fichier de la Sainte-Baume (111 fiches). Grâce au SDIS (Service départemental d’incendie et de sécurité), tous les spéléologues à jour de leur cotisation peuvent recevoir gracieusement les additifs. En 1994, AlainFranco et Alain Paillier avaient édité 1239 fiches et leurs additifs Ils doivent être loués pour ce travail d’autant plus important que le fichier n’était pas encore informatisé et que tout était rédigé et corrigé à la machine à écrire. A la fin de cette période, des changements ont lieu au sein du CDS 83. Jean-Pierre Lucot s’occupe maintenant du fichier qu’il décide immédiatement d’informatiser. Il travaille d’abord seul, saisissant tout le texte des fiches. Puis, il reçoit l’aide de Philippe Jubault, Frédéric Hay et Stéphane Riviani (deMarseille). Ils se partagent le travail pour scanner et mettre en forme toutes les topographies. En 1997, l’informatisation est terminée, mais les fiches sont toujours publiées sur papier à partir du fichier informatique. Fin 2000 apparaît enfin le premier
Cd-rom des cavités du Var qui sera ensuite mis à jour une fois par an. En 1990, Jean-Pierre Lucot avait hérité de 988 fiches, en 1995 elles avaient fait de nombreux bébés pour atteindre le chiffre de 1543. On atteignait 1778 fiches en 2000. Au 1er juin 2005, nous en sommes à 1961. Ce travail important n’est pas aisé, car il faut constamment relancer les clubs ou les individuels pour fournir les topographies et localisation des nouvelles cavités. À la longue, c’est un travail usant
EOD;
		 
		//$vPdf->addHeaderOnPage = false;
		//$vPdf->addFooterOnPage = false;
		$vPdf->addpage();
		//image d'entete
		$vPdf->Image('img/entetePDF.png',20,4,170);
		//titre de la page
		$vPdf->setY(12);
		$vPdf->SetFont('dejavusans','B',14);
		$vPdf->write(14,'Historique du fichier des cavités du Var :');
		$vPdf->Bookmark('Historique du fichier des cavités du Var','0');
		$vPdf->Ln(12);
		$vPdf->SetFont('dejavusans','',10);
		$vPdf->multiCell(0,5,$articleSpelunca99,0,'J',false);
		 
		 
		//$vPdf->addHeaderOnPage = false;
		//$vPdf->addFooterOnPage = false;
		$vPdf->addpage();
		//image d'entete
		$vPdf->Image('img/entetePDF.png',20,4,170);
		$vPdf->SetFont('dejavusans','B',14);
		//titre de la page
		$vPdf->setY(12);
		$vPdf->write(12,'Introduction :');
		$vPdf->Bookmark('Introduction','0');
		$vPdf->Ln(12);
		$vPdf->SetFont('dejavusans','I',10);
		//$vPdf->multiCell(0,5,$livretpdfConfig->disclaimer,0,'L',false);

		
		//$vPdf->multiCell(0,5,$livretpdfConfig->livretpdftexte1,0,'L',false);
	   
		//$vPdf->multiCell(0,5,$livretpdfConfig->livretpdftexte2,0,'L',false);

		//ajout d'une page vide
		$vPdf->noheader = false;
		$vPdf->nofooter = true;
        $vPdf->addpage();
	   
        foreach($caveList as $nbr => $cave)
        {
            $_cave = $caveObj->selectByguid($cave['guidv4'], false, false);
            $vPdf->addpage('P'); // 'P' to force portrait mode if changes on previous cavesaccess
            $vPdf->Bookmark($_cave['name'],'0');
            $vPdf->setCavedata($_cave);
            $vPdf->setY(15);
            $vPdf->caveinfo();
            //$vPdf->setY(70);
            $vPdf->caveaccess();
            $vPdf->addcavemaps();            
        }
        
        $vPdf->noheader = true;
		$vPdf->nofooter = true;
        $vPdf->addTOCPage('P');
		
		//modification des marges pour empécher d'avoir l'image d'entete surperposée avec le sommaire
		$vPdf->SetMargins(4,12,4);
		$vPdf->SetAutoPageBreak(true, 4);
		
		//Titre du sommaire
		$vPdf->SetFont('dejavusans', 'B', 16);
		$vPdf->MultiCell(0, 0, 'INDEX DES CAVITÉS', 0, 'C', 0, 1, '', '', true, 0);
		$vPdf->Ln();
		
		//titre de la TOC
		$vPdf->SetFont('dejavusans', '', 10);
		//insertion de la TOC page 4 apres les intoductions
		$vPdf->addTOC(4, 'dejavusans', '.', 'INDEX DES CAVITÉS', 'B', array(128,0,0));
		
		$vPdf->endTOCPage();
       
        $savePath = realpath('./') . '/pdfbook_'. date('Y-m-d_His') . '.pdf' ;
		
        $vPdf->Output($savePath, 'F');
       
		 
		/*
		 * Cavités
		 */
		 //Démarrage nouvelle numérotation
		/*$vPdf->addHeaderOnPage = true;
		$vPdf->addFooterOnPage = true;
        $i = 1;
        
		/*while ($cavite = $caviteAll[0]->fetch(PDO::FETCH_ASSOC))
		{   
                echo 'cavite num : ' . $i . "\n";
                $i++;
                
				$vPdf->startPageGroup();
				
				$caviteCourante = new Cavite();
				$caviteCourante->selectByID((int) $cavite['indexid']);
				$vPdf->caviteCouranteNum = $caviteCourante->numero;
				$vPdf->pageInfoCavite($caviteCourante,$livretpdfConfig);
			
				$vPdf->pageAnnexe($caviteCourante,$livretpdfConfig);
			
				$vPdf->pageTopoA4($caviteCourante,$livretpdfConfig);
			
				$vPdf->pageTopoA4Bis($caviteCourante,$livretpdfConfig);
				
				//pour test debug echo $caviteCourante->numero . " = " . $caviteCourante->nom . ' id = ' . $caviteCourante->indexid . ' topo = ' . $caviteCourante->topographie_path .'<br>';
		}
		
		//nouvelle page de TOC
		$vPdf->addHeaderOnPage = false;
		$vPdf->addFooterOnPage = false;
		$vPdf->addTOCPage();
		
		//modification des marges pour empécher d'avoir l'image d'entete surperposée avec le sommaire
		$vPdf->SetMargins(4,12,4);
		$vPdf->SetAutoPageBreak(true, 4);
		
		//Titre du sommaire
		$vPdf->SetFont('dejavusans', 'B', 16);
		$vPdf->MultiCell(0, 0, 'INDEX DES CAVITÉS', 0, 'C', 0, 1, '', '', true, 0);
		$vPdf->Ln();
		
		//titre de la TOC
		$vPdf->SetFont('dejavusans', '', 10);
		//insertion de la TOC page 4 apres les intoductions
		$vPdf->addTOC(4, 'dejavusans', '.', 'INDEX DES CAVITÉS', 'B', array(128,0,0));
		
		$vPdf->endTOCPage();
		
		try
		{
			//on sauvegarde dans le répertoire courant du script buidpdfbook dans l'emplacement défini dans $livretpdfConfig->pdfbook;
			$savePath = realpath('./') . '/' . $livretpdfConfig->pdfbook;
		
			$vPdf->Output($savePath, 'F');
		}
		catch (Exception $e)
		{
			//ne marche pas car tcpdf ne revoi pas d'exception si la création du fichier echoue.
			$retour['erreur'] .= 'Impossible de créer le fichier : ' .  $e->getMessage();
		}
		
		$endTime = time() - $startTime;
		$retour['html'] = 'Début de de la création : ' . date('d/m/Y H:i:s', $startTime) . '<br>';
		$retour['html'] .= 'Création du fichier terminée le ' . date('d/m/Y H:i:s') . '<br>';
		
		$retour['html'] .= htmlspecialchars( 'Temps de generation : ' . $endTime . 's') . '<br>';
		
		$retourJson = json_encode($retour);
		return $retourJson;
	}
	
	/*
	 * 
	 * DEBUT DE LA PAGE HTML
	 * 
	 */

?>

