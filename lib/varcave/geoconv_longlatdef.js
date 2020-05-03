
/*
 * This send back the default coords value in long/lat format. This is the default storage 
 * method in geojson.
 */
function convert2GEOGRAPHIC(coordsJsonObj)
{
    //extract only usefull data from object
    console.log('Started long/lat revert back');
    coordsObj = coordsJsonObj.features[0].geometry.coordinates;
    console.log(coordsObj);

    var convertedData = []; 
    coordsObj.forEach(function(coord) {
        
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
                x : coord[0],
                y : coord[1],
                z : coord[2],
                string : 'X:' + coord[0] + ' Y:' + coord[1] + ' ' + Z,
            });
    });
    return convertedData;
}
