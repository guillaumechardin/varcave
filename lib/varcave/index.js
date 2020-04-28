$(document).ready(function()	
{
		//open a new tab to target cave 
	$('.home-recentChg').on('click', function(event)
	{
		var caveguid = $(this).attr('data-targetguid');
		window.open('display.php?guid=' + caveguid, '_blank');
	});
	
	$('#test').addClass('ui-state-error');
});
