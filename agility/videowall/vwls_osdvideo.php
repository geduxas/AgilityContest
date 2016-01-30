<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once(__DIR__."/../server/tools.php");
require_once(__DIR__."/../server/auth/Config.php");
require_once(__DIR__."/../server/auth/AuthManager.php");
$config =Config::getInstance();
$am = new AuthManager("Videowall::livestream");
if ( ! $am->allowed(ENABLE_VIDEOWALL)) { include_once("unregistered.php"); return 0;}
?>
<!--
livestream.inc

Copyright 2013-2015 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 -->


<!-- Pantalla liveStream -->
<div id="vwls_LiveStream-window" style="padding:0px">
	<div id="vwls_LiveStream" class="easyui-panel"
		data-options="noheader:true,border:false,closable:false,collapsible:false,collapsed:false,resizable:true">
		<!-- http://rolandocaldas.com/html5/video-de-fondo-en-html5 -->
            <video id="vwls_video" autoplay="autoplay" preload="auto" muted="muted"
                   loop="loop" poster="/agility/server/getRandomImage.php" style="width=100%;height:auto">
                <!-- http://guest:@192.168.122.168/videostream.cgi -->
                <source id="vwls_videomp4" src="" type='video/mp4'/>
                <source id="vwls_videoogv" src="" type='video/ogg'/>
                <source id="vwls_videowebm" src="" type='video/webm'/>
            </video>
		<div id="vwls_common" style="font-size:1.75em;display:inline-block;width:100%">
			<!-- Recuadros de decoracion -->
			<span class="vwls_fondo" id="vwls_Resultados">&nbsp;</span>
            <span class="vwls_fondo" id="vwls_Datos">&nbsp;</span>
            <span class="vwls_fondo" id="vwls_InfoManga">&nbsp;</span>
			<!-- datos de resultados -->
			<span class="vwls_dlabel" id="vwls_FaltasLbl"><?php _e('F'); ?>:</span>
			<span class="vwls_data"  id="vwls_Faltas">0</span>
			<span class="vwls_dlabel" id="vwls_TocadosLbl"><?php _e('T'); ?>:</span>
			<span class="vwls_data"  id="vwls_Tocados">0</span>
			<span class="vwls_dlabel" id="vwls_RehusesLbl"><?php _e('R'); ?>:</span>
			<span class="vwls_data"  id="vwls_Rehuses">0</span>
			<!-- <span class="vwls_dlabel" id="vwls_TiempoLbl">Time</span> -->
			<span class="vwls_dtime"  id="vwls_Tiempo">00.00</span>
       		<span id="vwls_timestamp" style="display:none"></span>
			<!-- Informacion del participante -->
			<span style="display:none" id="vwls_Perro">0</span>
			<img id="vwls_Logo" alt="Logo" src="/agility/images/logos/rsce.png" width="80" height="80" class="vwls_logo"/>
			<span class="vwls_label" id="vwls_Dorsal"><?php _e('Dorsal'); ?></span>
			<span class="vwls_label" id="vwls_Nombre"><?php _e('Name'); ?></span>
			<span class="vwls_label" id="vwls_NombreGuia"><?php _e('Handler'); ?></span>
			<span class="vwls_label" id="vwls_NombreClub"><?php _e('Club'); ?></span>
			<span class="vwls_label" id="vwls_Categoria"><?php _e('Category'); ?></span>
			<span class="vwls_label" id="vwls_Grado"><?php _e('Grade'); ?></span>
            <span class="vwls_label" id="vwls_Celo"><?php _e('Heat'); ?></span>
            <!-- Informacion de la manga -->
            <span class="vwls_label" id="vwls_Manga" style="text-align:center;"><?php _e('Round'); ?></span>
		</div>
	</div>
</div>

<!-- declare a tag to attach a chrono object to -->
<div id="cronometro"><span id="vwls_StartStopFlag" style="display:none">Start</span></div>
		
<script type="text/javascript">

// create a Chronometer instance
$('#cronometro').Chrono( {
	seconds_sel: '#vwls_timestamp',
	auto: false,
	interval: 50,
	showMode: 2,
	onUpdate: function(elapsed,running,pause) { 
		$('#vwls_Tiempo').html(parseFloat(elapsed/1000).toFixed((running)?1:ac_config.numdecs));
		return true;
	},
	onBeforePause:function() { $('#vwls_Tiempo').addClass('blink'); return true; },
	onBeforeResume: function() { $('#vwls_Tiempo').removeClass('blink'); return true; },
	onBeforeReset: function() { $('#vwls_Tiempo').removeClass('blink'); return true; }
});

$('#vwls_LiveStream-window').window({
	fit:true,
	noheader:true,
	border:false,
	closable:false,
	collapsible:false,
	collapsed:false,
	resizable:true,
	onOpen: function() {
		startEventMgr(workingData.sesion,videowall_eventManager);
	}
});

// layout
var layout= {'cols':800, 'rows':450}; // declare base datagrid as A5 sheet

doLayout(layout,"#vwls_Resultados",	700,	20,		75,	    100	);
doLayout(layout,"#vwls_Datos",		25,		390,	750,	45	);
doLayout(layout,"#vwls_InfoManga",	25,	    20,	    250,	20	);

