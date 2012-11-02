var fresh_icon = new Icon(new Img("./images/map_recent.gif",12,12));
var fresh_icon_med = new Icon(new Img("./images/map_recent.gif",20,20));
var fresh_icon_big = new Icon(new Img("./images/map_recent.gif",30,30));
var fresh = [fresh_icon, fresh_icon, fresh_icon_med, fresh_icon_med, fresh_icon_big, fresh_icon_big];

var stale_icon = new Icon(new Img("./images/map_old.gif",10,10));
var stale_icon_med = new Icon(new Img("./images/map_old.gif",18,18));
var stale_icon_big = new Icon(new Img("./images/map_old.gif",28,28));
var stale = [stale_icon, stale_icon, stale_icon_med, stale_icon_med, stale_icon_big, stale_icon_big];

var markers = new Array();

function openURL(url, target) {

	window.open(url,target);
	return false;

}

function mapMatchSize(map, other) {

	var map = document.getElementById(map);
	var squawks = document.getElementById(other);
	
	var h = 0;
	h = squawks.offsetHeight;
	
	if(h < 600) { h = 600; }

	map.style.height = h + "px";

}

function mapRefreshMarkers() {
	
	mapInstance.removeAllMarkers();

	for($m = 0; $m < markers.length; $m++) {
		 mapInstance.addMarker(markers[$m][0], new MapWindow(markers[$m][1], {width: 240, height: 120, closeOnMove: true}));
	}

}