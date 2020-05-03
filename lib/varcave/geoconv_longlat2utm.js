
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
            var Z = coord[2]+'m';
        }
        
        convertedData.push(
            {   
                zone : utmZone+zoneBand,
                x : Math.round(converted.x),
                y : Math.round(converted.y),
                string : 'Zone:' + utmZone+zoneBand + ' X:' + Math.round(converted.x) + ' Y:' + Math.round(converted.y) + ' ' + Z,
            });
    });
    return convertedData;
}


/*
 * this get a UTM zone for a know longitude. Take long as only arg.
 * It does not work for some area (ie : norway and Svalbard)
 * see https://stackoverflow.com/questions/9186496/determining-utm-zone-to-convert-from-longitude-latitude
 */
function long2UTMZone(long)
{
    return (Math.floor( (long + 180)/6) % 60) + 1;
}


/* this function get UTM latitude band for a given 
 * latitude in degrees
 * inspired from 
 *https://gis.stackexchange.com/questions/238931/utm-coordinates-and-knowing-how-to-get-the-grid-zone-letter
 */
function getUTMLatBand(lat)
{

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