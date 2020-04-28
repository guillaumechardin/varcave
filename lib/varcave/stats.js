var table = null;

$(document).ready(function ()
{
	table = $('#stats-table').DataTable(
	{
		"order": [[ 0, "desc" ]],
		"paging": false,
		"searching": false
	});
});