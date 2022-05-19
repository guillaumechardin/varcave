$(document).ready(function ()
{
    $( function() {
        $( "#myaccount_tabs" ).tabs();
    });

    $('#myaccount_table_fav_caves').DataTable({
        data: favCaveData,
        columns: [
            {title: 'id', 'visible':false},
            { title: 'Name' },
            { title: 'delete'},
        ],
    });

    $('#myaccount_table_fav_caves').on('click', '.delete-favorite', function(evt)
    {
        var tr = $(this).closest('tr');
        var table = $('#myaccount_table_fav_caves').DataTable();
        var rowid = table.cell(tr,0).data();
        console.log( 'delete ' + rowid);
        
        var form_data = new FormData();
		form_data.append('guid', $(this).parent().data('guid') );
		form_data.append('action', 'toggleCaveToFav');

        $.ajax(
            {
                type: 'post',
                url: 'myaccount.php',
                processData: false,
                contentType: false,
                data: form_data,
                dataType: "json",
                success:function(data, textStatus, jqXHR)
                        {                        
                            console.log('delete row');
                            tr.fadeOut(400, function () {
                                table.row(tr).remove().draw();
                                
                                });
                    
                            return true;
                        },
                error: function(data, textStatus, jqXHR){return false},
            });
    
            return true;
        //deleteUserFav(caveElid)

    });

    //force open in new tab
    $('table > tbody > tr >td > a').on('click', function(event)
    {
        event.preventDefault();
        var url = $(this).attr('href');
        window.open(url, '_blank');
    });
    

});