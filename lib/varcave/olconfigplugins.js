var table = null;
var groupTable = null;
var loadingSpiner = $(".loadingSpiner");

$(document).ready(function()
{
    //initiate table with plugins
	table = $("#olconfigplugins-pluginTable").DataTable(
    {
        "rowId": 0, // select the uid col as the id
        "pageLength": 10,
        "language":   i18nMenus,
        //"jQueryUI":   true,
        "data":       pluginsData,
        columns: [ //defined in main php file
            { title: "id",visible:false },
            { title: "guid",visible:false },
            { title: col_mapname },
            { title: col_display_name },
            { title: col_path },
            { title: col_isactive },
            { title: col_change_state },
            { title: col_isdefault },
        ],
        select: {
            //style:    'single', //disable row selection
            blurable: true,
        },
    });
    
    $('#olconfigplugins-pluginTable').on('click','.active-plugin', function(e) 
	{
        var idtable = $(this).closest('tr');
	    var data = table.row( idtable ).data();
        var pluginId  = data[0];
        console.log('edit #' + pluginId);
        activePlugin(pluginId);
    });

    function activePlugin(pluginID)
    {
        form_data = new FormData();
        //form_data.append('expire-days',  $("#expire-days").val() );

        $.ajax(
        {
            type: 'post',
            url: 'olconfigplugins.php?action=activeplugin&pid='+pluginID,
            processData: false,
            contentType: false,
            data: form_data,
            dataType: "json",
            success:function(data, textStatus, jqXHR)
                    {
                        
                        var table = $('#olconfigplugins-pluginTable').DataTable();
                        //table.cell('#'+pluginID,5).data(data.newclassinfo); //col ID 5 = red or green light
                        var cell = table.cell('#'+pluginID,5).node();
                        $(cell).children("span").removeClass();
                        $(cell).children("span").addClass(data.newclassinfo);
                        
                    },
            error: function(jqXHR, error, errorThrown)
                    {
                        //loadingSpiner.delay(500).hide('slow');
                        var msg = JSON.parse(jqXHR.responseText);
                        showDialog(msg.errorTitle, msg.errorStr); //show new message windows
                        return false
                    },
        });
    }
})