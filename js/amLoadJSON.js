AmCharts.loadJSON = function(url) {
	// create the request
	if (window.XMLHttpRequest) {
		// IE7+, Firefox, Chrome, Opera, Safari
		var request = new XMLHttpRequest();
	} else {
		// IE5, IE6
		var request = new ActiveXObject('Microsoft.XMLHTTP');
	}

	// load it
	// the last "false" parameter ensures that our code will wait before
	// the data is loaded
	request.open('GET', url, false);
	request.send();

	// parse and return the output
	return eval(request.responseText);
}
