/*
perroenpista.js

Copyright 2013-2021 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


/*
Ejemplo del json devuelto en la llamada "listEvents()"

{"ID":"19237",
    "Session":"2",
    "Source":"tablet",
    "Type":"llamada",
    "Timestamp":"2021-07-17 19:58:49",
    "Data":"{
\"ID\":0,
\"Session\":2,
\"SessionName\":\"tablet:2:0:0:tablet_k20Ish85@1\",
\"TimeStamp\":1626544729,
\"Type\":\"llamada\",
\"Source\":\"tablet\",
\"Pru\":70,
\"Jor\":545,
\"Mng\":808,
\"Tnd\":2527,
\"Dog\":4737,
\"Drs\":29,
\"Hot\":0,
\"Eqp\":1060,
\"Flt\":0,
\"Toc\":0,
\"Reh\":0,
\"NPr\":0,
\"Eli\":0,
\"Tim\":0,
\"Value\":\"0\",
\"stop\":0,
\"start\":0,
\"Name\":\"tablet_k20Ish85@1\",
\"Oper\":0,
\"Numero\":3,
\"Nombre\":\"Pep\\\\'s Gunly\",
\"NombreLargo\":\"de Loustalarie des Bourdalats\",
\"NombreGuia\":\"Emilie Sales\",
\"NombreClub\":\"Cu et D\\\\'EC St. Cyprien\",
\"NombreEquipo\":\"-- Sin asignar --\",
\"Categoria\":\"X\",
\"Grado\":\"GI\"
}"
}
*/

/**
 * Analize received data and populate html form
 * @param {string} data received json decoded data
 */
function parseEvent(entry) {
	let lista=[];
	let data=JSON.parse(entry.Data);
	switch (entry.Type) {
		case "open":
			lista=['NombrePrueba','NombreJornada','NombreManga','NombreRing'];
			break;
		case "llamada":
			// ajustamos logo y timestamp
			$('#pp_Timestamp').html(entry.Timestamp);
			var canvas = document.getElementById("pp_Logo");
			ctx = canvas.getContext("2d");
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ac_config.myImage.src="../ajax/database/clubFunctions.php?Operation=getLogoByPerro&Federation=0&Perro="+data['Dog'];
			lista=['Drs','Nombre','NombreLargo','NombreGuia','NombreClub','NombreEquipo',"Categoria",'Grado'];
			// no break
		case "aceptar":
			lista=lista.concat(['Flt','Toc','Reh','Eli','NPr','Tim']);
			break;
		case "datos": // -1: no change; else data
			if (data['Flt']>=0 ) lista=lista.concat(['Flt']);
			if (data['Toc']>=0 ) lista=lista.concat(['Toc']);
			if (data['Reh']>=0 ) lista=lista.concat(['Reh']);
			if (data['Eli']>=0 ) lista=lista.concat(['Eli']);
			if (data['NPr']>=0 ) lista=lista.concat(['NPr']);
			break;
	}
	// rellenamos pagina
	$.each(lista, function(key, value){ $('#pp_'+value).html(data[value]);	});
}

	// wait for new events
/**
 * Wait for new events
 * @param {integer} evtID last received event id
 * @param {integer} timestamp last received event timestamp
 * @param {boolean} firstcall when true ignore all events but last one
 */
	function waitForEvents(evtID,timestamp,firstcall){
		// ejemplo: source:ringsessionID:view_type:round_mode:SessionName
		// source: videowall,tablet, chrono, xxxx
        // use inner vars to preserve scope in handleSuccess
		var mark=timestamp;
		var lastID=evtID;
		var fcall=firstcall;

		function handleSuccess(received,status,jqXHR){
			var row=null;
            mark=received.TimeStamp; // store last event timestamp
			for (var n=0;n<parseInt(received.total);n++) {
				var parse=true;
				row=received.rows[n];
                lastID=row.ID; // update last id
				parseEvent(row);
			}
			// re-queue event
			setTimeout(function(){ waitForEvents(lastID,mark,false);},1000);
		}

		function handleError(XMLHttpRequest,textStatus,errorThrown) {
			// register and show error
			var msg= 'waitForEvent() error: '+XMLHttpRequest.status+" - "+XMLHttpRequest.responseText+" - "+textStatus + ' '+ errorThrown;
	        console.log(msg);
            $.messager.show({
                title: 'Error',
                msg: msg,
                timeout: 1000,
                showType: 'slide'
            });
			// and if event handler is still alive fire up again with extra delay
            if (ac_config.event_handler!==null){ // retry in 5 seconds
                ac_config.backup_timeoutHandler = setTimeout(function(){ waitForEvents(evtID,timestamp,fcall);},5000);
            }
		}

		$.ajax({
			type: "GET",
			url: "../ajax/database/eventFunctions.php",
			data: {
				Operation:	'getEvents',
				ID: 		evtID,
				Session:	ac_config.SessionID,
				TimeStamp:	mark,
				Source:		ac_config.Source,
				Destination: ac_config.Destination,
				Name: 		ac_config.Name,
				SessionName: ac_config.Sname
			},
			async: true,
			cache: false,
			dataType: 'json',
			success: handleSuccess,
			error: handleError
		});
	}
	// waitForEvents(evtID,timestamp);
