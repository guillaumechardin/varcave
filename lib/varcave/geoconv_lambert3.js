
/*
 **** convertGeo2Utm convert a lat/long coordinates set to LAMBERT3 projection ****
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
                    x : 1235.45678,
                    y : 9876.54321,
                    str:  1235.45678 9876.54321
                },
                ]
 */
function convert2LAMBERT3(coordsJsonObj)
{
    //extract only usefull data from object
    console.log('Started long/lat to LAMBERT3 conversion');
    coordsObj = coordsJsonObj.features[0].geometry.coordinates;
    console.log(coordsObj);

    var convertedData = []; 
    coordsObj.forEach(function(coord) {
        
        var srcCoords = {'x':parseFloat(coord[0]),'y':parseFloat(coord[1])};
        
        //prepare convert
        var dest = ('+proj=lcc +lat_1=44.10000000000001 +lat_0=44.10000000000001 +lon_0=0 +k_0=0.999877499 +x_0=600000 +y_0=3200000 +a=6378249.2 +b=6356515 +towgs84=-168,-60,320,0,0,0,0 +pm=paris +units=m +no_defs ');
        var source = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
        
        var converted = proj4(source,dest, srcCoords);
        console.log( 'converted => X:'+converted.x + ' Y:' +converted.y );
        
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
                x : (converted.x/1000).toFixed(3),
                y : (converted.y/1000).toFixed(3),
                string : 'X:' + (converted.x/1000).toFixed(3) + ' Y:' + (converted.y/1000).toFixed(3) + ' ' + Z,
            });
    });
    return convertedData;
}

