<?php

/*
Selectiva_eo_RSCE.php

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
class Selectiva_eo_RSCE extends Selectiva_awc_RSCE {

    protected $myDBObject;
    protected $countries;
    /*
     La selectiva para el European Open tiene las mismas caracteristicas que una selectiva para el awc-fci
     con los siguientes cambios:
    - Los perros con y sin loe puntuan
    - Los perros extranjeros NO puntuan
    - No hay puntuacion por manga conjunta
    - Se utilizan los resultados de la selectiva awc correspondiente

    En cristiano: es una jornada subordinada de la selectiva, en la que participan todos los perros de grado III
     */

    function __construct() {
        parent::__construct("Selectiva European Open 2017");
        $this->myDBObject=new DBObject("EuropeanOpen::construct");
        $this->countries=array(); // array ( nombreclub => pais
        $this->federationID=0;
        $this->competitionID=6;
    }

    /**
     * Re-evaluate and fix -if required- results data used to evaluate TRS for
     * provided $prueba/$jornada/$manga
     * @param {object} $manga Round data and trs parameters
     * @param {array} $data Original results provided for evaluation
     * @param {integer} $mode which categories have to be selected
     * @return {array} final data to be used to evaluate trs/trm
     */
    public function checkAndFixTRSData($manga,$data,$mode=0) {
        // remember that prueba,jornada and manga are objects, so passed by reference
        $cat="";
        switch ($mode) { // en el EO solo puede valer 0,1,2, pero bueno...
            case 0: /* Large */		$cat= "AND (Categoria='L')"; break;
            case 1: /* Medium */	$cat= "AND (Categoria='M')"; break;
            case 2: /* Small */		$cat= "AND (Categoria='S')"; break;
            case 3: /* Med+Small */ $cat= "AND ( (Categoria='M') OR (Categoria='S') )"; break;
            case 4: /* L+M+S */ 	$cat= "AND ( (Categoria='L') OR (Categoria='M') OR (Categoria='S') )"; break;
            case 5: /* Tiny */		$cat= "AND (Categoria='T')"; break;
            case 6: /* L+M */		$cat= "AND ( (Categoria='L') OR (Categoria='M') )"; break;
            case 7: /* S+T */		$cat= "AND ( (Categoria='S') OR (Categoria='T') )"; break;
            case 8: /* L+M+S+T */	break; // no check categoria
            default: return $this->error("modo de recorrido desconocido:$mode");
        }
        // fase 0: buscamos la jornada padre
        $this->prueba->Selectiva = 1; // not really required, just to be sure
        $parent=intval($this->jornada->SlaveOf);
        if ($parent==0) return $data;

        // fase 1: cogemos todos los resultados de standard grado III de la manga padre
        $res=$this->myDBObject->__select(
            /* SELECT */ "Perro, Mangas.Tipo AS Tipo, GREATEST(200*NoPresentado,100*Eliminado,5*(Tocados+Faltas+Rehuses)) AS PRecorrido,Tiempo",
            /* FROM */   "Resultados,Mangas",
            /* WHERE */  "(Resultados.Manga=Mangas.ID) AND (Pendiente=0) AND (Resultados.Jornada=$parent) AND (Resultados.Grado='GIII') $cat",
            /* ORDER BY */" PRecorrido ASC, Tiempo ASC",
            /* LIMIT */  ""
        );
        // fase 2: eliminamos aquellos que no coincidan con el tipo de manga (agility/jumping)
        $tipo=Mangas::$tipo_manga[$manga->Tipo][5]; // vemos si estamos en agility o jumping
        $result=array();
        foreach ($res['rows'] as $row ) {
            if (Mangas::$tipo_manga[$row['Tipo']][5] != $tipo) continue;
            $result[]=$row; // tipo coincide. anyadimos al resultado. Recuerda que ya estan ordenados
        }
        // finalmente retornamos el resultado
        return $result;
    }

    /**
     * Every National GIII dogs can get points for European Open
     * So this function must discriminate comes or not from Spain
     */
    function canReceivePoints($club){
        // create and handle an club->country array cache
        if (!array_key_exists($club,$this->countries)) {
            $c=$this->myDBObject->conn->real_escape_string($club);
            $str="SELECT Pais FROM Clubes WHERE ( Nombre = '$c' )";
            $res=$this->myDBObject->query($str);
            if (!$res) {
                $this->myDBObject->error($this->myDBObject->conn->error);
                return false;
            }
            $row = $res->fetch_array(MYSQLI_ASSOC);
            if (!$row) {
                do_log("EuropeanOpen::canReceivePoints() no country for club $club");
                return false;
            }
            $this->countries[$club]=$row['Pais'];
        }
        if( ($this->countries[$club]==="ESP") || ($this->countries[$club]==="Spain")) return true;
        return false;
    }

    /**
     * Evalua la calificacion parcial del perro
     * @param {object} $m datos de la manga
     * @param {array} $perro datos de puntuacion del perro. Passed by reference
     * @param {array} $puestocat puesto en funcion de la categoria
     */
    public function evalPartialCalification($m,&$perro,$puestocat) {
        if ($perro['Grado']!=="GIII") {
            parent::evalPartialCalification($m,$perro,$puestocat);
            return;
        }
        // arriving here means grado III
        if ($this->prueba->Selectiva==0) { // need to be marked as selectiva to properly evaluate TRS in GIII
            parent::evalPartialCalification($m,$perro,$puestocat);
            return;
        }
        // si no tiene excelente no puntua
        if ( ($perro['Penalizacion']>=6.0)) {
            parent::evalPartialCalification($m,$perro,$puestocat);
            return;
        }
        // comprobamos si el perro es extranjero
        if (! $this->canReceivePoints($perro['NombreClub']) ) { // perro extranjero no puntua
            $this->poffset[$perro['Categoria']]++; // mark to skip point assignation
            parent::evalPartialCalification($m,$perro,$puestocat);
            return;
        }
        $pts=array("25","20","16","12","8","6","4","3","2","1"); // puntuacion manga de agility
        if (intval($m->Tipo)==11) $pts=array("20","16","12","8","6","5","4","3","2","1"); // puntuacion manga de jumping
        // solo puntuan los 10 primeros
        $puesto=$puestocat[$perro['Categoria']]-$this->pfoffset[$perro['Categoria']];
        if ( ($puesto>10) || ($puesto<=0) ) {
            parent::evalPartialCalification($m,$perro,$puestocat);
            return;
        }
        // si llegamos aqui tenemos los 10 primeros perros una prueba selectiva EO en grado 3 con un perro no extranjero que ha sacado excelente :-)
        $pt1=$pts[$puesto-1];
        if ($perro['Penalizacion']>0)	{
            $perro['Calificacion'] = _("Excellent")." $pt1";
            $perro['CShort'] = _("Exc")." $pt1";
        }
        if ($perro['Penalizacion']==0)	{
            $perro['Calificacion'] = _("Excellent")." (p) $pt1";
            $perro['CShort'] = _("ExP")." $pt1";
        }
        // en una selectiva solo puntua el primer perro, por lo que no tiene demasiado sentido la evaluacion de puntos y estrellas
        // de momento ponemos lo ponemos... luego ya veremos
        $perro['Puntos']=($puesto>1)?0:1;
        $perro['Estrellas']=0;
        if ($puesto>1) return;
        foreach ( $this->puntos as $item) {
            if ($perro['Grado']!==$item[0]) continue;
            // comprobamos si estamos en agility o en jumping
            $offset=(Mangas::$tipo_manga[$m->Tipo][5])?0/*agility*/:3/*jumping*/;
            $base=2;
            if ($perro['Categoria']==="M") $base=3;
            if ($perro['Categoria']==="S") $base=4;
            // si la velocidad es igual o superior se apunta tanto. notese que el array está ordenado por grad/velocidad
            if ($perro['Velocidad']>=$item[$base+$offset]) {
                $perro['Puntos'] = $item[8];
                $perro['Estrellas'] = $item[9];
            }
        }
    }


    /**
     * Evalua la calificacion final del perro
     * En las selectivas para el European Open, no hay clasificacion conjunta
     * Por lo que simplemente vamos a indicar la puntuacion en cada manga
     *
     * @param {array} $mangas informacion {object} de las diversas mangas
     * @param {array} $resultados informacion {array} de los resultados de cada manga
     * @param {array} $perro datos de puntuacion del perro. Passed by reference
     * @param {array} $puestocat puesto en funcion de la categoria
     */
    public function evalFinalCalification($mangas,$resultados,&$perro,$puestocat){
        $grad=$perro['Grado']; // cogemos la categoria
        if ($grad==="GI") { // en grado uno puntua como prueba normal
            parent::evalFinalCalification($mangas,$resultados,$perro,$puestocat);
            return;
        }
        if ($grad==="GII") { // grado dos puntua como prueba normal
            parent::evalFinalCalification($mangas,$resultados,$perro,$puestocat);
            return;
        }
        if ($grad!=="GIII") { // ignore other extrange grades
            do_log("Invalid grade '$grad' found");
            return;
        }
        // arriving here means grado III
        if ($this->prueba->Selectiva==0){ // need to be marked as selectiva to properly evaluate TRS in GIII
            parent::evalFinalCalification($mangas,$resultados,$perro,$puestocat);
            return;
        }
        // arriving here means prueba selectiva European Open and Grado III
        if ( ! $this->canReceivePoints($perro['NombreClub']) ) {  // comprobamos si el perro es extranjero
            $this->pfoffset[$perro['Categoria']]++; // mark to skip point assignation
            // parent::evalFinalCalification($mangas,$resultados,$perro,$puestocat);
            $perro['Calificacion']= "No puntua";
            return;
        }
        // llegando aqui tenemos perro no extranjero que ha obtenido excelente en ambas mangas
        // imprimimos los resultados de cada manga. NO HAY puntuacion por conjunta
        // manga 1
        $pt1 = "0";
        if ($resultados[0] != null) { // extraemos los puntos de la primera manga
            $x=trim(substr($perro['C1'],-2));
            $pt1=(is_numeric($x))?$x:"0";
        }
        // manga 2
        $pt2="0";
        if ($resultados[1]!=null) { // extraemos los puntos de la segunda manga
            $x=trim(substr($perro['C2'],-2));
            $pt2=(is_numeric($x))?$x:"0";
        }
        $perro['Calificacion']= "$pt1 / $pt2";
        return;
    }
}