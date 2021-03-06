<?php
/*
print_clasificacion_excel.php

Copyright  2013-2017 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/



header('Set-Cookie: fileDownload=true; path=/');
// mandatory 'header' to be the first element to be echoed to stdout

/**
 * genera un fichero excel con los datos de la clasificacion de la ronda
 */

require_once(__DIR__."/../tools.php");
require_once(__DIR__."/../logging.php");
require_once(__DIR__.'/../database/classes/Clasificaciones.php');
require_once(__DIR__.'/classes/PrintClasificacionExcel.php');

try {
	$result=null;
	$mangas=array();
	$prueba=http_request("Prueba","i",0);
	$jornada=http_request("Jornada","i",0);
	$rondas=http_request("Rondas","i","0"); // bitfield of 512:Esp 256:KO 128:Eq4 64:Eq3 32:Opn 16:G3 8:G2 4:G1 2:Pre2 1:Pre1
	$mangas[0]=http_request("Manga1","i",0); // single manga
	$mangas[1]=http_request("Manga2","i",0); // mangas a dos vueltas
	$mangas[2]=http_request("Manga3","i",0);
	$mangas[3]=http_request("Manga4","i",0); // 1,2:GII 3,4:GIII
	$mangas[4]=http_request("Manga5","i",0);
	$mangas[5]=http_request("Manga6","i",0);
	$mangas[6]=http_request("Manga7","i",0);
	$mangas[7]=http_request("Manga8","i",0);
	$mangas[8]=http_request("Manga9","i",0); // mangas 3..9 are used in KO rondas
	
	$mode=http_request("Mode","i","0"); // 0:Large 1:Medium 2:Small 3:Medium+Small 4:Large+Medium+Small
	$c= new Clasificaciones("print_etiquetas_pdf",$prueba,$jornada);
	$result=$c->clasificacionFinal($rondas,$mangas,$mode);

	// Creamos generador de documento
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Disposition: attachment;filename=clasificacion.xls");
	header("Content-Transfer-Encoding: binary ");
	
	$excel=new PrintClasificacionExcel($prueba,$jornada,$mangas);
	$excel->xlsBOF();
	$base=$excel->write_PageHeader($prueba,$jornada,$mangas);
	
	// buscamos los recorridos asociados a la mangas
	$c= new Clasificaciones("print_clasificacion_excel",$prueba,$jornada);
	
	$result=array();
	$heights=intval(Federations::getFederation( intval($excel->prueba->RSCE) )->get('Heights'));
	switch($excel->manga1->Recorrido) {
		case 0: // recorridos separados large medium small tiny
			$r=$c->clasificacionFinal($rondas,$mangas,0);
			$base = $excel->composeTable($mangas,$r,0,$base+1);
			$r=$c->clasificacionFinal($rondas,$mangas,1);
			$base = $excel->composeTable($mangas,$r,1,$base+1);
			$r=$c->clasificacionFinal($rondas,$mangas,2);
			$base = $excel->composeTable($mangas,$r,2,$base+1);
			if ($heights!=3) {
				$r=$c->clasificacionFinal($rondas,$mangas,5);
				$base = $excel->composeTable($mangas,$r,5,$base+1);
			}
			break;
		case 1: // large / medium+small (3heignts) ---- L+M / S+T (4heights)
			if ($heights==3) {
				$r=$c->clasificacionFinal($rondas,$mangas,0);
				$base = $excel->composeTable($mangas,$r,0,$base+1);
				$r=$c->clasificacionFinal($rondas,$mangas,3);
				$base = $excel->composeTable($mangas,$r,3,$base+1);
			} else {
				$r=$c->clasificacionFinal($rondas,$mangas,6);
				$base = $excel->composeTable($mangas,$r,6,$base+1);
				$r=$c->clasificacionFinal($rondas,$mangas,7);
				$base = $excel->composeTable($mangas,$r,7,$base+1);
			}
			break;
		case 2: // recorrido conjunto large+medium+small+tiny
			if ($heights==3) {
				$r=$c->clasificacionFinal($rondas,$mangas,4);
				$base = $excel->composeTable($mangas,$r,4,$base+1);
			} else {
				$r=$c->clasificacionFinal($rondas,$mangas,8);
				$base = $excel->composeTable($mangas,$r,8,$base+1);
			}
			break;
	}
	$excel->xlsEOF();
} catch (Exception $e) {
	do_log($e->getMessage());
	die ($e->getMessage());
}
?>