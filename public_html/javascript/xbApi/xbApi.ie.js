/***************************************
** Layer related
***************************************/

	// Shows a layer
	function xbApi_showLayer(layerId){
		document.all[layerId].style.visibility = 'visible';
	}

	// Hides a layer
	function xbApi_hideLayer(layerId){
		document.all[layerId].style.visibility = 'hidden';
	}

	function xbApi_isLayerVisible(layerId){
		return (document.all[layerId].style.visibility == 'visible');
	}

	// Sets a layers position
	function xbApi_setLayerPosition(layerId, Xpos, Ypos){
		document.all[layerId].style.left = Xpos;
		document.all[layerId].style.top  = Ypos;
	}

	// Sets a layers innerHTML	
	function xbApi_setInnerHTML(layerId, html){
		document.all[layerId].innerHTML = html;
	}

/***************************************
** Mouse related
***************************************/

	// Call back for onMouseMove event
	function xbApi_getMouseXY(){
		mousePosX = event.clientX; + document.body.scrollLeft;
		mousePosY = event.clientY; + document.body.scrollTop;
	}
