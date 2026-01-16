<?php
include('lib/motor.php');
	$idS=10827;
	$conteo = array();
	$tdes=0;
	$tal=0;
	$tcen=0;
	$tdor=0;

	$arrFechaE = array();
	$arrFechaS = array();
	$arrHoraE = array();
	$arrHoraS = array();
	$conteo = array();
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	$queryCargar="SELECT * FROM destinos_viaticos WHERE id_viatico = {$idS}";

	$rsCargar=sqlsrv_query($_SESSION['con'],$queryCargar, $params, $options);
	
	while($filaC=sqlsrv_fetch_array($rsCargar, SQLSRV_FETCH_ASSOC))
	{
		$arrFechaE[]=$filaC['fecha_entrada'];
		$arrFechaS[]=$filaC['fecha_salida'];
		$arrHoraE[]=$filaC['hora_entrada'];
		$arrHoraS[]=$filaC['hora_salida'];
	}	
	
	$dias;
	for($f=0;$f<count($arrFechaE);$f++)
	{
		$fecha = $arrFechaE[$f]->format('Y-m-d');
		$fecha2 = $arrFechaS[$f]->format('Y-m-d');
		
		$dias = (strtotime($arrFechaE[$f]->format('Y-m-d'))-strtotime($arrFechaS[$f]->format('Y-m-d')))/86400;
		$dias = abs($dias); $dias = floor($dias);	
			
		for($x=0;$x<=$dias;$x++)
		{
			$horaE = explode(":", $arrHoraE[$f]->format('H:i:s'));
			$horaS = explode(":", $arrHoraS[$f]->format('H:i:s'));
			if ($dias>=1)
			{
				if ($x==$dias) {
					if($arrHoraS[$f]->format('H:i:s') >= '07:30')
					{
						$tdes+=1;
					}

					if($arrHoraS[$f]->format('H:i:s') >='13:00') {
						$tal+=1;
					}

					if ($arrHoraS[$f]->format('H:i:s')>='19:00') {
						$tcen+=1;
					}
				}
				else
				{
					if ($x==0) {
						if($arrHoraE[$f]->format('H:i:s') <= '07:30' )
						{
							$tdes+=1;
						}

						if($arrHoraE[$f]->format('H:i:s') <='12:00' && $arrHoraS[$f]->format('H:i:s') >='13:00' ) {
							$tal+=1;
						}

						$tcen+=1;

					}
					else
					{
						$tdes+=1;
							
						$tal+=1;

						$tcen+=1;							
					}
				}
			}
			else
			{
				if($arrHoraE[$f]->format('H:i:s') <= '07:30' ){
					$tdes+=1;
				}

				if($arrHoraE[$f]->format('H:i:s') <='12:00' && $arrHoraS[$f]->format('H:i:s') >='13:00' ) {
					$tal+=1;
				}

				if ($arrHoraS[$f]->format('H:i:s') >='19:00') {
					$tcen+=1;
				}
			}
		}

		$tdor= $dias;
		$conteo[] = $fecha.";".$fecha2.";".$tdes.";".$tal.";".$tcen.";".$tdor;
		$tdes=0;$tal=0;$tcen=0;
	}

	echo "<pre>";
	var_dump($conteo);
	echo "</pre>";


