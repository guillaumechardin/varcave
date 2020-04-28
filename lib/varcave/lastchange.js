$(document).ready(function ()
{
	$('.changelogItem').on('click',function()
	{
		var guid = ( $( this ).attr('guid') );
		var thisHost = window.location.origin;
		window.location.replace(thisHost+'/display.php?guid='+guid);
	});
});
