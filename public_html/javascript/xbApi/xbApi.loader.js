/***************************************
** Browser determination
***************************************/

	Mac       = (navigator.appVersion.indexOf('Mac') != -1);
	Nix       = (navigator.appVersion.indexOf('X11') != -1);
	Win       = (navigator.appVersion.indexOf('Win') != -1);

	DOM       = (document.getElementById) ? true : false;
	NS4       = (document.layers) ? true : false;
	IE        = (document.all) ? true : false;
	IE4       = IE && !DOM;
	IE4M      = IE4 && Mac;
	Opera     = (navigator.userAgent.indexOf('Opera') != -1);
	Konqueror = (navigator.userAgent.indexOf('Konqueror') != -1);

/***************************************
** Check path variable
***************************************/

	if(!xbApi_path){
		xbApi_path = '';
	}

/***************************************
** Include appropriate browser specific functions
***************************************/

	// IE
	if(IE){
		document.write('<script src="' + xbApi_path + 'xbApi.ie.js" type="text/javascript" language="javascript"></script>');

	// DOM
	}else if(DOM){
		document.write('<script src="' + xbApi_path + 'xbApi.dom.js" type="text/javascript" language="javascript"></script>');

	// Netscape
	}else if(NS4){
		document.write('<script src="' + xbApi_path + 'xbApi.ns4.js" type="text/javascript" language="javascript"></script>');
	}

/***************************************
** Load common functions
***************************************/

	document.write('<script src="' + xbApi_path + 'xbApi.common.js" type="text/javascript" language="javascript"></script>');

// end