<?php

require_once ('lib/varcave/varcaveHtml.class.php');
require_once ('lib/varcave/varcaveAuth.class.php');
require_once ('lib/varcave/varcaveUsers.class.php');


$auth = new varcaveAuth();
$varcave = new varcave();
$logger = $varcave->logger;

$htmlstr = '';
$html = new VarcaveHtml(L::pagename_openlayerplugins);


if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	//echo 'vous allez être redirigé vers une connexion sécurisée :<br>'. $redirect; 
	exit();
}

$acl = $auth->getacl('a76507e1-36e8-4284-a0f0-cc83f7f8d8e5');
if ( !$auth->isSessionValid() || !$auth->isMember($acl[0]))
{
    $logger->error('editcave.php : user try to access unauthentified');
    $logger->error('IP : '. $_SERVER['REMOTE_ADDR']);
    $html = new VarcaveHtml(L::errors_ERROR );
    $htmlstr .= '<h2>' . L::errors_ERROR . '</h2>';
    $htmlstr .= L::errors_pageAccessDenied . '.';
    $html->insert($htmlstr,true);
    echo $html->save();
    exit();
}

$htmlstr .= '<h2> Gestion open layers</h2>';
switch($_GET['action'])
{
    case 'registerPlugins':
        $htmlstr .= <<<'EOF'
        <script>    
        $(document).ready(function()
        {
            $(".button").on('click',function(e)
            {
                $(this).css("background-color", "red");
                console.log( $(this).attr('id') );
            });
        });
        </script>'
EOF;
        $htmlstr .= '<div id="olconfigplugins-modeswitch"><a href="/olconfigplugins.php?action=default">' . L::olconfigplugins_switch_to_config . '</a></div>';
        $pluginDir = 'lib/varcave/ol-plugins';
        $files = scandir( $pluginDir );

        unset($files[0]);
        unset($files[1]);

        $plugins = $varcave->getOlRegisteredPlugins();
        if( is_array($plugins) )
        {
            if( !empty($plugins) || (empty($plugins) && count($files) >1) )
            {
                foreach($files as $key => $file  )
                {
                    $filepath =  $pluginDir . '/' . $file;
                    require_once('./' . $filepath);

                    //load plugin config
                    $varBaseName = $pluginShortName . '_config_register';
                    $pluginConfig = $$varBaseName;

                    //check if pluggin already registered
                    $pluginRegistered = array_search($pluginConfig[0]['pluginGUID'], array_column($plugins, 'guid'));
                    if( $pluginRegistered !== false  )
                    {
                        $logger->warning('  Plugin already registered : ' . $pluginConfig[0]['pluginGUID'] );
                        $htmlstr .= '<div id="olconfigplugins-plg-' . $pluginConfig[0]['pluginShortName'] . '">';
                        $htmlstr .= '  <div>plugin ' . $pluginConfig[0]['pluginShortName'] .' already registered</div>';
                        $htmlstr .= '  <div><button class="button" id="olconfigplugins-btn' . $pluginConfig[0]['pluginShortName'] .'" data-pluginName="' . $pluginConfig[0]['pluginShortName'] . '">delete</button></div>';
                        $htmlstr .= '</div>';
                        continue;
                    }
                    $htmlstr .= '<div>plugin ' . $pluginConfig[0]['pluginShortName'] .' to register</div>';
                    try
                    {
                        $varcave->registerOlPlugin($pluginConfig, $filepath);
                        $varcave->addListElement('default_geo_api', $pluginConfig[0]['pluginShortName'] ) ;
                        $htmlstr .= '<div>save success</div>';
                    }
                    catch(exception $e)
                    {
                        $htmlstr .= $e->getmessage();
                    }
                }
            }
            else
            {
                $htmlstr .= 'No plugins found';
            }
        }
        else
        {
            $htmlstr .= 'error fetching plugins';
        }
    break;
    
    case 'activeplugin':
        $logger->info('  enable or disable plugin id:[' . $_GET['pid'] . ']');
        try
        {
            if ( !isset($_GET['pid'])  || empty($_GET['pid']) )
            {
                $errorMsg = L::errors_ERROR . ':' . L::olconfigplugins_bad_plugin_id;
                Throw new Exception($errorMsg);
            }
            $pluginData = $varcave->getOlRegisteredPlugins($_GET['pid']);
            if($pluginData != false || !empty($pluginData) )
            {
                //change to bool and inverse val
                $pluginData[0]['is_active'] = !boolval($pluginData[0]['is_active']);

                $newclassinfo = 'round-box-green';
                if($pluginData[0]['is_active'] == false)
                {
                    $newclassinfo = 'round-box-red';
                }
                
                if (!$varcave->setOlPluginState($_GET['pid'], intval($pluginData[0]['is_active'])) )
                {
                    $errorMsg = L::errors_ERROR . ':' . L::olconfigplugins_unable_to_active_plug;
                    Throw new Exception($errorMsg);
                }
            }
            else
            {
                $errorMsg = L::errors_ERROR . ':' . L::olconfigplugins_fail_to_fetch_data;
                Throw new Exception($errorMsg);
            }
            $clientData = array(
                'status' => 'OK',
                'newclassinfo' => $newclassinfo,
                'plunginData' =>$pluginData,
            );
            $html->writeJson( $clientData);
        }
        catch(Exception $e)
        {
            
            $logger->error( $e->getmessage() );
            $clientData = array(
                'errorTitle' => L::errors_ERROR,
                'errorStr' => $e->getmessage(),
            );
            $html->writeJson( $clientData, 400, 'Bad Request');
        }
        
    
    break;
    
    default:
        //load datatables
        $htmlstr .= <<<EOF
        <script src="lib/varcave/olconfigplugins.js"></script>
        <script src="lib/varcave/datatables-i18n.php"></script>
        <link rel="stylesheet" type="text/css" href="lib/Datatables/DataTables-1.10.18/css/dataTables.jqueryui.min.css"/>
        <link rel="stylesheet" type="text/css" href="lib/Datatables/Select-1.2.6/css/select.jqueryui.min.css"/>
        <script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="lib/Datatables/DataTables-1.10.18/js/dataTables.jqueryui.min.js"></script>
        <script type="text/javascript" src="lib/Datatables/Select-1.2.6/js/dataTables.select.min.js"></script>
EOF;
        //list plugin state
        $RegisteredPlugins = $varcave->getOlRegisteredPlugins();
        $newArray = array();
        $htmlstr .= '<div id="olconfigplugins-modeswitch"><a href="/olconfigplugins.php?action=registerPlugins">' . L::olconfigplugins_switch_to_install . '</a></div>';
        foreach($RegisteredPlugins as $key => &$value)
        {
            
            if(!$value['is_active'])
            {
                $value['is_active'] = '<span class="round-box-red"></span>';
            }
            else
            {
                $value['is_active'] = '<span class="round-box-green"></span>';
            }
            $value[] = '<button class="active-plugin">' . L::general_enable_disable . '</button>'; //active
            $value[] ='' ;//default
            $newarray[] = array_values($value);
        }

        $htmlstr .= '<h3> plugin state</h3>';;
        $htmlstr .= '<script>';
        $htmlstr .= '	var pluginsData = ' .  json_encode($newarray,   JSON_PRETTY_PRINT) . ';';
        $htmlstr .= '   var col_mapname = "' . L::olconfigplugins_mapname . '";';
        $htmlstr .= '   var col_display_name = "' . L::olconfigplugins_display_name . '";';
        $htmlstr .= '   var col_path = "' . L::olconfigplugins_path . '";';
        $htmlstr .= '   var col_isactive = "' . L::olconfigplugins_isactive . '";';
        $htmlstr .= '   var col_change_state = "' . L::olconfigplugins_change_state . '";';
        $htmlstr .= '   var col_isdefault = "' . L::olconfigplugins_isdefault . '";';
        $htmlstr .= '</script>';
        $htmlstr .= '<div id="olconfigplugins-pluginState">';
        $htmlstr .= '      <table id="olconfigplugins-pluginTable" class="display" style="width:100%">'; //hardcoded size of 100% for javascript size in px recognition instead of css
        //$htmlstr .= ' <tfoot><tr><th></th></tr></tfoot>';
        $htmlstr .= '      </table>';
        
        $htmlstr .= '</div>';
        break;
}

$html->insert($htmlstr,true);
echo $html->save();

?>
