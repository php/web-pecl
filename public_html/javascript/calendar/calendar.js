/***************************************
** Filename.......: calendar.js
** Project........: Popup Calendar
** Last Modified..: $Date: 2002-05-26 22:58:12 $
** CVS Revision...: $Revision: 1.1 $
** Copyright......: 2001, 2002 Richard Heyes
***************************************/

/***************************************
** Shows the calendar
***************************************/

	calendar_layers = new Array;

	// Shows the calendar
	function showCalendar(callbackFunc, layerId){

		// Hide the layer if the button has been clicked again
		if(xbApi_isLayerVisible(layerId)){
			xbApi_hideLayer(layerId);
			return;
		}else{
			xbApi_showLayer(layerId);
			xbApi_setLayerPosition(layerId, xbApi_getMouseX()+10, xbApi_getMouseY());
		}

		// Generate date values
		today = new Date();
		date  = today.getDate();
		month = today.getMonth();
		year  = today.getFullYear();

		showCalendarWithDate(callbackFunc, layerId, month, year);
	}

	// Shows the calendar
	function showCalendarWithDate(callbackFunc, layerId, month, year){

		// Register this calendar in the array of layerid
		calendar_layers[calendar_layers.length] = layerId;

		// Get the html

		html  = getCalendarHTML(callbackFunc, layerId, month, year);
		xbApi_setInnerHTML(layerId, html);
	}

/***************************************
** Returns table for a particular month
***************************************/

	function getCalendarHTML(callbackFunc, layerId, month, year){

		monthnames = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		numdays    = xbApi_getDaysInMonth(month, year);
		today      = new Date();
		fdate      = new Date(year,month,1);
		first      = fdate.getDay();

		// First few blanks up to first day
		ret = new Array(new Array());
		for(i=0; i<first; i++){
			ret[0][ret[0].length] = '<td>&nbsp;</td>';
		}

		// Main body of calendar
		row = 0;
		i   = 1;
		while(i <= numdays){
			if(ret[row].length == 7){
				ret[++row] = new Array();
			}
			css_class = (i == today.getDate() && month == today.getMonth() && year == today.getFullYear()) ? 'cal_today' : 'cal_day';
			ret[row][ret[row].length] = '<td align="center" class="' + css_class + '"><a href="javascript: ' + callbackFunc + '(' + i + ', ' + (month + 1) + ', ' + year + '); xbApi_hideLayer(\'' + layerId + '\')">' + i++ + '</a></td>';
			
		}

		// Format the HTML
		for(i=0; i<ret.length; i++){
			ret[i] = ret[i].join('\n') + '\n';
		}

		previous_year  = fdate.getFullYear();
		previous_month = fdate.getMonth() - 1;
		if(previous_month < 0){
			previous_month = 11;
			previous_year--;
		}
		
		next_year  = fdate.getFullYear();
		next_month = fdate.getMonth() + 1;
		if(next_month > 11){
			next_month = 0;
			next_year++;
		}

		html = '<table border="0" bgcolor="#eeeeee">';
		html += '<tr><td class="cal_header"><a href="javascript: showCalendarWithDate(\'' + callbackFunc + '\', \'' + layerId + '\', ' + previous_month + ', ' + previous_year +')"><img src="' + calendarImagesPath + 'tri-back.gif" alt="<<" border="0" width="10" height="10"></a></td><td colspan="5" align="center" class="cal_header">' + monthnames[fdate.getMonth()] + ' ' + fdate.getFullYear() + '</td><td align="right" class="cal_header"><a href="javascript: showCalendarWithDate(\'' + callbackFunc + '\', \'' + layerId + '\', ' + next_month + ', ' + next_year +')"><img src="' + calendarImagesPath + 'tri.gif" alt=">>" border="0" width="10" height="10"></a></td></tr>';
		html += '<tr>';
		html += '<td class="cal_dayname">Sun</td>';
		html += '<td class="cal_dayname">Mon</td>';
		html += '<td class="cal_dayname">Tue</td>';
		html += '<td class="cal_dayname">Wed</td>';
		html += '<td class="cal_dayname">Thu</td>';
		html += '<td class="cal_dayname">Fri</td>';
		html += '<td class="cal_dayname">Sat</td></tr>';
		html += '<tr>' + ret.join('</tr>\n<tr>') + '</tr>';
		html += '</table>';

		return html;
	}

/***************************************
** Onmouse(over|out) event handler
***************************************/

	calendar_mouseover = false;
	function calendarMouseOver(status){
		calendar_mouseover = status;
		return true;
	}

/***************************************
** Callbacks for document.onclick
***************************************/

	calendar_old_onclick = document.onclick ? document.onclick : new Function;

	document.onclick = function(){
		if(!calendar_mouseover){
			for(i=0; i<calendar_layers.length; ++i){
				xbApi_hideLayer(calendar_layers[i]);
			}
		}
		
		calendar_old_onclick();
	} 