doLayout(layout,"#vwls_FaltasLbl",	715,	30,		30,		20	);
doLayout(layout,"#vwls_Faltas",		740,	30,		25,		20	);
doLayout(layout,"#vwls_TocadosLbl",	715,	50,		30,		20	);
doLayout(layout,"#vwls_Tocados",	740,	50,		25,		20	);
doLayout(layout,"#vwls_RehusesLbl",	715,	70,	    30,		20	);
doLayout(layout,"#vwls_Rehuses",	740,	70,	    25,		20	);
// doLayout(layout,"#vwls_TiempoLbl",	710,	90,     30,		20	);
doLayout(layout,"#vwls_Tiempo",		710,	90,     55,		20	);

doLayout(layout,"#vwls_Logo",		30,		360,	80,		80	);
doLayout(layout,"#vwls_Dorsal",		120,	395,	110,	25	);
doLayout(layout,"#vwls_Nombre",		230,	395,	270,	25	);
doLayout(layout,"#vwls_NombreGuia",	120,	415,	380,	25	);
doLayout(layout,"#vwls_NombreClub",	500,	415,	300,	25	);
doLayout(layout,"#vwls_Categoria",	500,	395,	150,	25	);
doLayout(layout,"#vwls_Grado",		650,	395,	125,	25	);
doLayout(layout,"#vwls_Celo",		700,	415,	75,		25	);

doLayout(layout,"#vwls_Manga",		25, 	22,	    250,	15	);

jQuery('#vwls_common').fitText(0.02);

var eventHandler= {
	'null': null,// null event: no action taken
	'init': function(event,time) { // operator starts tablet application
		setupWorkingData(event['Pru'],event['Jor'],(event['Mng']>0)?event['Mng']:1); // use shortname to ensure data exists
		vwls_showOSD(0); 	// activa visualizacion de OSD
	},
	'open': function(event,time){ // operator select tanda
		vw_updateWorkingData(event,function(e,d){
			vw_updateDataInfo(e,d);
		});
	},
	'datos': function(event,time) {      // actualizar datos (si algun valor es -1 o nulo se debe ignorar)
		vwls_updateData(event);
	},
	'llamada': function(event,time) {    // llamada a pista
		var crm=$('#cronometro');
		myCounter.stop();
		crm.Chrono('stop',time);
		crm.Chrono('reset',time);
		vwls_showOSD(1); 	// activa visualizacion de OSD
		vwls_showData(event);
	},
	'salida': function(event,time){     // orden de salida
		myCounter.start();
	},
	'start': function(event,time) {      // start crono manual
		// si crono automatico, ignora
		var ssf=$('#vwls_StartStopFlag');
		if (ssf.text()==="Auto") return;
		ssf.text("Stop");
		myCounter.stop(); // stop 15 seconds countdown if needed
		var crm=$('#cronometro');
		crm.Chrono('stop',time);
		crm.Chrono('reset');
		crm.Chrono('start',time);
	},
	'stop': function(event,time){      // stop crono manual
		$('#vwls_StartStopFlag').text("Start");
		myCounter.stop();
		$('#cronometro').Chrono('stop',time);
	},
	// nada que hacer aqui: el crono automatico se procesa en el tablet
	'crono_start':  function(event,time){ // arranque crono automatico
		var crm=$('#cronometro');
		myCounter.stop();
		$('#vwls_StartStopFlag').text('Auto');
		// si esta parado, arranca en modo automatico
		if (!crm.Chrono('started')) {
			crm.Chrono('stop',time);
			crm.Chrono('reset');
			crm.Chrono('start',time);
			return
		}
		if (ac_config.crono_resync==="0") {
			crm.Chrono('reset'); // si no resync, resetea el crono y vuelve a contar
			crm.Chrono('start',time);
		} // else wait for chrono restart event
	},
	'crono_restart': function(event,time){	// paso de tiempo manual a automatico
		$('#cronometro').Chrono('resync',event['stop'],event['start']);
	},
	'crono_int':  	function(event,time){	// tiempo intermedio crono electronico
		var crm=$('#cronometro');
		if (!crm.Chrono('started')) return;	// si crono no esta activo, ignorar
		crm.Chrono('pause',time); setTimeout(function(){crm.Chrono('resume');},5000);
	},
	'crono_stop':  function(event,time){	// parada crono electronico
		$('#vwls_StartStopFlag').text("Start");
		$('#cronometro').Chrono('stop',time);
	},
	'crono_reset':  function(event,time){	// puesta a cero del crono electronico
		var crm=$('#cronometro');
		myCounter.stop();
		$('#vwls_StartStopFlag').text("Start");
		crm.Chrono('stop',time);
		crm.Chrono('reset',time);
	},
	'crono_dat': function(event,time) {      // actualizar datos -1:decrease 0:ignore 1:increase
		vwls_updateChronoData(event);
	},
	'crono_error':  null, // fallo en los sensores de paso
	'aceptar':	function(event,time){ // operador pulsa aceptar
		myCounter.stop();
		$('#cronometro').Chrono('stop',time);  // nos aseguramos de que los cronos esten parados
	},
	'cancelar': function(event,time){  // operador pulsa cancelar
		var crm=$('#cronometro');
		myCounter.stop();
		crm.Chrono('stop',time);
		crm.Chrono('reset',time);
		vwls_showOSD(0); // apaga el OSD
	},
	'info':	null // click on user defined tandas
};

</script>