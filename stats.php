<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveCave.class.php');

$htmlstr = '';
$html = new varcaveHtml(L::pagename_stats);
$cave = new varcaveCave();
$html->setNoindex(true);

$stastList = $cave->getStats();

$htmlstr .= '<h1>' . L::stats_title . '</h1>';
$htmlstr .= '<div id="stats-table-container">'; 
$htmlstr .= '<table id="stats-table">';
$htmlstr .= '<thead>';
$htmlstr .= '<tr>
    <th>' . L::stats_views . '</th>
    <th>' . L::cave . '</th>
  </tr>';

$htmlstr .= '</thead>';
$htmlstr .= '</tbody>';

foreach($stastList as $key => $value)
{
	$htmlstr .= '<tr>';
	$htmlstr .= '<td>' . $value['view_count'];
	$htmlstr .= '</td>';
	$htmlstr .= '<td>' . $value['name'];
	$htmlstr .= '</td>';
	$htmlstr .= '</tr>';
}
$htmlstr .= '</table>';
$htmlstr .= '</div>';

$htmlstr .= '<script src="lib/varcave/stats.js"></script>';
$htmlstr .= '<script src="lib/varcave/datatables-i18n.php"></script>';
$htmlstr .= '<link rel="stylesheet" type="text/css" href="lib/Datatables/DataTables-1.10.18/css/dataTables.jqueryui.min.css"/>';
$htmlstr .= '<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>';
$htmlstr .= '<script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/dataTables.jqueryui.min.js"></script>';

$html->insert($htmlstr,true);
echo $html->save();


?>
