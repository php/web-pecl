/***************************************
** Form related
***************************************/

	// Return a form element
	function xbApi_getFormElement(form, element){
		return document.forms[form].elements[element];
	}

	// Returns a form elements' value
	function xbApi_getFormValue(element){
		return xbApi_formValue('get', element);
	}

	// Sets a form elements' value
	function xbApi_setFormValue(element, value){
		return xbApi_formValue('set', element, value);
	}
	
	// Master function for above two
	function xbApi_formValue(action, formElement, value){

		switch(formElement.type){
			case 'text':
			case 'hidden':
			case 'textarea':
				return (action == 'set' ? formElement.value = value : formElement.value);
				break;

			case 'select-one':
				if(action == 'get'){
					return formElement.options[formElement.selectedIndex].value;
				}else{
					for(i=0; i<formElement.options.length; ++i){
						if(formElement.options[i].value == value){
							formElement.selectedIndex = i;
							break;
						}
					}
				}

				break;

			case 'select-multiple':
				if(action == 'get'){
					ret = new Array;
					for(i=0; i<formElement.options.length; ++i){
						if(formElement.options[i].selected == true){
							ret[ret.length] = formElement.options[i].value;
						}
					}
					return ret;
				}else{
					if(typeof(value) == 'array'){
						for(i=0; i<value.length; ++i){
							for(j=0; j<formElement.options.length; ++j){
								if(formElement.options[j].value == value[i]){
									formElement.options[j].selected = true;
								}
							}
						}
						return true;
					}else{
						for(i=0; i<formElement.options.length; ++i){
							if(formElement.options[i].value == value){
								formElement.selectedIndex = i;
								return true;
							}
						}
					}
				}
				break;
		}
	}

/***************************************
** Date related functions
***************************************/

	// Returns number of days in the supplied month
	function xbApi_getDaysInMonth(month, year){
		monthdays = [30, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		if(month != 1){
			return monthdays[month];
		}else{
			return (year % 4 == 0 ? 29 : 28);
		}
	}


/***************************************
** Mouse related
***************************************/

	var mousePosX;
	var mousePosY;

	// Returns mouses current X pos
	function xbApi_getMouseX(){
		return mousePosX;
	}

	// Returns mouses current Y pos
	function xbApi_getMouseY(){
		return mousePosY;
	}

/***************************************
** Event callbacks
***************************************/

	window.onload = function(){
		if (NS4) {
			document.captureEvents(Event.MOUSEMOVE);
		}
		document.onmousemove = xbApi_getMouseXY;

		for(i=0; i < xbApi_onload.length;i++){
			eval(xbApi_onload[i] + '()');
		}
	}
