<?php
/*
print_resultadosByManga.php

Copyright  2013-2018 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/
/**
 * genera un pdf con los participantes ordenados segun los resultados de la manga
 */

require_once(__DIR__."/../fpdf.php");
require_once(__DIR__."/../../tools.php");
require_once (__DIR__."/../../logging.php");
require_once(__DIR__.'/../../database/classes/DBObject.php');
require_once(__DIR__.'/../../database/classes/Pruebas.php');
require_once(__DIR__.'/../../database/classes/Jueces.php');
require_once(__DIR__.'/../../database/classes/Jornadas.php');
require_once(__DIR__.'/../../database/classes/Mangas.php');
require_once(__DIR__.'/../../database/classes/Resultados.php');
require_once(__DIR__.'/../../database/classes/Equipos.php');
require_once(__DIR__."/../print_common.php");

class PrintResultadosByEquipos3 extends PrintCommon {
	
	protected $manga;
	protected $resultados;
    protected $equipos;
	protected $mode;
	protected $title;
    protected $eqmgr;
    protected $defaultPerro = array( // participante por defecto para garantizar que haya 4perros/equipo
        'Dorsal' => '-',
        'Perro' => 0,
        'Nombre' => '-',
        'NombreLargo' => '-',
        'NombreGuia' => '-',
        'NombreClub' => '-',
        'Licencia' => '-',
        'Categoria' => '-',
        'Faltas' => 0,
        'Tocados' => 0,
        'Rehuses' => 0,
        'Tiempo' => '-',
        'Velocidad' => '-',
        'Penalizacion' => 400,
        'Calificacion' => '-',
        'CShort' => '-',
        'Puesto' => '-'
    );
	// geometria de las celdas
	protected $cellHeader;
                    //     Dors    Nombre  Lic     Guia   Club    Cat     Flt    Toc    Reh     Tiempo   vel   penal calif   puesto, equipo
	protected $pos	=array(  7,		18,		15,		30,		24,	    7,	   5,      5,    5,       11,     7,    12,    10,	 7,  25);
	protected $align=array(  'L',   'L',    'C',    'R',   'R',    'C',    'C',   'C',   'C',     'R',    'R',  'R',   'C',	 'C', 'R');
	
	/**
	 * Constructor
     * @param {int} $prueba PruebaID
     * @param {int} $jornada JornadaID
	 * @param {obj} $manga datos de la manga
	 * @param {obj} $resobj instance of Resultados (or any child)
     * @param {int} $mode how dogs are grouped by category
     * @param {string} $title what to show in main header title
	 * @throws Exception
	 */
	function __construct($prueba,$jornada,$manga,$resobj,$mode,$title) {
		parent::__construct('Portrait',"print_resultadosEquipos3",$prueba,$jornada);
		$this->manga=$manga;
        $this->resultados=$resobj->getResultadosIndividual($mode); // throw exception if pending dogs
        $this->mode=$mode;
        $this->title=$title;
        $this->cellHeader=
            array(_('Dorsal'),_('Name'),_('Lic').'.',_('Handler'),$this->strClub,_('Cat').'.',_('Flt').'.',_('Tch').'.',_('Ref').'.',
                  _('Time'),_('Vel').'.',_('Penal').'.',_('Calification'),_('Position'),_('Team global'));
        $this->equipos=$resobj->getResultadosEquipos($this->resultados);
        $this->eqmgr=new Equipos("print_resultadosByEquipos",$prueba,$jornada);
        // set file name
        $grad=$this->federation->getTipoManga($this->manga->Tipo,3); // nombre de la manga
        $cat=$this->federation->getMangaMode($mode,0);
        $str=($cat=='-')?$grad:"{$grad}_{$cat}";
        $res=normalize_filename($str);
        $this->set_FileName("ResultadosManga_{$res}.pdf");
	}
	
