
/*
 **** convertGeo2Utm convert a lat/long coordinates set to LAMBERT3 projection ****
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
function convert2LAMBERT3(coordsJsonObj)
{
    //extract only usefull data from object
    console.log('Started long/lat to LAMBERT3 conversion');
    coordsObj = coordsJsonObj.features;
    console.log(coordsObj);

    var convertedData = []; 
    coordsObj.forEach(function(coord) {
        
        var srcCoords = {'x':parseFloat(coord.geometry.coordinates[0]),'y':parseFloat(coord.geometry.coordinates[1])};
        
        //prepare convert
        var dest = ('+proj=lcc +lat_1=44.10000000000001 +lat_0=44.10000000000001 +lon_0=0 +k_0=0.999877499 +x_0=600000 +y_0=3200000 +a=6378249.2 +b=6356515 +towgs84=-168,-60,320,0,0,0,0 +pm=paris +units=m +no_defs ');
        var source = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
        
        var converted = proj4(source,dest, srcCoords);
        console.log( 'converted => X:'+ converted.x + ' Y:' + converted.y );
        
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
                x : (converted.x/1000).toFixed(3),
                y : (converted.y/1000).toFixed(3),
                z : Z,
                id : coord.properties.id,
                string : 'X:' + (converted.x/1000).toFixed(3) + ' Y:' + (converted.y/1000).toFixed(3) + ' ' + Z + 'm',
            });
    });
    return convertedData;
}

/*
 * convert coords from long/lat and get back html required in editpage.php
 * return html content to insert into page
 */
function getCoordEditBoxes_LAMBERT3(geoCoords){
    console.log('build new input boxes for lambert3')
    var converted = convert2LAMBERT3(geoCoords);
    var html = '';
    converted.forEach( function(item){
        html += '<div class="editCoords" data-coordset="' + item.id + '">'; 
        html += '   X:<input type="text" class="coords" data-coord="lat" value="' + item.x + '" />';
        html += '   Y:<input type="text" class="coords" data-coord="long" value="' + item.y + '" />';
        html += '   Z:<input type="text" class="coords" data-coord="z" value="' + item.z + '" />';
        html += '</div>';
    });

    return html;
}

/*
 * Convert coords from lambert3 to long/lat and update coordinatesList 
 * global var to reflect changes
 * 
 * return javascript object thats contains converted data 
 */
function LAMBERT3_2longlat(coordSetIdx){
    console.log('convert Lambert3 to geographic coordinates');
    var x = $('.editCoords[data-coordset="'+coordSetIdx+'"] > .coords[data-coord="lat"]' ).val();
    var y = $('.editCoords[data-coordset="'+coordSetIdx+'"] > .coords[data-coord="long"]' ).val();
    var z = $('.editCoords[data-coordset="'+coordSetIdx+'"] > .coords[data-coord="z"]' ).val();
    
    console.log(x+'   '+y);
    
    //prepare convert
    var srcCoords = { 'x':parseFloat(x) * 1000, 'y':parseFloat(y) *1000 };
    //console.log('sources coords: ');
    //console.log(srcCoords);
    var source = ('+proj=lcc +lat_1=44.10000000000001 +lat_0=44.10000000000001 +lon_0=0 +k_0=0.999877499 +x_0=600000 +y_0=3200000 +a=6378249.2 +b=6356515 +towgs84=-168,-60,320,0,0,0,0 +pm=paris +units=m +no_defs ');
    var dest = ('+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs');
    
    var converted = proj4(source,dest, srcCoords);
    console.log( 'Converted Lambert3 => X:'+converted.x + ' Y:' +converted.y );
    return { //keep only last 7 digits, to have an accuracy around 1mm
                "x": Number(converted.x).toFixed(7),
                "y": Number(converted.y).toFixed(7),
                "z": z
            }; 
   
}