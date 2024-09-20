/*
 **** convertGeo2Utm convert a lat/long coordinates set to UTM ****
 * @param coordsJsonObj is an geojson object
 * {
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "Point",
        "coordinates": [
            5.92,
            43.19,
            629
        ]
      },
      "properties": {
        "id": "point db id"
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
    coordsObj = coordsJsonObj.features;
    console.log(coordsObj);

    var convertedData = []; 
    coordsObj.forEach(function(coord) {
        var utmZone = long2UTMZone( parseFloat(coord.geometry.coordinates[1]) );
        var zoneBand = getUTMLatBand( parseFloat(coord.geometry.coordinates[0]) );
        var srcCoords = {'x':parseFloat(coord.geometry.coordinates[1]),'y':parseFloat(coord.geometry.coordinates[0])};
        
        //prepare convert
        var dest = ('+proj=utm +zone=' + utmZone +' +units=m');
        var source = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
        
        var converted = proj4(source,dest, srcCoords);
        console.log( 'converted => zone:' + utmZone+zoneBand +  ' X:'+converted.x + ' Y:' +converted.y );
        
        //handle empty value for Z
        if(coord.geometry.coordinates[2] == null)
        {
            var Z = '';
        }
        else
        {
            var Z = coord.geometry.coordinates[2];
        }
        
        convertedData.push(
            {   
                zone : utmZone+zoneBand,
                x : Math.round(converted.x),
                y : Math.round(converted.y),
                z : Z,
                id : coord.properties.id,
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
    converted.forEach( function(item){
        html += '<div class="editCoords" data-coordset="' + item.id + '">'; 
        html += '   ZONE:<input type="text" class="coords" data-coord="zone" value="' + item.zone + '"/>';
        html +=  '   X:<input type="text" class="coords" data-coord="lat"  value="' + item.x + '" />';
        html +=  '   Y:<input type="text" class="coords" data-coord="long" value="' + item.y + '" />';
        html +=  '   Z:<input type="text" class="coords" data-coord="z"    value="' + item.z + '" />';
        html +=  '</div>';
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
    
    var utmZoneFull = $('.editCoords[data-coordset="'+coordSetIdx+'"] > .coords[data-coord="zone"]' ).val();
    var utmZone = parseInt(utmZoneFull, 10);
    console.log('zone:'+utmZone);
    
    var x = $('.editCoords[data-coordset="'+coordSetIdx+'"] > .coords[data-coord="lat"]' ).val();
    var y = $('.editCoords[data-coordset="'+coordSetIdx+'"] > .coords[data-coord="long"]' ).val();
    var z = $('.editCoords[data-coordset="'+coordSetIdx+'"] > .coords[data-coord="z"]' ).val();
    
    //prepare convert
    var srcCoords = { 'x':parseFloat(x), 'y':parseFloat(y) };
    var source = ('+proj=utm +zone=' + utmZone +' +units=m');
    var dest = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
    
    var converted = proj4(source,dest, srcCoords);
    
    console.log( 'converted => X:'+converted.x + ' Y:' +converted.y );
    
    return { "x": Number(converted.x).toFixed(7), "y": Number(converted.y).toFixed(7), "z": z}; //keep only last 7 digits, to have an accuracy around 1mm
}