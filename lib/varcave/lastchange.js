$(document).ready(function ()
{
	$('.changelogItem').on('click',function()
	{
		var guid = ( $( this ).attr('guid') );
		var thisHost = window.location.origin;
		//window.location.replace(thisHost+'/display.php?guid='+guid);
        var url = thisHost+'/display.php?guid='+guid;
        window.open(url, '_blank');
	});
});
