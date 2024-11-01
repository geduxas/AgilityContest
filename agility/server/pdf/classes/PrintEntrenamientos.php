<?php
/*
PrintEntrenamientos.php

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
 * genera un pdf con la tabla de horarios de entrenamiento
*/
require_once(__DIR__."/../fpdf.php");
require_once(__DIR__."/../../tools.php");
require_once(__DIR__."/../../logging.php");
require_once(__DIR__.'/../../database/classes/DBObject.php');
require_once(__DIR__.'/../../database/classes/Pruebas.php');
require_once(__DIR__.'/../../database/classes/Entrenamientos.php');
require_once(__DIR__."/../print_common.php");

class PrintEntrenamientos extends PrintCommon {

    protected $cols=array();
    protected $fields=array();
    protected $sizes=array();
    protected $align=array();

	protected $orden; // orden de tandas
    protected $fedName;
	
	/**
	 * Constructor
	 * @param {integer} $prueba Prueba ID
	 * @throws Exception
	 */
	function __construct($prueba) {
        if ( $prueba<=0 ) {
            $this->errormsg="printTandas: invalid prueba id:$prueba";
            throw new Exception($this->errormsg);
        }
		parent::__construct('Portrait',"print_Entrenamientos",$prueba);

        // Datos del orden de entrenamientos
		$eobj = new Entrenamientos("PrintEntrenamientos",$prueba);
		$this->orden = $eobj->enumerate()['rows'];
        $this->fedName= $this->federation->get('Name');

        // datos del layout y cabecera de la pagina
        $s4=12; $s123=12; $corc=_('Club');
        if ($this->federation->isInternational()){ $s4=0; $s123=15; $corc=_('Country'); }
        // fill columns
        $this->cols     = array( '#',    '',        $corc, _('Date'), _('Check in'),_('Veterinary'), _('Start'), _('Duration'), _('Ring').' 1', _('Ring').' 2', _('Ring').' 3', _('Ring').' 4');
        $this->fields   = array( /*eval*/'','LogoClub','NombreClub','Fecha','Firma', 'Veterinario','Comienzo','Duracion',/*keyValue1 */ '',/*KeyValue2*/ '',/*KeyValue3*/ '',/*KeyValue4*/ '');
        $this->sizes    = array(    10,         10,     30,         23,     20,       15,          18,       18,          $s123,          $s123,           $s123,           $s4 );
        $this->align    = array(    'C',        'C',    'L',        'L',    'C',      'C',         'C',      'R',         'C',            'C',             'C',             'C');
        // set file name as Entrenamientos_prueba
        $this->set_FileName("Entrenamientos.pdf");
        $this->icon2=getIconPath($this->federation->get('Name'),"null.png");
	}
	
	// Cabecera de página
	function Header() {
		// cabecera comun
		$this->print_commonHeader(_("Vet & Training schedule"));
        $this->Ln(5);
	}
	
	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}
	
	function writeTableHeader() {
        $this->ac_header(1,10);
        $this->setX(10);
        for( $n=0; $n<count($this->cols); $n++) {
            $a=($n==0)?'LRTB':'RTB';
            if ($this->sizes[$n]==0) continue;
            $this->Cell($this->sizes[$n],7,$this->cols[$n],$a,0,$this->align[$n],true);
        }
		$this->Ln();
	}
	
	// Tabla coloreada
	function composeTable() {
		$this->myLogger->enter();
		
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor'));
		$this->SetLineWidth(.3);
		$rowcount=0;
		foreach($this->orden as $row) {
			// $this->cell(width,height,text,border,start,align,fill)
			if (($rowcount%42)==0) {
				$this->AddPage();
				$this->writeTableHeader();
			}
            $this->ac_row($rowcount,8); // set color and font size for this row
			for ($n=0;$n<count($this->cols);$n++) {
			    if ($this->sizes[$n]==0) continue;
			    switch ($n) {
			        // handle special cases
                    case 0: /* orden */
                        $this->SetFont($this->getFontName(),'B',10);
                        $this->Cell($this->sizes[$n],5.5,1+$rowcount,'LR',0,$this->align[$n],true); // orden en negritas
                        $this->SetFont($this->getFontName(),'',7);
                        break;
                    case 1: /* logo */
                        $logo=getIconPath($this->fedName,$row['LogoClub']);
                        $x=1+$this->GetX();$y=1+$this->GetY();
                        $this->Image($logo,$x,$y,$this->sizes[$n]-2,4);
                        // $this->Cell($this->sizes[$n],5.5,'','',0,$this->align[$n],true); // fondo de logos
                        $this->SetX($x+$this->sizes[$n]-1);
                        break;
                    case 2: /* country/Club (bold) */
                        $this->SetFont($this->getFontName(),'B',8);
                        $this->Cell($this->sizes[$n],5.5,$row['NombreClub'],'LR',0,$this->align[$n],true); // club/pais en negritas
                        $this->SetFont($this->getFontName(),'',7);
                        break;
                    case 3: /* day */
                        $r=date_parse($row[$this->fields[$n]]);
                        $d = mktime($r['hour'], $r['minute'], $r['second'], $r['month'], $r['day'], $r['year']);
                        $val=date("l - d", $d);
                        $this->Cell($this->sizes[$n],5.5,$val,'R',0,$this->align[$n],true);
                        break;
                    case 4: /* check in */
                    case 5: /* vet */
                    case 6: /* entry */
                        $r=date_parse($row[$this->fields[$n]]);
                        $d = mktime($r['hour'], $r['minute'], $r['second'], $r['month'], $r['day'], $r['year']);
                        $val=date("H:i:s", $d);
                        $this->Cell($this->sizes[$n],5.5,$val,'R',0,$this->align[$n],true);
                        break;
                    case 7: /* duration */
                        $val=date("i:s",$row[$this->fields[$n]]); // 'Duration' comes in seconds
                        $this->Cell($this->sizes[$n],5.5,$val,'R',0,$this->align[$n],true);
                        break;
                    case 8:
                    case 9:
                    case 10:
                    case 11: // rings
                        $idx=strval($n-7);
                        $key=$row["Key{$idx}"];
                        $value=$row["Value{$idx}"];
                        $data=($key==="")? "---" : "$key - $value";
                        $this->Cell($this->sizes[$n],5.5,$data,'R',0,$this->align[$n],true);
                        break;
                    default:
                        $this->Cell($this->sizes[$n],5.5,$row[$this->fields[$n]],'R',0,$this->align[$n],true);
                        break;
                }
            }
            $rowcount++;
            $this->Ln(5.5);
		}
		$this->myLogger->leave();
        return "";
	}
}
?>