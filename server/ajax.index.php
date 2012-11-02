
var ajax = false;

if (window.XMLHttpRequest) {
	ajax = new XMLHttpRequest();
} else if (window.ActiveXObject) {
	ajax = new ActiveXObject("Microsoft.XMLHTTP");
}

if(ajax) {

	var squawkStart = 0;

	mapInstance = new SLMap(document.getElementById('slmap'));
	mapInstance.setCurrentZoomLevel(1);

	function updateSquawks() {

		var objContainer = document.getElementById('mapContainer');
		var objList = document.getElementById('squawks');

		objList.innerHTML = '<div id="loading"></div>';

		ajax.open("GET", "./json.main", true);
		ajax.onreadystatechange=function() {
			if (ajax.readyState==4) {
				if(ajax.status == 200) {

					mapInstance.removeAllMarkers();
					markers = new Array();

					objList.innerHTML = '';

					var squawks = eval("(" + ajax.responseText + ")");
					var out = '';

					for(s = 0; s < squawks.length; s++) {
						out  = '<li><a title="' + squawks[s]['name'] + ' (' + squawks[s]['status'] + ')" href="./people?name=' + squawks[s]['encoded_name'] + '" class="' + squawks[s]['status'] + '"><img src="./ext.avatar.php?t=' + squawks[s]['twitter'] + '" /></a>';
						out += '<p><a href="./people?name=' + squawks[s]['encoded_name'] + '&squawk=' +  squawks[s]['id'] + '" onclick="mapInstance.clickMarker(markers[' + s + ']); return false;">' + squawks[s]['message'] + '</a>';
						out += '<span class="age" title="Listed times are in SLT.">' + squawks[s]['age'] + '</span></p>';
						out += '</li>';
						objList.innerHTML += out;

						out  = '<p class="marker">';
						out += '<a href="./people?name=' + squawks[s]['encoded_name'] + '">' + squawks[s]['name'] + '</a> ' + squawks[s]['age'];
						out += '<span><img src="./ext.avatar.php?t=' + squawks[s]['twitter'] + '" />' + squawks[s]['message'] + '</span>';
						out += '<a href="secondlife://' + squawks[s]['encoded_region'] + '/' + squawks[s]['x'] + '/' + squawks[s]['y'] + '/' + squawks[s]['z'] + '">Teleport</a> <a href="./people?name=' + squawks[s]['encoded_name'] + '&squawk=' +  squawks[s]['id'] + '">Permalink</a>';
						out += '</p>';

						freshness = stale;
						if(squawks[s]['freshness'] == "fresh") freshness = fresh;

						markers[s] = new Marker(freshness, new SLPoint(squawks[s]['region'], squawks[s]['x'], squawks[s]['y']));
						mapInstance.addMarker(markers[s], new MapWindow(out, {width: 240, height: 130, closeOnMove: true}));
					}

					objContainer.style.display = 'block';
					mapInstance.panOrRecenterToSLCoord(new SLPoint(squawks[0]['region'], squawks[0]['x'], squawks[0]['y']), true);
					mapMatchSize('slmap','squawks');

					setTimeout("updateSquawks()", 300000);

				}
			}
		}
		ajax.send(null);

	}

	updateSquawks();

} else {

	alert("Your browser does not appear to support the JavaScript features necessary to use Squawk. Please consider upgrading.");

}