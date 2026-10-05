$(function() {
	'use strict';
	// Leftlet Maps — OpenStreetMap tiles (no third-party access token)
	var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
	var osmAttr = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
	var mymap = L.map('leaflet1').setView([51.505, -0.09], 13);
	L.tileLayer(osmUrl, {
		maxZoom: 18,
		attribution: osmAttr
	}).addTo(mymap);
	// Adding a Popup
	var mymap2 = L.map('leaflet2').setView([51.505, -0.09], 13);
	L.tileLayer(osmUrl, {
		maxZoom: 18,
		attribution: osmAttr
	}).addTo(mymap2);
	L.marker([51.5, -0.09]).addTo(mymap2).bindPopup("<b>Hello world!<\/b><br />I am a popup.").openPopup();
	// Adding a Circle
	var mymap3 = L.map('leaflet3').setView([51.505, -0.09], 13);
	L.tileLayer(osmUrl, {
		maxZoom: 18,
		attribution: osmAttr
	}).addTo(mymap3);
	L.circle([51.508, -0.11], {
		color: 'red',
		fillColor: '#f03',
		fillOpacity: 0.5,
		radius: 500
	}).addTo(mymap3);
});
