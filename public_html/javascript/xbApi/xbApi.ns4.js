/***************************************
** Layer related
***************************************/

	// Shows a layer
	function xbApi_showLayer(layerId){
		document.layers[layerId].visibility = 'show';
	}

	// Hides a layer
	function xbApi_hideLayer(layerId){
		document.layers[layerId].visibility = 'hide';
	}

	function xbApi_isLayerVisible(layerId){
		return (document.layers[layerId].visibility == 'show');
	}

	// Sets a layers position
	function xbApi_setLayerPosition(layerId, Xpos, Ypos){
		document.layers[layerId].left = Xpos;
		document.layers[layerId].top  = Ypos;
	}

	// Sets a layers innerHTML	
	function xbApi_setInnerHTML(layerId, html){
		document.layers[layerId].document.open();
        document.layers[layerId].document.write(html)
        document.layers[layerId].document.close();
 
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