	// Cabecera de página
	function Header() {
		$this->print_commonHeader($this->title);
		$this->print_identificacionManga($this->manga,$this->getModeString(intval($this->mode)));
		
		// Si es la primera hoja pintamos datos tecnicos de la manga
		if ($this->PageNo()!=1) return;

		$this->SetFont($this->getFontName(),'B',9); // bold 9px
		$jobj=new Jueces("print_resultadosEquipos3");
		$juez1=$jobj->selectByID($this->manga->Juez1);
		$juez2=$jobj->selectByID($this->manga->Juez2);
		$this->Cell(20,5,_("Judge")." 1:","LT",0,'L',false);
		$str=($juez1['Nombre']==="-- Sin asignar --")?"":$juez1['Nombre'];
		$this->Cell(70,5,$str,"T",0,'L',false);
		$this->Cell(20,5,_("Judge")." 2:","T",0,'L',false);
		$str=($juez2['Nombre']==="-- Sin asignar --")?"":$juez2['Nombre'];
		$this->Cell(78,5,$str,"TR",0,'L',false);
		$this->Ln(5);
		$this->Cell(20,5,_("Distance").":","LB",0,'L',false);
		$this->Cell(25,5,"{$this->resultados['trs']['dist']} mts","B",0,'L',false);
		$this->Cell(20,5,_("Obstacles").":","B",0,'L',false);
		$this->Cell(25,5,$this->resultados['trs']['obst'],"B",0,'L',false);
		$this->Cell(10,5,_("SCT").":","B",0,'L',false);
		$this->Cell(20,5,"{$this->resultados['trs']['trs']} seg.","B",0,'L',false);
		$this->Cell(10,5,_("MCT").":","B",0,'L',false);
		$this->Cell(20,5,"{$this->resultados['trs']['trm']} seg.","B",0,'L',false);
		$this->Cell(20,5,_("Speed").":","B",0,'L',false);
		$this->Cell(18,5,"{$this->resultados['trs']['vel']} m/s","BR",0,'L',false);
		$this->Ln(5);
	}
	
	// Pie de página
	function Footer() {
        // add judge signing box
        $this->SetXY(80,-20);
        $this->SetFont($this->getFontName(),'IB',10);
        $this->Cell(58,7,_('Judge signature / Date').":  ",0,0,'R',false);
        $this->ac_SetFillColor($this->config->getEnv('pdf_hdrbg2')); // azul
        $this->Cell(60,7,"","TBRL",0,'C',true);
        $this->Ln();
		$this->print_commonFooter();
	}

    private function printTeamInformation($teamcount,$team) {
        // evaluate logos
        $logos=array();
        $maxdogs=$this->getMaxDogs();
        for($n=0;$n<$maxdogs;$n++) $logos[]='null.png';
        if ($team['Nombre']==="-- Sin asignar --") {
            $logos[0]=getIconPath($this->federation->get('Name'),"agilitycontest.png");
        } else {
            $miembros=$this->eqmgr->getPerrosByTeam($team['ID']);
            $count=0;
            for ($n=0;$n<count($miembros);$n++) {
                $logo=getIconPath($this->federation->get('Name'),$miembros[$n]['LogoClub']);
                if ( ( ! in_array($logo,$logos) ) && ($count<$maxdogs) ) $logos[$count++]=$logo;
            }
        }
        $this->ac_header(1,18);
        $this->Cell(15,7,strval(1+$teamcount)." -",'LT',0,'C',true); // imprime puesto del equipo
        $x=$this->getX();
        $y=$this->getY();
        // if no logo is "null.png" don't try to insert logo, just add empty text with parent background
        for ($n=0;$n<$maxdogs;$n++) {
            if ($logos[$n]==="null.png") {
                $this->SetX($x+10*$n);
                $this->Cell(10,7,"",'T',0,'C',true);
            } else {
                $this->Image($logos[$n],$x+10*$n,$y,7);
            }
        }
        $this->SetX($x+40);
        $this->Cell(125,7,$team['Nombre'],'T',0,'R',true);
        $this->Cell(8,7,'','TR',0,'R',true); // empty space at right of page
        $this->Ln();
        $this->ac_header(2,8);
        for($i=0;$i<count($this->cellHeader);$i++) {
            // en la cabecera texto siempre centrado. Si caza skip licencia
            if ($this->pos[$i]!=0) $this->Cell($this->pos[$i],4,$this->cellHeader[$i],1,0,'C',true);
        }
        $this->ac_row(2,9);
        $this->Ln();
        return $this->GetY();
    }

