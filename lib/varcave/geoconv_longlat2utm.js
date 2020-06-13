
/*
 **** convertGeo2Utm convert a lat/long coordinates set to UTM ****
 * @param coordsJsonObj is an geojson object
 * {
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "MultiPoint",
        "coordinates": [
          [
            5.92,
            43.19,
            629
          ]
        ]
      },
      "properties": {
        "prop0": null
      }
    }
  ]
}
    @return : a new coordinate object set
     var return = [
                {
                    zone : "31T",
                    x : 1235.45678,
                    y : 9876.54321,
                    str: 31T 1235.45678 9876.54321
                },
                ]
 */
function convert2UTM(coordsJsonObj)
{
    //extract only usefull data from object
    console.log('Started long/lat to UTM conversion');
    coordsObj = coordsJsonObj.features[0].geometry.coordinates;
    console.log(coordsObj);

    var convertedData = []; 
    coordsObj.forEach(function(coord) {
        
        var utmZone = long2UTMZone( coord[0] );
        var zoneBand = getUTMLatBand( coord[1] );
        var srcCoords = {'x':parseFloat(coord[0]),'y':parseFloat(coord[1])};
        
        //prepare convert
        var dest = ('+proj=utm +zone=' + utmZone +' +units=m');
        var source = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
        
        var converted = proj4(source,dest, srcCoords);
        console.log( 'converted => zone:' + utmZone+zoneBand +  ' X:'+converted.x + ' Y:' +converted.y );
        
        //handle empty value for Z
        if(coord[2] == null)
        {
            var Z = '';
        }
        else
        {
            var Z = coord[2];
        }
        
        convertedData.push(
            {   
                zone : utmZone+zoneBand,
                x : Math.round(converted.x),
                y : Math.round(converted.y),
                z : Z,
                string : 'Zone:' + utmZone+zoneBand + ' X:' + Math.round(converted.x) + ' Y:' + Math.round(converted.y) + ' ' + Z + 'm',
            });
    });
    return convertedData;
}


/*
 * this get a UTM zone for a know longitude. Take long as only arg.
 * It does not work for some area (ie : norway and Svalbard)
 * see https://stackoverflow.com/questions/9186496/determining-utm-zone-to-convert-from-longitude-latitude
 */
function long2UTMZone(long){
    return (Math.floor( (long + 180)/6) % 60) + 1;
}


/* this function get UTM latitude band for a given 
 * latitude in degrees
 * inspired from 
 *https://gis.stackexchange.com/questions/238931/utm-coordinates-and-knowing-how-to-get-the-grid-zone-letter
 */
function getUTMLatBand(lat){

    var bandLetters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    //int latz = 0;//Latitude zone: A-B S of -80, C-W -80 to +72, X 72-84, Y,Z N of 84

    if (lat > -80 && lat < 72) {
        //= floor((lat + 80)/8)+2;
        return bandLetters.charAt(Math.floor( (lat+80)/8) +2);
    }
    if (lat > 72 && lat < 84) {
        return bandLetters.charAt(21);
    }
    if (lat > 84){
        return bandLetters.charAt(23)
    }
}

/*
 * convert coords from long/lat and get back html required in editpage.php
 * return html content to insert into page
 */
function getCoordEditBoxes_UTM(geoCoords){
    var converted = convert2UTM(geoCoords);
    var html = '';
    var i = 0;
    converted.forEach( function(item){
        html += '<div class="editCoords" data-coordSet="' + i + '">'; 
        html += '   ZONE:<input type="text" class="coords" data-elNumber="zone" value="' + item.zone + '"/>';
        html += '   X:<input type="text" class="coords" data-elNumber="0" value="' + item.x + '" />';
        html += '   Y:<input type="text" class="coords" data-elNumber="1" value="' + item.y + '" />';
        html += '   Z:<input type="text" class="coords" data-elNumber="2" value="' + item.z + '" />';
        html += '   &nbsp<span class="fas fa-trash-alt fa-lg"  id="edit-delCoordSet-' + i + '"></span>';
        html += '</div>';
        i++;
    });

    return html;
}

/*
 * Convert coords from utm to long/lat and update coordinatesList 
 * global var to reflect changes
 * 
 * return javascript object thats contains converted data 
 */
function UTM_2longlat(coordSetIdx){
    console.log('convert UTM to geographic coordinates');
    //console.log('target is jsonObjCoordSet['+coordSet+']['+coordIdx+'] => '+value);
    //coordsJsonObj.features[0].geometry.coordinates[coordSet][coordIdx] = value;
    
    var utmZoneFull = $('.editCoords[data-coordSet="'+coordSetIdx+'"] > .coords[data-elNumber="zone"' ).val();
    var utmZone = parseInt(utmZoneFull, 10);
    console.log('zone:'+utmZone);
    
    var x = $('.editCoords[data-coordSet="'+coordSetIdx+'"] > .coords[data-elNumber="0"' ).val();
    var y = $('.editCoords[data-coordSet="'+coordSetIdx+'"] > .coords[data-elNumber="1"' ).val();
    var z = $('.editCoords[data-coordSet="'+coordSetIdx+'"] > .coords[data-elNumber="2"' ).val();
    
    //prepare convert
    var srcCoords = { 'x':parseFloat(x), 'y':parseFloat(y) };
    var source = ('+proj=utm +zone=' + utmZone +' +units=m');
    var dest = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
    
    var converted = proj4(source,dest, srcCoords);
    
    console.log( 'converted => X:'+converted.x + ' Y:' +converted.y );
    
    return [Number(converted.x).toFixed(7), Number(converted.y).toFixed(7), z]; //keep only last 7 digits, to have an accuracy around 1mm
}