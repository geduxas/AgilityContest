<?php
class RSCE extends Federations {

    function __construct() {
        parent::__construct();
        // combine global data with specific data for this federation
        $this->config= array_merge ($this->config, array(
            'ID'    => 0,
            'Name'  => 'RSCE',
            'ClassName' => get_class($this),
            'LongName' => 'Real Sociedad Canina de España',
            // use basename http absolute path for icons, as need to be used in client side
            'OrganizerLogo'     => 'rsce.png',
            'Logo'     => 'rsce.png',
            'ParentLogo'   => 'fci.png',
            'WebURL' => 'http://www.rsce.es/',
            'ParentWebURL' => 'http://www.fci.org/',
            'Email' => 'agility@rsce.es',
            'Heights' => 3,
            'Grades' => 3,
            'Games' => 0,
            'International' => 0,
            'LicenseType' => Federations::$LICENSE_REQUIRED_SHORT, // no license required
            'RoundsG1' => 2, // on rfec may be 3
            'Recorridos' => array(
                _('Common course'),
                _('Standard / Midi + Mini'),
                _('Separate courses'),
                "Invalid"),
            'ListaGradosShort' => array(
                '-' => 'Sin especificar',
                // 'Jr' => 'Jr.',
                // 'Sr' => 'Sr.',
                // 'Ch' => 'Ch.',
                // 'Par' => 'Par.',
                'GI' => 'GI',
                'GII'=> 'GII',
                'GIII' => 'GIII',
                'P.A.' => 'P.A.',
                'P.B.' => 'P.B.' // "perro en blanco"
            ),
            'ListaGrados'    => array (
                '-' => 'Sin especificar',
                // 'Jr' => 'Junior',
                // 'Sr' => 'Senior',
                // 'Ch' => 'Children',
                // 'Par' => 'ParaAgility',
                'GI' => 'Grado I',
                'GII'=> 'Grado II',
                'GIII' => 'Grado III',
                'P.A.' => 'Pre-Agility',
                'P.B.' => 'Perro en Blanco'
            ),
            'ListaCategoriasShort' => array (
                '-' => '-',
                'X' => 'XL',
                'L' => 'Std',
                'M' => 'Midi',
                'S' => 'Mini',
                'T' => 'Toy'
            ),
            'ListaCategorias' => array (
                '-' => 'Sin especificar',
                'X' => 'Extra Large',
                'L' => 'Standard',
                'M' => 'Midi',
                'S' => 'Mini',
                'T' => 'Toy'
            ),
            'ListaCatGuias' => array (
                '-' => 'Sin especificar',
                // 'I' => 'Infantil',
                'J' => 'Junior',
                'A' => 'Adulto',
                'S' => 'Senior',
                'R' => 'Retirado',
                // 'P' => 'Para-Agility',
            ),
            'InfoManga' => array(
                array('L' => _('Standard'), 'M' => _('Midi'),        'S' => _('Mini'),  'T' => '', 'X' => ''), // separate courses
                array('L' => _('Standard'), 'M' => _('Midi+Mini'),   'S' => '',         'T' => '', 'X' => ''), // mixed courses
                array('L' => _('Std+Midi+Mini'), 'M' => '',          'S' => '',         'T' => '', 'X' => ''), // common
                array('L' => '',            'M' => '',               'S' => '',         'T' => '', 'X' => '') // common
            ),
            'Modes' => array(
                // categorias        L  M  S   T   X
                array(/* separado */ 0, 1, 2, -1, -1),
                array(/* 2 grupos */ 0, 3, 3, -1, -1),
                array(/* conjunto */ 4, 4, 4, -1, 4 ), // pre-agility is -XLMST in tandas cat assignment
                array(/* 3 grupos */-1,-1,-1, -1, -1 ) // invalid en rsce
            ),
            'ModeStrings' => array( // text to be shown on each category
                array(/* separado */ "Standard", "Midi", "Mini", "Invalid","Invalid"),
                array(/* mixto */ "Standard", "Midi+Mini", "Midi+Mini", "Invalid","Invalid"),
                array(/* conjunto */ "Conjunta", "Conjunta", "Conjunta", "Invalid","Invalid"),
                array(/* conjunto */ "Invalid", "Invalid", "Invalid", "Invalid","Invalid")
            ),
            'IndexedModes' => array (
                "Standard",
                "Midi",
                "Mini",
                "Midi+Mini",
                "Conjunta L/M/S",
                "Tiny",
                "Standard+Midi",
                "Mini+Tiny",
                "Conjunta L/M/S/T",
                "Extra Large",
                "Large + XL",
                "Med+Small+Tiny",
                "Common X/L/M/S/T"
            ),
            'IndexedModeStrings' => array(
                "-" => "",
                "L"=>"Standard",
                "M"=>"Midi",
                "S"=>"Mini",
                "T"=>"Tiny", // invalid
                "LM"=>"Standard/Midi", // invalid
                "ST"=>"Mini/Tiny", // invalid
                "MS"=>"Midi/Mini",
                "LMS" => 'Conjunta LMS',
                "-LMS" => 'Conjunta LMS',
                "LMST" => 'Conjunta LMST', // invalid
                "-LMST" => 'Conjunta LMST', // invalid
                "X" => '',
                "XL" => '',
                "MST" => '',
                "XLMST" => '',
                "-XLMST"
            ),
            'NombreTandas' => array(
                0	=> '-- Sin especificar --',
                1	=> 'Pre-Agility 1',
                2	=> 'Pre-Agility 2',
                3	=> 'Agility-1 GI Standard',
                4	=> 'Agility-1 GI Midi',
                5	=> 'Agility-1 GI Mini',
                6	=> 'Agility-2 GI Standard',
                7	=> 'Agility-2 GI Midi',
                8	=> 'Agility-2 GI Mini',
                9	=> 'Agility GII Standard',
                10	=> 'Agility GII Midi',
                11	=> 'Agility GII Mini',
                12	=> 'Agility GIII Standard',
                13	=> 'Agility GIII Midi',
                14	=> 'Agility GIII Mini',
                15	=> 'Agility Standard', //  Individual-Open
                16	=> 'Agility Midi',	//  Individual-Open
                17	=> 'Agility Mini', //  Individual-Open
                18	=> 'Agility Equipos Std', // team best
                19	=> 'Agility Equipos Midi',// team best
                20	=> 'Agility Equipos Mini',	 // team best
                // en jornadas por equipos conjunta tres alturas se mezclan categorias M y S
                21	=> 'Ag. Equipos Std',// team combined
                22	=> 'Ag. Equipos Midi/Mini', // team combined
                23	=> 'Jumping GII Standard',
                24	=> 'Jumping GII Midi',
                25	=> 'Jumping GII Mini',
                26	=> 'Jumping GIII Standard',
                27	=> 'Jumping GIII Midi',
                28	=> 'Jumping GIII Mini',
                29	=> 'Jumping Standard',//  Individual-Open
                30	=> 'Jumping Midi',	//  Individual-Open
                31	=> 'Jumping Mini', //  Individual-Open
                32	=> 'Jumping Equipos Std',	// team best
                33	=> 'Jumping Equipos Midi',// team best
                34	=> 'Jumping Equipos Mini',	// team best
                // en jornadas por equipos conjunta 3 alturas se mezclan categorias M y S
                35	=> 'Jp. Equipos Std',// team combined
                36	=> 'Jp. Equipos Midi/Mini', // team combined
                // en las rondas KO, los perros compiten todos contra todos
                37	=> 'Manga K.O. - 1',
                38	=> 'Manga Especial Standard',
                39	=> 'Manga Especial Midi',
                40	=> 'Manga Especial Mini',

                // LAS SIGUIENTES CONFIGURACIONES NO EXISTEN EN RSCE (3 alturas)
                // "Tiny" support for Pruebas de cuatro alturas
                41	=> 'Agility-1 GI Tiny',
                42	=> 'Agility-2 GI Tiny',
                43	=> 'Agility GII Tiny',
                44	=> 'Agility GIII Tiny',	// no existe
                45	=> 'Agility Tiny', //  Individual-Open
                46	=> 'Agility Equipos Tiny',// team best
                // en equipos4  cuatro alturas  agrupamos por LM y ST
                47	=> 'Ag. Equipos Large/Medium', // team combined
                48	=> 'Ag. Equipos Small/Tiny', // team combined
                49	=> 'Jumping GII Tiny',
                50	=> 'Jumping GIII Tiny', // no existe
                51	=> 'Jumping Tiny', //  Individual-Open
                52	=> 'Jumping team Tiny',	 // team best
                53	=> 'Jp. teams Large/Medium',  // team combined
                54	=> 'Jp. teams Small/Tiny',// team combined
                55	=> 'Special round Tiny',
                56	=> 'Agility-3 GI Large',	 // extra rounds for GI RFEC
                57	=> 'Agility-3 GI Medium',
                58	=> 'Agility-3 GI Small',
                59	=> 'Agility-3 GI Tiny',
                // resto de las mangas k.o.
                60	=> 'Manga K.O. - 2',
                61	=> 'Manga K.O. - 3',
                62	=> 'Manga K.O. - 4',
                63	=> 'Manga K.O. - 5',
                64	=> 'Manga K.O. - 6',
                65	=> 'Manga K.O. - 7',
                66	=> 'Manga K.O. - 8',

                // las siguientes mangas no tienen sentido en RSCE, pero las dejamos por compatibilidad
                // tandas para games/wao ( cuatro categorias hasta 2021, siete mangas distintas )
                67	=> 'Agility A 650',
                68	=> 'Agility A 525',
                69	=> 'Agility A 400',
                70	=> 'Agility A 300',
                71	=> 'Agility B 650',
                72	=> 'Agility B 525',
                73	=> 'Agility B 400',
                74	=> 'Agility B 300',
                75	=> 'Jumping A 650',
                76	=> 'Jumping A 525',
                77	=> 'Jumping A 400',
                78	=> 'Jumping A 300',
                79	=> 'Jumping B 650',
                80	=> 'Jumping B 525',
                81	=> 'Jumping B 400',
                82	=> 'Jumping B 300',
                83	=> 'Snooker 650',
                84	=> 'Snooker 525',
                85	=> 'Snooker 400',
                86	=> 'Snooker 300',
                87	=> 'Gambler 650',
                88	=> 'Gambler 525',
                89	=> 'Gambler 400',
                90	=> 'Gambler 300',
                91	=> 'SpeedStakes 650',
                92	=> 'SpeedStakes 525',
                93	=> 'SpeedStakes 400',
                94	=> 'SpeedStakes 300',
                // mangas para junior
                95  => 'Junior 1 Large',
                96  => 'Junior 1 Medium',
                97  => 'Junior 1 Small',
                98  => 'Junior 1 Toy',
                99  => 'Junior 2 Large',
                100  => 'Junior 2 Medium',
                101  => 'Junior 2 Small',
                102  => 'Junior 2 Toy',
            ),
            'TipoMangas' => array(
                0 =>	array( 0, 'Nombre Manga largo',	'Grado corto',	'Nombre manga',	'Grado largo',  'IsAgility'),
                1 =>	array( 1, 'Pre-Agility Manga 1', 		'P.A.',	'PreAgility 1',	'Pre-Agility',  1),
                2 => 	array( 2, 'Pre-Agility Manga 2', 		'P.A.',	'PreAgility 2',	'Pre-Agility',  2),
                3 =>	array( 3, 'Agility Grado I Manga 1',	'GI',	'Agility-1 GI',	'Grado I',      1),
                4 => 	array( 4, 'Agility Grado I Manga 2',	'GI',	'Agility-2 GI',	'Grado I',      2),
                5 =>	array( 5, 'Agility Grado II', 			'GII',	'Agility GII',	'Grado II',     1),
                6 =>	array( 6, 'Agility Grado III', 			'GIII',	'Agility GIII',	'Grado III',    1),
                7 =>	array( 7, 'Agility', 	        		'-',	'Agility',		'Individual',   1), // Open
                8 =>	array( 8, 'Agility Equipos',			'-',	'Ag. Equipos',	'Equipos',      1), // team best
                9 =>	array( 9, 'Agility Equipos'				,'-',	'Ag. Equipos.',	'Equipos',      1), // team combined
                10 =>	array( 10,'Jumping Grado II',			'GII',	'Jumping GII',	'Grado II',     2),
                11 =>	array( 11,'Jumping Grado III',			'GIII',	'Jumping GIII',	'Grado III',    2),
                12 =>	array( 12,'Jumping',    				'-',	'Jumping',		'Individual',   2), // Open
                13 =>	array( 13,'Jumping Equipos'				,'-',   'Jp. Equipos',	'Equipos',      2), // team best
                14 =>	array( 14,'Jumping Equipos'				,'-',  	'Jp. Equipos',	'Equipos',      2), // team combined
                15 =>	array( 15,'K.O. Primera manga',			'-',	'K.O. manga 1',	'K.O. 1',       1),
                16 =>	array( 16,'Manga Especial', 			'-',	'Manga Especial','Individual',  1), // special round, no grades
                17 => 	array( 17,'Agility Grado I Manga 3',	'GI',	'Agility-3 GI',	'Grado I',      3), // on RFEC special G1 3rd round
                // mangas extra para K.O.
                18 =>	array( 18,'K.O. Segunda manga',			'-',	'K.O. Manga 2',	'K.O. - 2',      2),
                19 =>	array( 19,'K.O. Tercera manga',			'-',	'K.O. Manga 3',	'K.O. - 3',      3),
                20 =>	array( 20,'K.O. Cuarta manga',			'-',	'K.O. Manga 4',	'K.O. - 4',      4),
                21 =>	array( 21,'K.O. Quinta manga',			'-',	'K.O. Manga 5',	'K.O. - 5',      5),
                22 =>	array( 22,'K.O. Sexta manga',			'-',	'K.O. Manga 6',	'K.O. - 6',      6),
                23 =>	array( 23,'K.O. Septima manga',			'-',	'K.O. Manga 7',	'K.O. - 7',      7),
                24 =>	array( 24,'K.O. Octava manga',			'-',	'K.O. Manga 8',	'K.O. - 8',      8),
                /**** estas mangas no existen en rsce. se ponen por compatibilidad *********/
                // mandas extras para wao
                25 =>	array( 25,'Agility A',			        '-',	'Agility A',	'Ag. A',        1),
                26 =>	array( 26,'Agility B',			        '-',	'Agility B',	'Ag. B',        2),
                27 =>	array( 27,'Jumping A',			        '-',	'Jumping A',	'Jp. A',        3),
                28 =>	array( 28,'Jumping B',			        '-',	'Jumping B',	'Jp. B',        4),
                29 =>	array( 29,'Snooker',			        '-',	'Snooker',	    'Snkr',         5),
                30 =>	array( 30,'Gambler',			        '-',	'Gambler',	    'Gmblr',        6),
                31 =>	array( 31,'SpeedStakes',			    '-',	'SpeedStakes',	'SpdStk',       7), // single round
                // junior ( really should be a separate journey with every cats and grades, but people doesn't follow rules... )
                32 =>	array( 32,'Junior Manga 1',	            'Jr',	'Junior 1',	    'Jr. 1',        1),
                33 => 	array( 33,'Junior Manga 2',	            'Jr',	'Junior 2',	    'Jr. 2',        2),
                34 =>   array( 34, 'Senior Round 1',            'Sr',   'Senior 1',     'Sr. 1',        1),
                35 =>   array( 35, 'Senior Round 2',            'Sr',   'Senior 2',     'Sr. 2',        2)
            )
        ));
    }

}
?>