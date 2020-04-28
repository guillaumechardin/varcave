<?php
	require_once (__DIR__ . '/varcaveAuth.class.php');
	header('Content-Type: application/javascript');
	$auth = new varcaveAuth();
		
	//generate var language object to use in
	// datatables initialization
	echo 'var i18nMenus = ';
	//echo 'language:';
	echo '{';
	echo '	processing:" ' .         L::dataTables_processing . '",';
	echo '  search: "' .             L::dataTables_search . '",';
	echo '  lengthMenu:"' .         L::dataTables_lengthMenu . '",';
	echo '  info:"' .               L::dataTables_info . '",';
	echo '  infoEmpty:"' .          L::dataTables_infoEmpty . '",';
	echo '  infoFiltered:"' .       L::dataTables_infoFiltered . '",';
	echo '  infoPostFix:"' .        L::dataTables_infoPostFix . '",';
	echo '  loadingRecords:"' .     L::dataTables_loadingRecords . '",';
	echo '  zeroRecords:"' .        L::dataTables_zeroRecords . '",';
	echo '  emptyTable:"' .         L::dataTables_emptyTable . '",';
	echo '  paginate:';
	echo '  {';
	echo '      first:"' .          L::dataTables_first . '",';
	echo '      previous:"' .       L::dataTables_previous . '",';
	echo '      next:"'  .          L::dataTables_next . '",';
	echo '      last:"' .           L::dataTables_last . '",';
	echo '  },';
	echo '  aria:';
	echo '  {';
	echo '      sortAscending:"' .  L::dataTables_sortAscending  . '",';
	echo '      sortDescending:"' . L::dataTables_sortDescending . '",';
	echo '  }';
	echo '};';
	
	//set default value for nbr of displayed rows
	if ( isset($_SESSION['maxSearchResults']) )
	{
		echo 'var maxSearchResults = ' . $_SESSION['maxSearchResults'] . ';';
	}
	else
	{
		echo 'var maxSearchResults = ' . $auth->getConfigElement('maxSearchResults_default') . ';';
	}
    
    
    //setting rowID to populate
    echo 'var rowID = "indexid"; ';
?>
