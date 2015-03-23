<?php
include("SQLHE.php");

$dia= date('d', strtotime(' -3 day'));
$mes=date('m', strtotime(' -3 day'));
$anio=date('Y', strtotime(' -3 day'));
echo $dia."/".$mes."/".$anio."<br>";

$info = array('Database'=>'RelojPonches', 'UID'=>'sa', 'PWD'=>'PromeseCal1525'); 
$oCon = sqlsrv_connect('172.125.10.21\RRINSIDEPROMESE', $info);  

$infoHE = array('Database'=>'horasextra', 'UID'=>'sa', 'PWD'=>'PromeseCal1525'); 
$oConHE = sqlsrv_connect('172.125.10.21\RRINSIDEPROMESE', $infoHE);  

$query="use RelojPonches;";
sqlsrv_query($oCon, $query);

if (!$oCon)
{
	echo "NO CONECTADO Punch";
	die( print_r( sqlsrv_errors(), true));
}
else if(!$oConHE){
	echo "NO CONECTADO HE";
	die( print_r( sqlsrv_errors(), true));
}
else
{
	echo "Conectado SQLSRV <br>";
	
	$query="SELECT DISTINCT BADGENUMBER
			FROM CHECKINOUT, USERINFO
			WHERE CHECKINOUT.USERID = USERINFO.USERID
			AND MONTH(CHECKINOUT.CHECKTIME)='{$mes}'
			AND YEAR(CHECKINOUT.CHECKTIME)='{$anio}' 
			AND DAY(CHECKINOUT.CHECKTIME)='{$dia}'";
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	$rs=sqlsrv_query($oCon, $query, $params, $options);
	if($rs===false)
	{
		die(print_r(sqlsrv_errors(),true));
	}
	else
	{
		echo sqlsrv_num_rows($rs)." Empleados registrados en total <br>";
		$codigos= array();
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$codigos[]=$fila['BADGENUMBER'];
		}
		foreach($codigos as $codigo)
		{
			$querySPE="
					SELECT *
					FROM ( SELECT top 1 DATEPART(HH,CHECKINOUT.CHECKTIME) as hora, DATEPART(MI,CHECKINOUT.CHECKTIME) as minutos, CHECKINOUT.CHECKTIME as fecha
							FROM USERINFO, CHECKINOUT
							WHERE CHECKINOUT.USERID = USERINFO.USERID
							AND MONTH(CHECKINOUT.CHECKTIME)='{$mes}' AND DAY(CHECKINOUT.CHECKTIME)='{$dia}'
							AND YEAR(CHECKINOUT.CHECKTIME)='{$anio}' AND BADGENUMBER={$codigo}
							order by fecha asc
					UNION
						  SELECT top 1 DATEPART(HH,CHECKINOUT.CHECKTIME) as hora, DATEPART(MI,CHECKINOUT.CHECKTIME) as minutos, CHECKINOUT.CHECKTIME as fecha
							FROM USERINFO, CHECKINOUT
							WHERE CHECKINOUT.USERID = USERINFO.USERID
							AND MONTH(CHECKINOUT.CHECKTIME)='{$mes}' AND DAY(CHECKINOUT.CHECKTIME)='{$dia}'
							AND YEAR(CHECKINOUT.CHECKTIME)='{$anio}' AND BADGENUMBER={$codigo}
							order by fecha desc
						 ) E_S ";
			$rsSPE = sqlsrv_query($oCon, $querySPE, $params, $options);	
			
			if($rsSPE)
			{
				if(SQLHE::obtenerIdEmpleado($codigo, $oConHE)!=0)
				{
					if(sqlsrv_num_rows($rsSPE)<=0)
					{
						echo "No hay registros de {$codigo} empleado en esa fecha </br>";
					}
					else if(sqlsrv_num_rows($rsSPE)==1)//si solo aparece 1 registro en ese dia(se asumira que es la hora de entrada)
					{
						$fila=sqlsrv_fetch_array($rsSPE,SQLSRV_FETCH_ASSOC);
						$fecha =$fila['fecha'];
						$horaentrada = $fila['hora'].":".$fila['minutos'].":00";
						if(SQLHE::insertarHorario($oConHE,  SQLHE::obtenerIdEmpleado($codigo, $oConHE), $fecha->format('Y-m-d'), $horaentrada, $horaentrada))
						{
							echo "Insercion correcta  $codigo (Solo un registro)</br>";
						}
						else
						{
							echo "Insercion fallida $codigo </br>";
						}
					}
					else if(sqlsrv_num_rows($rsSPE)>=2)
					{
						$row=0;
						while($fila=sqlsrv_fetch_array($rsSPE,SQLSRV_FETCH_ASSOC))
						{
							$row+=1;
							if($row>2) break;
							if($row==1)
							{
								$fecha =$fila['fecha'];
								$horaentrada = $fila['hora'].":".$fila['minutos'];
								$idE=SQLHE::obtenerIdEmpleado($codigo, $oConHE);						
							}
							else if($row==2)
							{
								$horasalida= $fila['hora'].":".$fila['minutos'];
								if(SQLHE::insertarHorario($oConHE, $idE, $fecha->format('Y-m-d'), $horaentrada, $horasalida))
								{
									echo "Insercion correcta  $codigo completo </br>";
								}
								else
								{
									echo "Insercion fallida $codigo </br>";
								}	
							}						
						}
					}
					else if (sqlsrv_num_rows($rsSPE)>2)
					{
						$row=0;
						$limit = sqlsrv_num_rows($rsSPE);
						while($fila=sqlsrv_fetch_array($rsSPE,SQLSRV_FETCH_ASSOC))
						{
							$row+=1;
							if($row==1)
							{
								$fecha =$fila['fecha'];
								$horaentrada = $fila['hora'].":".$fila['minutos'];
								$idE=SQLHE::obtenerIdEmpleado($codigo, $oConHE);						
							}
							else if($row==$limit)
							{
								$horasalida= $fila['hora'].":".$fila['minutos'];
								if(SQLHE::insertarHorario($oConHE,$idE, $fecha->format('Y-m-d'), $horaentrada, $horasalida))
								{
									echo "Insercion correcta  $codigo  tres registros</br>";
								}
								else
								{
									echo "Insercion fallida $codigo </br>";
								}	
							}						
						}
						echo "EL LIMITE ES:".$limit."<br>";
					}
				}
				else
				{
					echo "No se encontro id para $codigo <br>";
				}
				
			}
			else
			{
				echo "Hay un Problema en el rs SPE </br>";
			}
		}
	}
	/*
	$horatemp = 8;
	$minutotemp=3;
	if ($horatemp <9) {
	$horatemp = "0".$horatemp;
	}
	if ($minutotemp <9) {
	$minutotemp = "0".$minutotemp;
	}
	
	if ($horatemp.":".$minutotemp.":".":00" < '12:00:00') {
		echo 'si';
	}
	else
	{
		echo 'no';
	}
	*/
}
echo "Done!";
sqlsrv_close($oCon);
sqlsrv_close($oConHE);
?>