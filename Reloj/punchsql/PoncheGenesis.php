<?php
include("SQLHE.php");

if(isset($_GET['days']) && !empty($_GET['days']))
{

     $days = $_GET['days'];

/* Credenciales de ejemplo - Configurar según entorno */
$serverName = "SERVER_NAME_EXAMPLE"; // Ejemplo: 'localhost' o '192.168.1.50'
$u = "DB_USER_EXAMPLE";
$p = "DB_PASS_EXAMPLE";

$info = array('Database'=>'horasextra', 'UID'=>$u, 'PWD'=>$p); 
$oCon = sqlsrv_connect($serverName, $info);  

$infoHE = array('Database'=>'horasextra', 'UID'=>$u, 'PWD'=>$p); 
$oConHE = sqlsrv_connect($serverName, $infoHE);  

$query="use horasextra;";
sqlsrv_query($oCon, $query);
// where Fecha >= '2021-04-19 07:00:55.000'
set_time_limit(0);

for ($i=$days; $i >= 1; $i--) {  

	$dia= date('d', strtotime(-$i.' day'));
	$mes=date('m', strtotime(-$i.' day'));
	$anio=date('Y', strtotime(-$i.' day'));

	echo $dia."/".$mes."/".$anio."<br>";

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
		
		$query="SELECT DISTINCT CodigoHoraExtra
				FROM DatosHorasExtras
				WHERE MONTH(Fecha)='{$mes}'
				AND YEAR(Fecha)='{$anio}'
				AND DAY(Fecha)='{$dia}'";
		
		echo $query . "<br>";
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
				$codigos[]=$fila['CodigoHoraExtra'];
			}
			foreach($codigos as $codigo)
			{
				$querySPE="											 
						SELECT  DATEPART(HH,Fecha) as hora, DATEPART(MI,Fecha) as minutos, Fecha as fecha
							FROM DatosHorasExtras
							WHERE MONTH(Fecha)='{$mes}' AND DAY(Fecha)='{$dia}'
							AND YEAR(Fecha)='{$anio}' AND CodigoHoraExtra='{$codigo}'
							order by fecha asc";
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
						else if(sqlsrv_num_rows($rsSPE)==2)
						{
							$row=1;
							while($fila=sqlsrv_fetch_array($rsSPE,SQLSRV_FETCH_ASSOC))
							{
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
								$row+=1;						
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
	}
	echo "Done! <br>";
	echo "========================================== <br>";

}

sqlsrv_close($oCon);
sqlsrv_close($oConHE);
}

?>