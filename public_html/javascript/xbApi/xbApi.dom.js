/***************************************
** Layer related
***************************************/

	// Shows a layer
	function xbApi_showLayer(layerId){
		document.getElementById(layerId).style.visibility = 'visible';
	}

	// Hides a layer
	function xbApi_hideLayer(layerId){
		document.getElementById(layerId).style.visibility = 'hidden';
	}

	function xbApi_isLayerVisible(layerId){
		return (document.getElementById(layerId).style.visibility == 'visible');
	}

	// Sets a layers position
	function xbApi_setLayerPosition(layerId, Xpos, Ypos){
		document.getElementById(layerId).style.left = Xpos;
		document.getElementById(layerId).style.top  = Ypos;
	}

	// Sets a layers innerHTML	
	function xbApi_setInnerHTML(layerId, html){
		document.getElementById(layerId).innerHTML = html;
	}
	
/***************************************
** Mouse related
***************************************/

	// Call back for onMouseMove event
	function xbApi_getMouseXY(e){
		mousePosX = e.pageX;
		mousePosY = e.pageY;
		return true;
	}