// });

/** 
 * Call "connect" to retrieve last "open" event for provided session ID
 * fill working data with received info
 * If no response wait two seconds and try again
 * On sucess invoke
 */
function startEventMgr() {
    var sname=ac_config.Sname;
	$.ajax({
		type: "GET",
		url: "../ajax/database/eventFunctions.php",
		data: {
			Operation:	'connect',
			Session:	ac_config.SessionID,
            SessionName: sname,
			Source:		"'videowall",
			Destination: '', /* not specified */
			Name:		'perroenpista'
		},
		async: true,
		cache: false,
		dataType: 'json',
		success: function(response) {
			var timeout=5000;
			if (typeof(response['errorMsg'])!=="undefined") { // response indicates error, warn and try again
				console.log(response['errorMsg']);
				setTimeout(function(){ startEventMgr();},timeout );
				return;
			}
			if ( parseInt(response['total'])!==0) { // 'connect' ack. get data and start waiti events loop
				var row=response['rows'][0];
				var evtID=parseInt(row['ID'])-1; // make sure initial "init" event is received
                setTimeout(function(){ waitForEvents(evtID,0,true);},0);
			} else { // response has empty data: try again in (timeout) 5 seconds
				setTimeout(function(){ startEventMgr(); },timeout );
			}
		},
		error: function (XMLHttpRequest,textStatus,errorThrown) { // error in ajax call: notice and try again
			alert("startEventMgr() error: "+XMLHttpRequest.status+" - "+XMLHttpRequest.responseText+" - "+textStatus + " "+ errorThrown );
			setTimeout(function(){ startEventMgr(); },5000 );
		}
	});
	return false;
}

/**
 * Call to server to locate session id for requested ring on AgilityContest server running at provided host
 */
function findSessionID() {
	$.ajax({
		type: "GET",
		url: "../ajax/database/sessionFunctions.php",
		data: { Operation:	'selectring' },
		async: true,
		cache: false,
		dataType: 'json',
		success: function(response) {
			var str="success";
			var timeout=5000;
			if (typeof(response['errorMsg'])!=="undefined") { // response indicates error, warn and try again
				console.log(response['errorMsg']);
				alert(response['errorMsg']);
				return;
			}
			if ( parseInt(response['total'])===0) {
				str="Server '"+ac_config.Host+"' does not have any rings defined";
				console.log(str);
				alert(str);
				return
			}
			// OK find ring
			str="Ring "+ac_config.Ring;
			for (n=0;n<response['total'];n++) {
				let ses=response['rows'][n];
				if (ses['Nombre']!==str) continue;
				ac_config.SessionID=ses['ID'];
				console.log("Found AgilityContest server:'"+ac_config.Host+"' Ring:"+ac_config.Ring+" SessionID:"+ses['ID']);
				setTimeout(function(){startEventMgr()},2000);
				return
			}
			// arriving here means ring id not found
			str="AgilityContest server at host:'"+ac_config.Host+"' has not ring "+ac_config.Ring+" declared";
			console.log(str);
			alert(str);
		},
		error: function (XMLHttpRequest,textStatus,errorThrown) { // error in ajax call: notice and try again
			alert("startEventMgr() error: "+XMLHttpRequest.status+" - "+XMLHttpRequest.responseText+" - "+textStatus + " "+ errorThrown );
			setTimeout(function(){ startEventMgr(); },5000 );
		}
	});
	return false;
}