
/*
 * This send back the default coords value in long/lat format. This is the default storage 
 * method in json coords.
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

function convert2GEOGRAPHIC(coordsJsonObj)
{
    //extract only usefull data from object
    console.log('Started long/lat revert back');
    coordsObj = coordsJsonObj.features;
    console.log(coordsObj);

    var convertedData = []; 
    coordsObj.forEach(function(coord) {
        
        //handle empty value for Z
        if(coord.geometry.coordinates[2] == null)
        {
            var Z = '';
        }
        else
        {
            var Z = coord.geometry.coordinates[2]+'m';
        }
        
        convertedData.push(
            {   
                x : coord.geometry.coordinates[0],
                y : coord.geometry.coordinates[1],
                z : coord.geometry.coordinates[2],
                string : 'X:' + coord.geometry.coordinates[0] + ' Y:' + coord.geometry.coordinates[1] + ' ' + Z,
            });
    });
    return convertedData;
}


/*
 * This function build back default inputs fields for geographic coordinates
 */
function getCoordEditBoxes_GEOGRAPHIC(coordsList) {
    var list = coordsList.features;
    var newHtml = '';
    list.forEach( function(item){
        newHtml +=  '<div class="editCoords" data-coordset="' + item.properties.id + '">'; 
        newHtml +=  '   X:<input type="text" class="coords" data-coord="lat"  value="' +   item.geometry.coordinates[0] + '" />';
        newHtml +=  '   Y:<input type="text" class="coords" data-coord="long" value="' +   item.geometry.coordinates[1]  + '" />';
        newHtml +=  '   Z:<input type="text" class="coords" data-coord="z"    value="' +   item.geometry.coordinates[2] + '" />';
        newHtml +=  '  &nbsp<span class="fas fa-trash-alt fa-lg" data-coordset="' + item.properties.id + '" id="edit-delCoordSet-' + item.properties.id + '"></span>';
        newHtml +=  '</div>';
    });
    return newHtml;
}