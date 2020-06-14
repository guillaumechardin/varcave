
/*
 * This send back the default coords value in long/lat format. This is the default storage 
 * method in json coords.
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


/*
 * This function build back default inputs fields for geographic coordinates
 */
function getCoordEditBoxes_GEOGRAPHIC(coordsList) {
    var list = coordsList.features[0].geometry.coordinates;
    console.log(list);
    var newHtml = '';
   
    var i= 0;
    list.forEach( function(item){
        newHtml += '<div class="editCoords" data-elNumber="' + i + '">'; 
        newHtml += '   X:<input type="text" class="coords" data-elNumber="0" value="' + item[0] + '" />';
        newHtml += '   Y:<input type="text" class="coords" data-elNumber="1" value="' + item[1] + '" />';
        newHtml += '   Z:<input type="text" class="coords" data-elNumber="2" value="' + item[2] + '" />';
        newHtml += '  &nbsp<span class="fas fa-trash-alt fa-lg"  id="edit-delCoordSet-' + i + '" ></span>';    
        newHtml += '</div>';
        i++;
    });
    return newHtml;
}