    private function printRowData($n,$equipo,$row) {

        // print team member's result
        // $this->myLogger->trace("imprimiendo datos del perro {$row['Perro']} - {$row['Nombre']}");
        // properly format special fields
        $puesto= ($row['Penalizacion']>=200)? "-":"{$row['Puesto']}º";
        $veloc= ($row['Penalizacion']>=200)?"-":number_format2($row['Velocidad'],1);
        $tiempo= ($row['Penalizacion']>=200)?"-":number_format2($row['Tiempo'],$this->timeResolution);
        $penal=number_format2($row['Penalizacion'],$this->timeResolution);

        // print row data
        $this->SetFont($this->getFontName(),'',8); // set data font size
        $this->Cell($this->pos[0],5,$row['Dorsal'],			'LBR',	0,		$this->align[0],	true);
        $this->SetFont($this->getFontName(),'B',8); // mark Nombre as bold
        $nombre=$row['Nombre'];
        if ($this->useLongNames) $nombre .= " - " . $row['NombreLargo'];
        $this->Cell($this->pos[1],5,$nombre,			'LBR',	0,		$this->align[1],	true);
        $this->SetFont($this->getFontName(),'',8); // set data font size
        if ($this->pos[2]!=0) $this->Cell($this->pos[2],5,$row['Licencia'],		'LBR',	0,		$this->align[2],	true);
        $this->Cell($this->pos[3],5,$this->getHandlerName($row),		'LBR',	0,		$this->align[3],	true);
        $this->Cell($this->pos[4],5,$row['NombreClub'],		'LBR',	0,		$this->align[4],	true);
        // en pruebas por equipos el grado se ignora
        // $this->Cell($this->pos[5],6,$row['Categoria'].' - '.$row['Grado'],	'LR',	0,		$this->align[5],	$fill);
        $cat=$this->federation->getCategoryShort($row['Categoria']);
        $this->Cell($this->pos[5],5,$cat,  	'LBR',	0,		$this->align[5],	true);
        $this->Cell($this->pos[6],5,$row['Faltas'],			'LBR',	0,		$this->align[6],	true);
        $this->Cell($this->pos[7],5,$row['Tocados'],		'LBR',	0,		$this->align[7],	true);
        $this->Cell($this->pos[8],5,$row['Rehuses'],		'LBR',	0,		$this->align[8],	true);
        $this->Cell($this->pos[9],5,$tiempo,				'LBR',	0,		$this->align[9],	true);
        $this->Cell($this->pos[10],5,$veloc,				'LBR',	0,		$this->align[10],	true);
        $this->Cell($this->pos[11],5,$penal,				'LBR',	0,		$this->align[11],	true);
        $this->Cell($this->pos[12],5,$row['CShort'],	'LBR',	0,		$this->align[12],	true);
        $this->Cell($this->pos[13],5,$puesto,			'LBR',	0,		$this->align[13],	true);
        $this->ac_header(2,8);
        // en las dos primeras filas imprimimos informacion de resultados del equipo
        if ($n==0) {
            $tg=number_format2($equipo['Tiempo'],$this->timeResolution);
            $this->Cell($this->pos[14],5,_("Time").": $tg",	'LBR',	0,		$this->align[14],	true);
        }
        if ($n==1) {
            $pg=number_format2($equipo['Penalizacion'],$this->timeResolution);
            $this->Cell($this->pos[14],5,_("Penaliz").".: $pg",	'LBR',	0,		$this->align[14],	true);
        }
        // si la clasificacion va por puntos, se incluye tambien
        if (array_key_exists('Puntos',$equipo)) {
            if ( ($n==2) && ($equipo['Puntos']!=0)) {
                $this->Cell($this->pos[14],5,_("Points").".: {$equipo['Puntos']}",	'LBR',	0,		$this->align[14],	true);
            }
        }
        $this->ac_row(2,9);
        $this->Ln(5);
    }

	// Tabla coloreada
	function composeTable() {
		$this->myLogger->enter();
        // en la cabecera texto siempre centrado. Si caza or internacional skip licencia
		if ($this->federation->hasWideLicense() ) {
            $this->pos[1]+=5; $this->pos[2]=0; $this->pos[3]+=5;$this->pos[4]+=5;
        } else if ( $this->useLongNames) {
            $this->pos[1]+=20; $this->pos[2]=0; $this->pos[4]-=5;
        }
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor'));
		$this->SetLineWidth(.3);
		
		// Datos
		$teamcount=0;
		$this->AddPage();
        $this->SetXY(10,57);
        $maxdogs=$this->getMaxDogs();
        foreach($this->equipos as $equipo) {
            // REMINDER: $this->cell( width, height, data, borders, where, align, fill)
            // si el equipo no tiene participantes es que la categoria no es válida: skip
            if (count($equipo['Resultados'])==0) continue;
            $numboxes=min($maxdogs,4*count($equipo['Resultados']));
            $boxsize = 7+4*$numboxes;
            if ($this->GetY()+$boxsize>270) {
                $this->AddPage();
                $this->SetXY(10,45);
           }
            // evaluate puesto del equipo
            $this->myLogger->trace("Equipo: ".json_encode($equipo));
            $this->printTeamInformation($teamcount,$equipo);
            // print team header/data
            for ($n=0;$n<$numboxes;$n++) {
                $this->ac_row($n,9);
                // PENDING: ajustar altura de celdas al numero de perros del equipo
                $row=$this->defaultPerro;
                if (array_key_exists($n,$equipo['Resultados'])) $row=$equipo['Resultados'][$n];
                $this->printRowData($n,$equipo,$row);
            }
            $this->Ln(3);
            $teamcount++;
        }
        $this->myLogger->leave();
	}
}
?>