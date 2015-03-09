<?php 
include('Calculo.php');
if (!defined('Hora_Entrada')) define('Hora_Entrada', 8);
if (!defined('Minuto_Entrada')) define('Minuto_Entrada', 15);
if (!defined('Hora_Salida')) define('HoraSalida', 4);	
if(!isset($_SESSION)){
	session_start();
}
	class Manejador
	{
	
		// esta funcion busca en la base de datos y retorna todos los empleados, con su informacion para ser mostrada en el index.php
		static function obtenerHorarioEmpleados()
		{
			$sql="  SELECT empleado.id as id, nombre, departamento, DATE_FORMAT(fecha, '%d/%m/%Y') as fecha, DATE_FORMAT(horadeentrada, '%h:%i %p') as horadeentrada,
					DATE_FORMAT(horadesalida, '%h:%i %p') as horadesalida, sueldo, cargo, cedula, horario.id as horario
					FROM empleado, horario
					WHERE empleado.id = horario.id_empleado and fecha='".date("Y-m-d")."'";
			return mysql_query($sql);
		}
		
		static function obtenerOrdenamientos()
		{
			echo "
				<option value='nombre'>Nombre</option>
				<option value='cargo'>Cargo</option>
				<option value='departamento'>Departamento</option>
				<option value='fecha'>Fecha</option>
				<option value='horadeentrada'>Entrada</option>
				<option value='horadesalida'>Salida</option>
				<option value='tiempo_extra'>Tiempo Extra</option>
				<option value='pago'>Pago</option>
			";
		}
		
		static function obtenerOrdenamientosViaticos()
		{
			echo "
				<option value='nombre'>Nombre</option>
				<option value='cargo'>Cargo</option>
				<option value='departamento'>Departamento</option>
				<option value='dietaviatico.no_oficio'>Solicitud</option>
				<option value='hora_entrada'>Hora de Salida</option>
				<option value='hora_salida'>Hora de Entrada</option>
				<option value='fecha_entrada'>Fecha de Salida</option>
				<option value='fecha_salida'>Fecha de Entrada</option>
			";
		}
		static function obtenerEmpleados($parametro="")
		{
			if(!empty($parametro))
			{
				$sql="  SELECT empleado.id as id, empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, sueldo, codigo_empleado, horario_especial, tipo_viatico 
						FROM empleado, t_cargo, t_departamento
						WHERE t_departamento.id = empleado.departamento
						AND t_cargo.id = empleado.cargo
						AND empleado.nombre like '%{$parametro}%'
						ORDER BY nombre";
			}
			else if (strlen($parametro) ==0)
			{
				$sql="  SELECT empleado.id as id, empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, sueldo, codigo_empleado, horario_especial, tipo_viatico 
						FROM empleado, t_cargo, t_departamento
						WHERE t_departamento.id = empleado.departamento
						AND t_cargo.id = empleado.cargo
						ORDER BY nombre";			
			}
			$rs= mysql_query($sql);	
			return $rs;
		}
		
		
		static function obtenerMeses()
		{
			echo   "<option value='todos'>Todos</option>
					<option value='1'>Enero</option>
					<option value='2'>Febrero</option>
					<option value='3'>Marzo</option>
					<option value='4'>Abril</option>
					<option value='5'>Mayo</option>
					<option value='6'>Junio</option>
					<option value='7'>Julio</option>
					<option value='8'>Agosto</option>
					<option value='9'>Septiembre</option>
					<option value='10'>Octubre</option>
					<option value='11'>Noviembre</option>
					<option value='12'>Diciembre</option>
			";
		}
		//cambiarle la tabla, horario
		static function obtenerAnios()
		{
			$query="SELECT DISTINCT DATE_FORMAT(fecha, '%Y') as a
					FROM empleado";
			$rs = mysql_query($query);
			
			if(!$rs)
			{
				echo "No se pudieron cargar los años. ";
			}
			else
			{
				echo "<option value='todos'>Todos</option>";
				while($fila=mysql_fetch_assoc($rs))
				{
					echo "<option value='{$fila['a']}'>{$fila['a']}</option>";
				}
			}
		}		
			
		static function obtenerDepartamentos($preferido)
		{
			$query="SELECT DISTINCT t_departamento.nombre as departamento, t_departamento.id as id
					FROM empleado, t_departamento
					WHERE empleado.departamento = t_departamento.id";
			$rs = mysql_query($query);
			
			if(!$rs)
			{
				echo "No se pudieron cargar los departamentos. ";
			}
			else
			{
				echo "<option value='todos'>Todos</option>";
				while($fila=mysql_fetch_assoc($rs))
				{
					if($preferido == $fila['id'])
					{
						echo "<option value='{$fila['id']}' selected>{$fila['departamento']}</option>";
						$preferido=0;
					}				
					echo "<option value='{$fila['id']}'>{$fila['departamento']}</option>";
				}
			}
		}
		//obtiene la informacion de los empleados seleccionados
	    function obtenerSeleccionados($arrayid,$arhorario, $fecha)
		{
			$i =0;$feriado;
			$datos = Array();
			foreach($arrayid as $ids)
			{
				$query="SELECT empleado.id as id, empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, DATE_FORMAT(fecha, '%d/%m/%Y') as fecha, DATE_FORMAT(horadeentrada, '%h:%i %p') as horadeentrada, 
						DATE_FORMAT(horadesalida, '%h:%i %p') as horadesalida, sueldo
						FROM empleado, horario, t_departamento, t_cargo
						WHERE empleado.id = {$ids} and empleado.id = horario.id_empleado and horario.id ={$arhorario[$i]}
						AND t_cargo.id = empleado.cargo
						AND t_departamento.id = empleado.departamento";
				$rs = mysql_query($query);
				if($rs)
				{
					if(mysql_num_rows($rs) > 0)
					{
						$c = new Calculo();
						while($fila=mysql_fetch_assoc($rs))
						{
							$porciento = $c->calcularPorcientoSueldo($fila['sueldo']);
							$tiempoExtra = $this->obtenerTiempoExtraF($fila['id'],$arhorario[$i]);
							$feriado = $this->esFeriado($fecha);
							if($feriado==true)
							{
								$pago = round($c->calcularHoraExtraFeriada($this->obtenerCantidadHoras($fila['id'], $arhorario[$i], true),$fila['sueldo']),2);	
							}
							else
							{
								$pago = round($c->calcularHoraExtraNormal($this->obtenerCantidadHoras($fila['id'], $arhorario[$i], false),$fila['sueldo']),2);	
							}
							if($pago >0)
							{
								$datos[] = $fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['fecha'].";".$fila['horadeentrada'].";".$fila['horadesalida'].";".$tiempoExtra.";".$pago;
							}
							echo "<tr class='tab_bg_2'>
										<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$arhorario[$i]}' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtNombre' value='{$fila['nombre']}' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtCedula' value='{$fila['cargo']}' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtCargo' value='{$fila['cedula']}' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtFecha' value='{$fila['fecha']}' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtHoraEntrada' value='{$fila['horadeentrada']}' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtHoraSalida' value='{$fila['horadesalida']}' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtTiempoExtra' value='{$tiempoExtra}' readonly ></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtPorciento' value='{$porciento} RD$' readonly></input></td>
										<td><input id='txt' style='width: 100%;' type='text' name='txtPago' value='{$pago} RD$' readonly></input></td>
								 </tr>";
						}
					}
				}
				$i++;
			}
			$_SESSION['datos'] = $datos;
		}
		function obtenerTiempoExtraF($id, $horario)
		{
			if($this->obtenerHoras($id,$horario) ==0 && $this->obtenerMinutos($id, $horario) ==0)
			{
				$tiempoTotal = '------';
			}
			else
			{
				$tiempoTotal = $this->obtenerHoras($id,$horario)-8 .":".$this->obtenerMinutos($id, $horario);
			}
			return $tiempoTotal;
		}

		function obtenerCantidadHoras($id, $horario, $feriado=false)
		{
			$query="SELECT  HOUR(horadeentrada) as horaentrada, MINUTE(horadeentrada) as minutoentrada,
							HOUR(horadesalida) as horasalida, MINUTE(horadesalida) as minutosalida
					FROM empleado, horario
					WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";
			$rs = mysql_query($query);
			if($rs)
			{
				if(mysql_num_rows($rs) >0)
				{
					$fila = mysql_fetch_assoc($rs);
					
					if($this->empleadoEspecial($id) or $feriado==true)
					{
						//echo "Es especial <br>";
						$query2="SELECT HOUR( TIMEDIFF( horadeentrada , horadesalida ) ) AS Horas,
										MINUTE( TIMEDIFF( horadeentrada, horadesalida ) ) AS Minutos
								FROM empleado, horario
								WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";					
					}
					else
					{
						if($fila['horaentrada']>=8 && $fila['minutoentrada'] >= 15)
						{
							//echo "No es especial y llego tarde.<br>";
							$query2="SELECT HOUR( TIMEDIFF( horadeentrada , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( horadeentrada, horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";	
						}
						else if($fila['horaentrada']>=8 && $fila['minutoentrada'] <= 15)
						{
							//echo "No es especial (normal). antes de las 8 o antes de las 8:15 <br>";
							$query2="SELECT HOUR( TIMEDIFF( '8:00' , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( '8:00' , horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";
						}	
						else if($fila['horaentrada']<=8)
						{
							//echo "No es especial. Llego antes de las 8 <br>";
							$query2="SELECT HOUR( TIMEDIFF( '8:00' , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( '8:00' , horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";						
						}
					}
					$rs2 = mysql_query($query2);
					$total=0;
					while($fila=mysql_fetch_assoc($rs2))
					{
						$horas = $fila['Horas'];
						$minutos = $fila['Minutos'];
					}
					
					if ($horas-Horas_Laborables <=-1)
					{
						$total=0;
					}
					else
					{
						if($horas-Horas_Laborables >=1 && $minutos>=30)
						{
							$total+=$horas-Horas_Laborables;
							$total+=1;
						}
						else
						{
							if($horas-Horas_Laborables <=0)
							{
								$total=0;
							}
							else
							{
								$total+=$horas-Horas_Laborables;
							}
							if($minutos >=30)
							{
								$total+=1;				
							}
						}				
					}
					return $total;
				}
			}
			//divivir entre 0.5 para que se calcule cada 30 minutos, para minutos 0.(minutos) /0.5 si es menor de 1 no se calcula
		}
		
		function obtenerHoras($id, $idH, $feriado=false)
		{
			$query="SELECT  HOUR(horadeentrada) as horaentrada, MINUTE(horadeentrada) as minutoentrada,
							HOUR(horadesalida) as horasalida, MINUTE(horadesalida) as minutosalida
					FROM empleado, horario
					WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
			$rs = mysql_query($query);
			if($rs)
			{
				if(mysql_num_rows($rs) >0)
				{
					$fila = mysql_fetch_assoc($rs);
					if($this->empleadoEspecial($id) or $feriado==true)
					{
						//echo "Es especial <br>";
						$query2="SELECT HOUR( TIMEDIFF( horadeentrada , horadesalida ) ) AS Horas,
										MINUTE( TIMEDIFF( horadeentrada, horadesalida ) ) AS Minutos
								FROM empleado, horario
								WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";					
					}
					else
					{
						if($fila['horaentrada']>=8 && $fila['minutoentrada'] >= 15)
						{
							//echo "No es especial y llego tarde.<br>";
							$query2="SELECT HOUR( TIMEDIFF( horadeentrada , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( horadeentrada, horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";	
						}
						else if($fila['horaentrada']>=8 && $fila['minutoentrada'] <= 15)
						{
							//echo "No es especial (normal). antes de las 8 o antes de las 8:15 <br>";
							$query2="SELECT HOUR( TIMEDIFF( '8:00' , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( '8:00' , horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
						}	
						else if($fila['horaentrada']<=8)
						{
							//echo "No es especial. Llego antes de las 8 <br>";
							$query2="SELECT HOUR( TIMEDIFF( '8:00' , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( '8:00' , horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";						
						}
					}
					$rs2 = mysql_query($query2);
					$total=0;
					while($fila=mysql_fetch_assoc($rs2))
					{
						return $fila['Horas'];
					}
				}
			}
		}
		
		function obtenerMinutos($id, $idH, $feriado=false)
		{
			$query="SELECT  HOUR(horadeentrada) as horaentrada, MINUTE(horadeentrada) as minutoentrada,
							HOUR(horadesalida) as horasalida, MINUTE(horadesalida) as minutosalida
					FROM empleado, horario
					WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
			$rs = mysql_query($query);
			if($rs)
			{
				if(mysql_num_rows($rs) >0)
				{
					$fila = mysql_fetch_assoc($rs);
					
					if($this->empleadoEspecial($id) or feriado==true)
					{
						//echo "Es especial <br>";
						$query2="SELECT HOUR( TIMEDIFF( horadeentrada , horadesalida ) ) AS Horas,
										MINUTE( TIMEDIFF( horadeentrada, horadesalida ) ) AS Minutos
								FROM empleado, horario
								WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";					
					}
					else
					{
						if($fila['horaentrada']>=8 && $fila['minutoentrada'] >= 15)
						{
							//echo "No es especial y llego tarde.<br>";
							$query2="SELECT HOUR( TIMEDIFF( horadeentrada , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( horadeentrada, horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";	
						}
						else if($fila['horaentrada']>=8 && $fila['minutoentrada'] <= 15)
						{
							//echo "No es especial (normal). antes de las 8 o antes de las 8:15 <br>";
							$query2="SELECT HOUR( TIMEDIFF( '8:00' , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( '8:00' , horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
						}	
						else if($fila['horaentrada']<=8)
						{
							//echo "No es especial. Llego antes de las 8 <br>";
							$query2="SELECT HOUR( TIMEDIFF( '8:00' , horadesalida ) ) AS Horas,
											MINUTE( TIMEDIFF( '8:00' , horadesalida ) ) AS Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";						
						}
					}
					$rs2 = mysql_query($query2);
					$total=0;
					while($fila=mysql_fetch_assoc($rs2))
					{
						return $fila['Minutos'];
					}
				}
			}
		}
		//obtiene el nombre de un empleado
		function obtenerNombre($id)
		{
			$query ="SELECT nombre
			FROM empleado
			WHERE id={$id}";
			$rs = mysql_query($query);
			if($rs)
			{
				while($fila=mysql_fetch_assoc($rs))
				{
					return $fila['nombre'];
				}
			}	
		}		
		function guardarSeleccionados($arrayid, $arhorario, $fecha,$she='')
		{
			$i =0;$feriado;
			foreach($arrayid as $ids)
			{
				$query="SELECT empleado.id as id, nombre, cedula, cargo, fecha, DATE_FORMAT(horadeentrada, '%h:%i %p') as horadeentrada, 
						DATE_FORMAT(horadesalida, '%h:%i %p') as horadesalida, sueldo
						FROM empleado, horario
						WHERE empleado.id = {$ids} and empleado.id = horario.id_empleado and horario.id = {$arhorario[$i]}";
				$rs = mysql_query($query);
				if($rs)
				{
					if(mysql_num_rows($rs) > 0)
					{
						$c = new Calculo();
						while($fila=mysql_fetch_assoc($rs))
						{
							$porciento = $c->calcularPorcientoSueldo($fila['sueldo']);
							$tiempoExtra = $this->obtenerTiempoExtraF($fila['id'],$arhorario[$i]);
							$feriado = $this->esFeriado($fecha);
							if($feriado==true)
							{
								$pago = round($c->calcularHoraExtraFeriada($this->obtenerCantidadHoras($fila['id'],$arhorario[$i]),$fila['sueldo']),2);	
							}
							else
							{
								$pago = round($c->calcularHoraExtraNormal($this->obtenerCantidadHoras($fila['id'],$arhorario[$i]),$fila['sueldo']),2);	
							}
							$query="SELECT horario.id AS id
									FROM horario, empleado
									WHERE empleado.id = horario.id_empleado AND horario.fecha = '{$fila['fecha']}' AND empleado.id = {$ids}";
							$rsH = mysql_query($query);
							
							if($rsH)
							{
								while($filaH=mysql_fetch_assoc($rsH))
								{
									$queryDupl="SELECT * FROM historial_empleado
												WHERE id_empleado = {$ids} AND id_horario = {$filaH['id']} AND tiempo_extra = '{$tiempoExtra}' AND id_SHE={$she}";
									$rsD=mysql_query($queryDupl);
									if(mysql_num_rows($rsD) == 0)
									{
										$queryI = "INSERT INTO historial_empleado 
										(id_empleado, id_horario, tiempo_extra, pago, feriado, id_SHE)
										VALUES
										({$ids}, {$filaH['id']}, '{$tiempoExtra}', {$pago}, '{$feriado}', {$she})";
										mysql_query($queryI);
									}
								}						
							}
						}
					}
				}
				$i++;
			}
		}
		
		function validarPago($idE, $idHorario)
		{
			$queryFecha="SELECT fecha, MONTHNAME(fecha)
						FROM horario
						WHERE id={$idHorario}";
			$rsF=mysql_query($queryFecha);
			if($rsF)
			{
				$fila = mysql_fetch_assoc($rsF);
				$query="SELECT sum(pago) AS total, sueldo, fecha
						FROM historial_empleado, horario, empleado
						WHERE empleado.id = {$idE}
						AND empleado.id = historial_empleado.id_empleado
						AND horario.id = historial_empleado.id_horario
						AND MONTH(horario.fecha) =MONTH('{$fila['fecha']}')";
				$rs = mysql_query($query);
				if($rs)
				{
					if(mysql_num_rows($rs)>0)
					{	
						$fila=mysql_fetch_assoc($rs);
						$porciento = $fila['sueldo'];
						$porciento = $porciento *0.30;
						if($porciento >= $fila['total'])
						{
							return true;
						}
						else
						{
							return false;
						}
					}
				}				
			}
		}
		
		function empleadoEspecial($id)
		{
			$query="SELECT * FROM empleado
					WHERE id={$id} and horario_especial = true";
			$rs = mysql_query($query);
			if($rs)
			{
				if(mysql_num_rows($rs) > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		static function obtenerEmpleadosDisponibles($idE, $mes)
		{
			$query="SELECT ROUND( SUM( pago ) , 2 ) AS pago, Nombre, sueldo *0.30 as porciento
					FROM empleado, horario, historial_empleado
					WHERE empleado.id = horario.id_empleado
					AND historial_empleado.id_empleado = empleado.id
					AND horario.id = historial_empleado.id_horario
					AND empleado.id ={$idE}
					AND MONTH( horario.fecha ) ={$mes}";
			$rs = mysql_query($query);
			if($rs)
			{
				if(mysql_num_rows($rs) >0)
				{
					$fila = mysql_fetch_assoc($rs);
					if($fila['pago'] >= $fila['porciento'])
					{
						return false;
					}
					else
					{
						return true;
					}
				}
			}
		}
		
		function esFeriado($fecha)
		{
			$query="SELECT * FROM dias_feriados
					WHERE fecha = '{$fecha}'";
			$rs = mysql_query($query);
			if($rs)
			{
				if(mysql_num_rows($rs) >0 or $this->esFinDeSemana($fecha)==1)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		function esFinDeSemana($date)
		{
			return (date('N', strtotime($date)) >= 6);
		}
	}
?>