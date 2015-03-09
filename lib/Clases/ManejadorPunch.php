<?php 
	class ManejadorPunch
	{
		static function cargarHorario($fecha1, $fecha2)
		{
			set_time_limit(0);
			$intentos = 0;
			$fecha;
			while($intentos <2)
			{
				$intentos+=1;
				if($intentos>2)
				{
					break;
				}
				$tempID=0;
				$idH=0;
				if($intentos <=1)
				{
					$query="SELECT empleado.id as idE, ponches.hora as hora
							from empleado, ponches
							where codigo_empleado = cod_empleado 
							and ponches.fecha = '{$fecha1}'
							order by idE, hora;";	
					$fecha = $fecha1;
				}
				else
				{
					$query="SELECT empleado.id as idE, ponches.hora as hora
							from empleado, ponches
							where codigo_empleado = cod_empleado 
							and ponches.fecha = '{$fecha2}'
							order by idE, hora;";
					$fecha = $fecha2;
				}

				$rs = mysql_query($query);
				if($rs)
				{
					while($fila = mysql_fetch_assoc($rs))
					{
						if($fila['idE'] == $tempID)
						{
							$queryUPD="UPDATE temp_horario set
								horadesalida='{$fila['hora']}'
								WHERE temp_horario.id = {$idH};";
							mysql_query($queryUPD);
							//echo "Haciendo Update a registro {$tempID} con hora de salida = {$fila['hora']} en el id Horario {$idH} <br>";
						}
						else
						{
							$tempID = $fila['idE'];
							$queryInst="INSERT INTO temp_horario 
										(id_empleado, horadeentrada,fecha)
										VALUES
										({$fila['idE']}, '{$fila['hora']}', '{$fecha}');";
							mysql_query($queryInst);
							$idH = mysql_insert_id();
							//echo "Insertando registro {$fila['idE']} con hora de entrada de {$fila['hora']} en el idHorario {$idH}<br>";
						}
					}	
				}				
			}
		}
		
		static function guardar($empleado, $hora, $fecha)
		{		
			set_time_limit(0);
			$conexion = mysql_connect("localhost", "root", "4261");
			$db = mysql_select_db ("horasextra",$conexion) or die ("ERROR AL CONECTAR A LA BD");
			$queryD="SELECT * FROM ponches
					 WHERE cod_empleado ={$empleado} AND hora='{$hora}' AND fecha='{$fecha}'";
			$rs =mysql_query($queryD) or die( mysql_error() );
			if(mysql_num_rows($rs)<=0)
			{
				$query="INSERT INTO
						ponches 
						(cod_empleado, hora, fecha)
						VALUES
						({$empleado}, '{$hora}', '{$fecha}')";
				mysql_query($query) or die( mysql_error() );		
			}
		}
		
		static function limpiarHorario()
		{
			$query="
			INSERT INTO horario(id_empleado, fecha, horadeentrada, horadesalida)
			SELECT id_empleado, fecha, horadeentrada, horadesalida FROM temp_horario
			WHERE (temp_horario.id_empleado, temp_horario.fecha, temp_horario.horadeentrada, temp_horario.horadesalida) NOT IN 
			(SELECT horario.id_empleado, horario.fecha, horario.horadeentrada, horario.horadesalida FROM horario);";
			$rs = mysql_query($query) or die (mysql_error());
			if($rs)
			{
				$query2="TRUNCATE temp_horario;";
				$rs2=mysql_query($query2) or die( mysql_error() );
				if($rs2)
				{
					//$query3="TRUNCATE ponches;";
					//$rs3=mysql_query($query3) or die( mysql_error() );
				}
			}
		}
	}
?>