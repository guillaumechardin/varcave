<?php

require_once (__DIR__ . '/lib/varcave/varcaveHtml.class.php');
require_once (__DIR__ . '/lib/varcave/varcaveCave.class.php');
require_once (__DIR__ . '/lib/varcave/functions.php');

$htmlstr = '';
$html = new VarcaveHtml(L::pagename_lastchanges,'fr');

$cave = new varcaveCave;

$res = $cave->findLastModificationsLog( 9999, false, 2, false);


$htmlstr .= '    <h1>' . L::lastchanges_fullChangesHistory . '</h1>';
//if fetch fail. output an error message
if  (!$res)
{
	$htmlstr .= '<script>
	$(document).ready(function()
	{
		showDialog("' . L::errors_ERROR . '","' . $cave->getErrorMsg(true) . '");
	});
	</script>';
}
else
{
	$htmlstr .= '<div class="changelog">';
	
	foreach ($res as $caveMods)
	{	
		
		$htmlstr .=  '<div class="changelogItem" guid="'. $caveMods['guid'] . '"> <i class="fas fa-arrow-alt-circle-right"></i> 	' . $caveMods['date'] . ' » ' . $caveMods['name'] . ' » ' . $caveMods['chgLogTxt'] . '</div> '; 
		
	}
	$htmlstr .= '</div>';
	$htmlstr .= '<script src="lib/varcave/lastchange.js"></script>';
}

/**** LAST MODs DIV CONTAINER ****/






$html->insert($htmlstr,true);
echo $html->save();




?>
