<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveCave.class.php');
require_once ('lib/varcave/varcaveNews.class.php');

$htmlstr = '';
$html = new varcaveHtml(L::pagename_index);

$news = new varcaveNews();
$newsList = $news->getNews(0,$html->getconfigelement('max_news_homepage'),false,true);
/**** NEWS DIV CONTAINER ****/
//$htmlstr .= '<div class="flexContainer">';
$htmlstr .= '<div class="genFlexContainerNowrap home-colContainer">';
$htmlstr .= '  <div class="home-col-1">' ;
$htmlstr .= '<h1>' . L::home_newsFeedTitle . '</h1>';
	
foreach($newsList as $news)
{
    $dtCreation =  new DateTime('@'.$news['creation_date']);
    
	$htmlstr .= '<div class="home_newsblock">';
	$htmlstr .= '  <div class="newsTitle">';
	$htmlstr .=  '<h2>'.$news['title'].'</h2>';
	$htmlstr .= '  </div>';
	$htmlstr .= '  <div class="newsSubTitle">';
	$htmlstr .= '  <i class="fas fa-user-edit"></i> ' . $news['firstname'] . ' ' . $news['lastname'] . ' » ' . $dtCreation->format('d-m-Y h:m:s');
	$htmlstr .= '  </div>';
	$htmlstr .= '  <hr>';
	$htmlstr .= '  <div class="newsContent">';
	$htmlstr .=  $news['content'];
	$htmlstr .= '  </div>';
	$htmlstr .= '  <hr>';
	$htmlstr .= '  <div class="newsFooter">';

	if ( !empty($news['edit_date']) )
	{
        $dtEdit =  new DateTime('@'.$news['edit_date'], new DateTimeZone('UTC'));
		$dtEdit->setTimezone(new DateTimeZone($html->getconfigelement('timezone')) );
		
		$htmlstr .= L::home_editedBy . ' ' . $news['editor_firstname'] . ' ' . $news['editor_lastname']. ' ' . L::home_editedOn .   ' ' . $dtEdit->format('d-m-Y H:i:s');
	}
	$htmlstr .= '  </div>';
	$htmlstr .= '</div>';
	
}
$htmlstr .= '  </div>';  //flexCol1

$cave = new varcaveCave;
$res = $cave->findLastModificationsLog($cave->getConfigElement('welcomePageShowLastUpdate') );


/**** LAST MODs DIV CONTAINER ****/
$htmlstr .= '  <div class="home-col-2">' ;
$htmlstr .= '    <h1>' . L::home_recentUpdates . '</h1>';

foreach ($res as $caveMods)
{	
	$htmlstr .= '<div class="home-recentChg" data-targetguid="' . $caveMods['guid'] . '">';
	$htmlstr .= '  <div class="home-caveChgt">';
	$htmlstr .=  '<h2>' . $caveMods['date'] . ' » ' . $caveMods['name'].'</h2>';
	$htmlstr .= '  </div>';
	$htmlstr .= '  <hr>';
	/*$htmlstr .= '  <div class="newsSubTitle">';
	$htmlstr .= '  </div>';*/
	$htmlstr .= '  <div class="home-caveChgtContent">';
	$htmlstr .=  $caveMods['chgLogTxt'];
	$htmlstr .= '  </div>';
	
	$htmlstr .= '  <div class="newsFooter">';
	$htmlstr .= '  </div>';
	$htmlstr .= '</div>';
	
	
}

$htmlstr .= '  </div>'; //flexCol2
$htmlstr .= '</div>'; //flex container
$htmlstr .= '<script src="lib/varcave/index.js"></script>';

$html->insert($htmlstr,true);
echo $html->save();


?>
