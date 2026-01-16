<?php 
include('Calculo.php');
set_time_limit(0);
if (!defined('Hora_Entrada')) define('Hora_Entrada', 8);
if (!defined('Minuto_Entrada')) define('Minuto_Entrada', 15);
if (!defined('Hora_Salida')) define('HoraSalida', 4);	
if(!isset($_SESSION)){
	session_start();
}
class Manejador
{		
	function obtenerIncludes($url)
	{
		$includes="";
		if(strstr($url,"reportes/") || strstr($url,"mantenimientos/") || strstr($url,"reloj/"))
		{
			$rutaBootstrapCss="../css/bootstrap.min.css";
			$rutaCssGeneric="../css/estilo.css";
			$rutaBootstrapJs="../js/bootstrap.min.js";
			$rutaJqueryJs="../js/jquery.js";
			$rutaJqueryMask="../js/jquery.mask.js";
		}
		else
		{
			$rutaBootstrapCss="css/bootstrap.min.css";
			$rutaCssGeneric="css/estilo.css";
			$rutaBootstrapJs="js/bootstrap.min.js";
			$rutaJqueryJs="js/jquery.js";
			$rutaJqueryMask="js/jquery.mask.js";
		}	
		$includes = "
			<link rel='stylesheet' type='text/css' href='{$rutaBootstrapCss}'>
			<link rel='stylesheet' type='text/css' href='{$rutaCssGeneric}'>
			<script src='{$rutaJqueryJs}'></script>
			<script src='{$rutaBootstrapJs}'></script>
			<script src='{$rutaJqueryMask}'></script> ";
		return $includes;
	}

	function obtenerIncludesJs($url)
	{
		$includes="";
		if(strstr($url,"reportes/") or strstr($url,"mantenimientos/"))
		{
			$rutaBootstrapJs="../js/bootstrap.min.js";
			$rutaJqueryJs="../js/jquery.js";
			$rutaJqueryMask="../js/jquery.mask.js";
		}
		else
		{
			$rutaBootstrapJs="js/bootstrap.min.js";
			$rutaJqueryJs="js/jquery.js";
			$rutaJqueryMask="js/jquery.mask.js";
		}	
		$includes = "
			<script src='{$rutaJqueryJs}'></script>
			<script src='{$rutaBootstrapJs}'></script>
			<script src='{$rutaJqueryMask}'></script> ";
		return $includes;
	}

	function obtenerIncludesCss($url)
	{
		$includes="";
		if(strstr($url,"reportes/") or strstr($url,"mantenimientos/"))
		{
			$rutaBootstrapCss="../css/bootstrap.min.css";
			$rutaCssGeneric="../css/estilo.css";

		}
		else
		{
			$rutaBootstrapCss="css/bootstrap.min.css";
			$rutaCssGeneric="css/estilo.css";
		}	
		$includes = "
			<link rel='stylesheet' type='text/css' href='{$rutaBootstrapCss}'>
			<link rel='stylesheet' type='text/css' href='{$rutaCssGeneric}'>";
		return $includes;
	}

	static function crearBreadCrumbs($ruta)
	{
		$bc="";
		if (strpos($ruta, '>') !== false) {
			$temp = explode(">", $ruta);
			foreach ($temp as $value) {
				if(end($temp) == $value){
				    $bc.= "<li><span>{$value}</span></li>";
				}
				else
				{
					$bc.= "<li><a href='#'>{$value}</a></li>";
				}	
			}
		}
		else
		{
			$bc.= "<li><a class='active'>{$ruta}</a></li>";
		}

		return $bc;
	}

	static function obtenerUsuariosSlc($asistente)
	{
		$query="SELECT a.id, a.usuario
				FROM [horasextra].[dbo].[usuario] a
				inner join empleado b on a.empleado =  b.id
				inner join t_departamento c on b.departamento = c.id
				where b.departamento in (
					select b.id from  usuario c
					inner join empleado a on c.empleado = a.id
					inner join t_departamento b on a.departamento = b.id or a.departamento = b.subDepId 
					where c.id = {$asistente})";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
				echo "<option value={$fila['id']}>{$fila['usuario']}</option>";
			}
		}
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
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$sql, $params, $options);
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
		
	static function obtenerDepartamentos($preferido=0)
	{
		$query="SELECT DISTINCT t_departamento.nombre as departamento, t_departamento.id as id
				FROM t_departamento
				order by departamento";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(!$rs)
		{
			echo "No se pudieron cargar los departamentos. ";
		}
		else
		{
			echo "<option value='todos'>Todos</option>";
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
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
	//obtiene la informacion de los empleados de una solicitud
    function obtenerSeleccionados($arrayid,$arhorario, $fecha)
	{
		$i =0;$feriado;
		$noCumple=false;
		$datos = Array();
		$msjNoCumple="";
		foreach($arrayid as $ids)
		{
			$query="SELECT empleado.id as id, empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, fecha as fecha, convert(varchar,horadeentrada, 108) as horadeentrada, 
					convert(varchar, horadesalida, 108) as horadesalida, sueldo
					FROM empleado, horario, t_departamento, t_cargo
					WHERE empleado.id = {$ids} and empleado.id = horario.id_empleado and horario.id ={$arhorario[$i]}
					AND t_cargo.id = empleado.cargo
					AND t_departamento.id = empleado.departamento";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				if(sqlsrv_num_rows($rs) > 0)
				{
					$c = new Calculo();
					while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
					{
						$porciento = $c->calcularPorcientoSueldo($fila['sueldo']);
						$feriado =$this->esFeriado($fecha);
						if($feriado==true)
						{
							$tiempoExtra = $this->obtenerTiempoExtraF($fila['id'],$arhorario[$i], true);
							$pago = round($c->calcularHoraExtraFeriada($this->obtenerCantidadHoras($fila['id'], $arhorario[$i],true),$fila['sueldo'] ),2);	
						}
						else
						{
							$tiempoExtra = $this->obtenerTiempoExtraF($fila['id'],$arhorario[$i]);
							$pago = round($c->calcularHoraExtraNormal($this->obtenerCantidadHoras($fila['id'], $arhorario[$i]),$fila['sueldo']),2);	
						}
						if (Manejador::validarPago($fila['id'],$_SESSION['fechasolicitud']))
						{
							$noCumple = true;
							$msjNoCumple=" +30% ";
						}
						/*
						else if ($pago + Manejador::obtenerAcumuladoHE($fila['id'],$_SESSION['fechasolicitud']) > $porciento)
						{
							$noCumple = true;
							$msjNoCumple=" pago + acumulado > 30% ";
						}
						*/
						if(strcmp($fila['horadeentrada'], "00:00:00")==0)
						{
							$fila['horadeentrada']='-----';
						}
						elseif (strcmp($fila['horadesalida'], "00:00:00")==0)
						{
							$fila['horadesalida']='-----';
						}

						if ($tiempoExtra < "00:30:00") {
							$noCumple=true;
							$msjNoCumple=" < 30 mins ";
						}

						echo "<tr class='tab_bg_2'>";
						if ($noCumple) {
							echo "<td align='center'><p>{$msjNoCumple}</p></input></td>";
						}
						else
						{
							$datos[$fila['id']] = $pago;
							echo "<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$arhorario[$i]}' readonly></input></td>";
						}
						echo "
								<td><input id='txt' style='width: 100%;' type='text' name='txtNombre' value='{$fila['nombre']}' readonly></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtCedula' value='{$fila['cargo']}' readonly></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtCargo' value='{$fila['cedula']}' readonly></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtFecha' value='{$fila['fecha']->format('d/m/Y')}' readonly></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtHoraEntrada' value='{$fila['horadeentrada']}' readonly></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtHoraSalida' value='{$fila['horadesalida']}' readonly></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtTiempoExtra' value='{$tiempoExtra}' readonly ></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtPorciento' value='{$porciento} RD$' readonly></input></td>
								<td><input id='txt' style='width: 100%;' type='text' name='txtPago' value='{$pago} RD$' readonly></input></td>
						 </tr>";
						$noCumple=false;
					}
				}
			}
			$i++;
		}
		$_SESSION['pagoemp'] = $datos;
	}
	function obtenerTiempoExtraF($id, $horario,$feriado=false)
	{
		$horas;$minutos;
		$horastr='';$minutostr='';
		if($feriado)
		{
				$horas=$this->obtenerHoras($id,$horario, $feriado);
				$minutos=$this->obtenerMinutos($id, $horario, $feriado);

				if ($horas <=9) {
					$horastr= "0".$horas;
				}
				else
				{
					$horastr= $horas;
				}
				if ($minutos <=9) {
					$minutostr= "0".$minutos;
				}
				else
				{
					$minutostr=$minutos;
				}

				$tiempoTotal=$horastr.":".$minutostr.":00";
		}
		else
		{
			if($this->obtenerHoras($id,$horario, $feriado) ==0 && $this->obtenerMinutos($id, $horario, $feriado) ==0)
			{
				$tiempoTotal = '------';
			}
			else
			{
				if($this->empleadoEspecial($id) && $feriado)
				{
					$horas=$this->obtenerHoras($id,$horario, $feriado);
					$minutos=$this->obtenerMinutos($id, $horario, $feriado);
				}
				else
				{
					$horas=$this->obtenerHoras($id,$horario, $feriado)-8;
					$minutos=$this->obtenerMinutos($id, $horario, $feriado);					
				}

				if ($horas<0) {
					$horastr="00";
					$minutostr="00";
				}
				else
				{
					if ($horas <=9) 
					{
						$horastr= "0".$horas;
					}
					else
					{
						$horastr= $horas;
					}

					if ($minutos <=9) {
						$minutostr= "0".$minutos;
					}
					else
					{
						$minutostr=$minutos;
					}					
				}
				$tiempoTotal=$horastr.":".$minutostr.":00";
			}				
		}
		return $tiempoTotal;
	}

	function obtenerCantidadHoras($id, $horario, $feriado=false)
	{
		$query="SELECT  DATEPART(HOUR,horadeentrada) as horaentrada, DATEPART(MINUTE,horadeentrada) as minutoentrada,
						DATEPART(HOUR,horadesalida) as horasalida, DATEPART(MINUTE,horadesalida) as minutosalida
				FROM empleado, horario
				WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) >0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if((strcmp($fila['horaentrada'], $fila['horasalida']) ==0 && strcmp($fila['minutoentrada'], $fila['minutosalida'])==0) || $fila['horasalida'] < $fila['horaentrada'])
				{
					$total=0;
				}
				else
				{
					if($this->empleadoEspecial($id) || $feriado==true)
					{
						//echo "Es especial <br>";
						$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Horas,
								datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Minutos
								FROM empleado, horario
								WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";					
					}
					else
					{
						if($fila['horaentrada']>=8 && $fila['minutoentrada'] >= 15)
						{
							//echo "No es especial y llego tarde.<br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";	
						}
						else if($fila['horaentrada']>=8 && $fila['minutoentrada'] <= 15)
						{
							//echo "No es especial (normal). antes de las 8 o antes de las 8:15 <br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";
						}	
						else if($fila['horaentrada']<=8)
						{
							//echo "No es especial. Llego antes de las 8 <br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$horario}";						
						}
					}
					$rs2 = sqlsrv_query($_SESSION['con'],$query2, $params, $options);
					$total=0;$termino=false;
					while($fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC))
					{
						$horas = $fila['Horas'];
						$minutos = $fila['Minutos'];
					}
					if($feriado)
					{
						$total += $horas;
						if($minutos >=30)
						{
							$total+=0.5;				
						}
					}
					else
					{
						if ($horas-Horas_Laborables <=-1)
						{
							$total=0;
						}
						else
						{
							if($horas-Horas_Laborables >=1 && $minutos>=30)
							{
								$total+=$horas-Horas_Laborables;
								$total+=0.5;
								$termino = true;
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
							}	
							if(!$termino)
							{
								if($minutos >=30)
								{
									$total+=0.5;				
								}								
							}
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
		$horas=0;
		$query="SELECT datepart(hh,CONVERT(time, horadeentrada)) as horaentrada,
				datepart(mi,CONVERT(time, horadeentrada)) as minutoentrada, 
				datepart(hh,CONVERT(time, horadesalida)) as horasalida,
				datepart(mi,CONVERT(time, horadesalida)) as minutosalida
				FROM empleado, horario
				WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) >0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if((strcmp($fila['horaentrada'], $fila['horasalida']) ==0 && strcmp($fila['minutoentrada'], $fila['minutosalida'])==0) || $fila['horasalida'] < $fila['horaentrada'])
				{
					$horas=0;
				}
				else
				{
					if($this->empleadoEspecial($id) || $feriado==true)
					{
						//echo "Es especial <br>";
						$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Horas,
								datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Minutos
								FROM empleado, horario
								WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";					
					}
					else
					{
						if($fila['horaentrada']>=8 && $fila['minutoentrada'] >= 15)
						{
							//echo "No es especial y llego tarde.<br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";	
						}
						else if($fila['horaentrada']>=8 && $fila['minutoentrada'] <= 15)
						{
							//echo "No es especial (normal). antes de las 8 o antes de las 8:15 <br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
						}	
						else if($fila['horaentrada']<=8)
						{
							//echo "No es especial. Llego antes de las 8 <br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";						
						}
					}
					$rs2 = sqlsrv_query($_SESSION['con'],$query2, $params, $options);
					$total=0;
					while($fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC))
					{
						$horas= $fila['Horas'];
					}					
				}
			}
		}
		return $horas;
	}
	
	function obtenerMinutos($id, $idH, $feriado=false)
	{
		$minutos=0;
		$query="SELECT datepart(hh,CONVERT(time, horadeentrada)) as horaentrada,
				datepart(mi,CONVERT(time, horadeentrada)) as minutoentrada, 
				datepart(hh,CONVERT(time, horadesalida)) as horasalida,
				datepart(mi,CONVERT(time, horadesalida)) as minutosalida
				FROM empleado, horario
				WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) >0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if((strcmp($fila['horaentrada'], $fila['horasalida']) ==0 && strcmp($fila['minutoentrada'], $fila['minutosalida'])==0) || $fila['horasalida'] < $fila['horaentrada'])
				{
					$minutos=0;
				}
				else
				{
					if($this->empleadoEspecial($id) || $feriado==true)
					{
						//echo "Es especial <br>";
						$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Horas,
								datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Minutos
								FROM empleado, horario
								WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";					
					}
					else
					{
						if($fila['horaentrada']>=8 && $fila['minutoentrada'] >= 15)
						{
							//echo "No es especial y llego tarde.<br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,horadeentrada, horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";	
						}
						else if($fila['horaentrada']>=8 && $fila['minutoentrada'] <= 15)
						{
							//echo "No es especial (normal). antes de las 8 o antes de las 8:15 <br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";
						}	
						else if($fila['horaentrada']<=8)
						{
							//echo "No es especial. Llego antes de las 8 <br>";
							$query2="SELECT datepart(hh,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Horas,
									datepart(mi,CONVERT(time, DATEADD(SECOND, DATEDIFF(SECOND,'08:00:00', horadesalida),0), 108)) as Minutos
									FROM empleado, horario
									WHERE empleado.id={$id} and horario.id_empleado = empleado.id and horario.id ={$idH}";						
						}
					}
					$rs2 =sqlsrv_query($_SESSION['con'],$query2, $params, $options);
					while($fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC))
					{
						$minutos= $fila['Minutos'];
					}					
				}
			}
		}
		return $minutos;
	}
	//obtiene el nombre de un empleado
	function obtenerNombre($id)
	{
		$query ="SELECT nombre
		FROM empleado
		WHERE id={$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				return $fila['nombre'];
			}
		}	
	}

	function guardarSeleccionados($arrayid, $arhorario, $fecha,$she='')
	{
		$i =0;$feriado;
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		foreach($arrayid as $ids)
		{
			$query="SELECT empleado.id as id, nombre, cedula, cargo, fecha, horadeentrada as horadeentrada, 
					horadesalida as horadesalida, sueldo
					FROM empleado, horario
					WHERE empleado.id = {$ids} and empleado.id = horario.id_empleado and horario.id = {$arhorario[$i]}";
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				if(sqlsrv_num_rows($rs) > 0)
				{
					$c = new Calculo();
					while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
					{
						$porciento = $c->calcularPorcientoSueldo($fila['sueldo']);
						
						$feriado = $this->esFeriado($fecha);
						if($feriado==true)
						{
							$tiempoExtra = $this->obtenerTiempoExtraF($fila['id'],$arhorario[$i], true);
							$pago = round($c->calcularHoraExtraFeriada($this->obtenerCantidadHoras($fila['id'],$arhorario[$i], true),$fila['sueldo']),2);
							$feriado=1;	
						}
						else
						{
							$tiempoExtra = $this->obtenerTiempoExtraF($fila['id'],$arhorario[$i]);
							$pago = round($c->calcularHoraExtraNormal($this->obtenerCantidadHoras($fila['id'],$arhorario[$i]),$fila['sueldo']),2);	
							$feriado=0;
						}
						if ($pago > $porciento) {
							// lo que complete el 30%
							$pago = Manejador::obtenerAcumuladoHE($fila['id'],$_SESSION['fechasolicitud']) - $porciento;
						}

						if ($pago ==0) {
							# code...
						}
						else
						{
							$query="SELECT horario.id AS id
									FROM horario, empleado
									WHERE empleado.id = horario.id_empleado AND horario.fecha = '{$fila['fecha']->format('Y-m-d')}' AND empleado.id = {$ids}";
							$rsH = sqlsrv_query($_SESSION['con'],$query,$params, $options);
							if($rsH)
							{
								while($filaH=sqlsrv_fetch_array($rsH, SQLSRV_FETCH_ASSOC))
								{
									$queryDupl="SELECT * FROM historial_empleado
												WHERE id_empleado = {$ids} AND id_horario = {$filaH['id']} AND tiempo_extra = '{$tiempoExtra}' AND id_SHE={$she}";
									$rsD=sqlsrv_query($_SESSION['con'],$queryDupl, $params, $options);

									if(sqlsrv_num_rows($rsD) == 0)
									{
										$queryI = "INSERT INTO historial_empleado 
										(id_empleado, id_horario, tiempo_extra, pago, feriado, id_SHE)
										VALUES
										({$ids}, {$filaH['id']}, '{$tiempoExtra}', {$pago}, {$feriado}, {$she})";
										//if($feriado){echo "true";};
										$stmt=sqlsrv_query($_SESSION['con'],$queryI);
									}
									//APLICAR AQUI EL UPDATE DE HISTORIAL
								}						
							}						
						}
					}
				}
			}
			$i++;
		}
	}
	
	static function validarPago($idE, $fecha)
	{
		$sobrepaso = false;
		$month = date("m",strtotime($fecha));
		$year = date("y",strtotime($fecha));
		$query="SELECT * from (select a.id, a.nombre , cedula, e.nombre as departamento,
				ROUND( SUM( pago ) , 2 )  pago, sueldo * 0.30  porciento  
				from empleado a
				inner join historial_empleado c on a.id = c.id_empleado
				inner join horario b on a.id = b.id_empleado and b.id = c.id_horario 
				AND MONTH( b.fecha ) = '{$month}' AND YEAR(b.fecha) = '{$year}'
				AND a.id = {$idE}
				inner join t_departamento e on e.id = a.departamento 
				GROUP BY a.cedula,a.id,a.nombre,e.nombre, a.sueldo) x
				where pago > porciento
				order by x.nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)	
			{
				$sobrepaso = true;
			}		
		}
		return $sobrepaso;
	}
	
	function empleadoEspecial($id)
	{
		$query="SELECT * FROM empleado
				WHERE id={$id} and horario_especial = 1";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	static function validarSolicitado($idE, $fecha,$return=0)
	{
		$flag = false;
		$usr="";
		$query="SELECT a.nombre, b.id, b.usr, b.fecha from empleado a
				inner join solicitudes c on c.id_empleado = a.id
				inner join solicitudhe b on b.id = c.id_solicitud
				inner join solicitudes_autorizadas d on d.id_solicitud = b.id 
				where a.id={$idE} AND b.fecha = '{$fecha}' and d.autorizado =1";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)	
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				$usr=$fila['usr'];
				$flag = true;
			}		
		}
		if ($return==1) {
			return $usr;
		}
		else
		{
			return $flag;
		}
	}

	static function obtenerFechadeFormulario($f)
	{
		$fecha='';
		$query="SELECT fecha from solicitudhe where id={$f}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if (sqlsrv_num_rows($rs)>0) {
			$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$fecha=$fila['fecha'];
		}
		return $fecha;
	}
	
	static function obtenerEmpleadosDisponibles($id_secretaria,$fecha, $idF)
	{
		$query="select a.id, a.nombre, a.cedula, b.nombre departamento from empleado a
				inner join t_departamento b on a.departamento = b.id
				inner join grupo_empleados g on g.id_empleado = a.id
				WHERE g.id_secretaria={$id_secretaria}
				order by a.nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);

		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$f = Manejador::obtenerFechadeFormulario($idF);
			$usr = Manejador::validarSolicitado($fila['id'], $f->format('Y-m-d'),1);
			if (Manejador::validarPago($fila['id'], $fecha)) 
			{
				echo "	<tr class='tab_bg_1'>
							<td> 
								<input type='checkbox' name='chkEmpleados[]' value='{$fila['id']}' disabled> <p style='color:red;font-weight: 100;'>{$fila['nombre']} - 
								{$fila['cedula']} - {$fila['departamento']}</p>
							</td>
						</tr>
					";						
			}
			else if(!empty($usr))
			{
				$tmp=explode("@", $usr);
				echo "	<tr class='tab_bg_1'>
							<td> 
								<input type='checkbox' name='chkEmpleados[]' value='{$fila['id']}' disabled> <p style='color:red;font-weight: 100;'>{$fila['nombre']} - 
								{$fila['cedula']} - {$fila['departamento']} <b>SOLICITADO {$tmp[0]} </b></p>
							</td>
						</tr>
					";	
			}
			else
			{
				echo "	<tr class='tab_bg_1'>
								<td> 
									<input type='checkbox' name='chkEmpleados[]' value='{$fila['id']}'> <b>{$fila['nombre']}</b> - 
									{$fila['cedula']} - {$fila['departamento']}
								</td>
							</tr>
						";
			}
		}
	}
	
	function esFeriado($fecha)
	{
		$f=false;
		$query="SELECT * FROM dias_feriados
				WHERE fecha = '{$fecha}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) >0 || $this->esFinDeSemana($fecha)==1)
			{
				$f= true;
			}
			else
			{
				$f= false;
			}
		}
		return $f;
	}

	function esFeriadoInt($fecha)
	{
		$f=0;
		$query="SELECT * FROM dias_feriados
				WHERE fecha = '{$fecha}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) >0 || $this->esFinDeSemana($fecha)==1)
			{
				$f= 1;
			}
			else
			{
				$f= 0;
			}
		}
		return $f;
	}
	
	function esFinDeSemana($date) {
		return (date('N', strtotime($date)) >= 6);
	}
	
	static function obtenerNombreUsuario($usuario)
	{
		$nu="-----";
		if(!empty($usuario))
		{
			$nu = explode("@",$usuario);
			$nu = $nu[0];
		}
		return $nu;
		
	}

	static function obtenerEmpTbl()
	{
		$query="SELECT a.id, a.nombre, a.cedula, c.nombre as cargo
				FROM empleado a
				inner join t_cargo c on c.id = a.cargo
				ORDER By nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if (sqlsrv_num_rows($rs)>0)  {
			while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
				echo "	<tr class='tab_bg_1'>
							<td> 
								<input type='checkbox' name='chkEmpleados[]' value='{$fila['id']}' <b>{$fila['nombre']}</b> - 
								{$fila['cedula']} - {$fila['cargo']}
							</td>
						</tr>
					";
			}
		}
	}

	static function obtenerEncargado($departamento)
	{
		$n='';
		$query="SELECT nombre as encargado 
				FROM empleado
				WHERE departamento={$departamento}
				AND nivel =2";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if(sqlsrv_num_rows($rs) > 0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$n= $fila['encargado'];
			}
		}	
		return $n;
	}

	static function obtenerNombreCompleto($idU)
	{
		$n='';
		$query="SELECT empleado.nombre 
				FROM empleado, usuario
				WHERE empleado.id = usuario.empleado
				AND usuario.id={$idU}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if(sqlsrv_num_rows($rs) > 0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$n= $fila['nombre'];
			}
		}	
		return $n;
	}

	static function obtenerIdDpto($departamento)
	{
		$n=0;
		$query="SELECT id
				FROM t_departamento
				WHERE nombre ='{$departamento}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if(sqlsrv_num_rows($rs)>0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$n= $fila['id'];
			}
		}	
		return $n;
	}

	static function obtenerCargo($idU)
	{
		$n='';
		$query="SELECT t_cargo.nombre as cargo
				FROM empleado, usuario, t_cargo
				WHERE empleado.id = usuario.empleado
				AND t_cargo.id = empleado.cargo
				AND usuario.id={$idU}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if(sqlsrv_num_rows($rs)>0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$n= $fila['cargo'];
			}
		}	
		return $n;
	}

	static function obtenerDepartamentoN($idU)
	{
		$n='';
		$query="SELECT nombre as depto
				FROM t_departamento
				WHERE id = {$idU}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if(sqlsrv_num_rows($rs)>0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$n= $fila['depto'];
			}
		}	
		return $n;
	}

	static function obtenerIdE($cedula)
	{
		$id;
		$query="SELECT id from empleado where cedula='{$cedula}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if(sqlsrv_num_rows($rs)>0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$id= $fila['id'];
			}
		}	
		return $id;
	}

	static function obtenerAcumuladoHE($idE, $fecha)
	{
		$month = date("m",strtotime($fecha));
		$year = date("Y",strtotime($fecha));
		$query="SELECT ROUND( SUM( pago ) , 2 )  pago
				from empleado a
				inner join historial_empleado c on a.id = c.id_empleado
				inner join horario b on a.id = b.id_empleado and b.id = c.id_horario 
				inner join solicitudhe she on she.id = c.id_SHE
				inner join solicitudes s on s.id_solicitud = she.id and a.id = s.id_empleado
				inner join solicitudes_autorizadas sa on sa.id_solicitud = she.id AND MONTH( b.fecha ) = '{$month}' AND YEAR(b.fecha) = '{$year}' AND a.id = {$idE} and sa.autorizado=1";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)	
			{
				while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
					return $fila['pago'];
				}
			}		
		}
	}

	static function esMultidepartamental($idE, $fecha, $detalle=0)
	{
		$flag=false;
		$solicitantes;
		$retorno;
		$month = date("m",strtotime($fecha));
		$year = date("Y",strtotime($fecha));
		$query="SELECT a.nombre, she.usr, ROUND( SUM( pago ) , 2 )  pago
				from empleado a
				inner join historial_empleado c on a.id = c.id_empleado
				inner join solicitudhe she on she.id = c.id_SHE
				inner join solicitudes s on s.id_solicitud = she.id and a.id = s.id_empleado
				inner join solicitudes_autorizadas sa on sa.id_solicitud = she.id 
				inner join horario b on a.id = b.id_empleado and b.id = c.id_horario 
				AND MONTH( b.fecha ) = '{$month}' AND YEAR(b.fecha) = '{$year}'
				AND a.id = {$idE}
				inner join t_departamento e on e.id = a.departamento
				where sa.autorizado=1
				group by she.usr, a.nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			$solicitantes = sqlsrv_num_rows($rs);
			if ($solicitantes) {
				if($solicitantes>=2)	
				{
					$flag=true;
				}	
			}	
		}

		if ($detalle==1) {
			$retorno=$solicitantes;
		}
		else
		{
			$retorno = $flag;
		}

		return $retorno;
	}

	static function obtenerPorciento($idE)
	{
		$query="SELECT sueldo *0.30  porciento  
				from empleado 
				WHERE id={$idE}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)	
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['porciento'];			
			}		
		}
	}

	static function obtenerHorario($desde, $hasta, $depto='', $codigo='')
	{
		if (!empty($codigo)) {
			$query="SELECT hor.id horario,emp.id, emp.nombre, emp.codigo_empleado, depto.nombre departamento, hor.fecha, convert(varchar,hor.horadeentrada,108) horadeentrada, convert(varchar,hor.horadesalida,108) horadesalida
					from empleado emp
					inner join t_departamento depto on depto.id = emp.departamento
					inner join horario hor on hor.id_empleado = emp.id
					where fecha between '{$desde}'  and '{$hasta}' and emp.codigo_empleado={$codigo}
					order by emp.nombre";
		}
		else if (strcmp($depto, 'todos')!=0)
		{
			$query="SELECT hor.id horario,emp.id, emp.nombre, emp.codigo_empleado, depto.nombre departamento, hor.fecha, convert(varchar,hor.horadeentrada,108) horadeentrada, convert(varchar,hor.horadesalida,108) horadesalida
					from empleado emp
					inner join t_departamento depto on depto.id = emp.departamento
					inner join horario hor on hor.id_empleado = emp.id
					where fecha between '{$desde}' and '{$hasta}' and depto.id = {$depto}
					order by emp.nombre";
		}
		else if (strcmp($depto, 'todos')==0)
		{
			$query="SELECT hor.id horario,emp.id, emp.nombre, emp.codigo_empleado, depto.nombre departamento, hor.fecha, convert(varchar,hor.horadeentrada,108) horadeentrada, convert(varchar,hor.horadesalida,108) horadesalida
					from empleado emp
					inner join t_departamento depto on depto.id = emp.departamento
					inner join horario hor on hor.id_empleado = emp.id
					where fecha between '{$desde}' and '{$hasta}'
					order by emp.nombre";
		}

		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			return $rs;
		}
		else
		{
			return sqlsrv_errors();
		}
	}

	static function cambiarHorario($idH, $horadeentrada, $horadesalida)
	{
		$flag=false;
		$anterior = Manejador::getHorario($idH);
		if (!empty($horadeentrada) && !empty($horadesalida)) 
		{
			$query="UPDATE horario set horadeentrada=convert(time,'{$horadeentrada}', 108), horadesalida=convert(time,'{$horadesalida}',108)
					WHERE id={$idH} ";
		}
		else if (!empty($horadeentrada) && empty($horasalida))
		{
			$query="UPDATE horario set horadeentrada=convert(time,'{$horadeentrada}', 108)
					WHERE id={$idH} ";
		}
		else if (empty($horaentrada) && !empty($horadesalida))
		{
			$query="UPDATE horario set horadesalida=convert(time,'{$horadesalida}',108)
					WHERE id={$idH} ";
		}
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			$flag=true;
			$file_path = $_SERVER['DOCUMENT_ROOT'].'/HorasExtra/archivos/log_omision_punch.log';
			$data = file_get_contents($file_path);
			$data.="\r\n". "Se actualizo:" . $idH."\r\n". 
					"Antes: " .$anterior."\r\n". 
					"Despues: Entrada: ".$horadeentrada.", Salida: ".$horadesalida."\r\n". 
					"A las: ".date('Y-m-d H:i:s')."\r\n".
					"------------------------------"."\r\n";
			$file_handle = fopen($file_path, 'w');
			fwrite($file_handle, $data);
			fclose($file_handle);
		}
		return $flag;
	}

	static function getHorario($idH)
	{
		$query="SELECT convert(varchar,horadeentrada, 108) horadeentrada, convert(varchar,horadesalida, 108) horadesalida
				from horario 
				WHERE id={$idH}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)	
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return "Entrada: ".$fila['horadeentrada']."; Salida: ".$fila['horadesalida'];			
			}		
		}
	}

	static function obtenerIdE2($codigo)
	{
		$id;
		$query="SELECT id from empleado where codigo_empleado={$codigo}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if(sqlsrv_num_rows($rs)>0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$id= $fila['id'];
			}
		}	
		return $id;
	}

	static function ingresarHorario($codigo, $fecha, $entrada, $salida)
	{
		$response=0;
		if (!empty($codigo)) {
			$id = Manejador::obtenerIdE2($codigo);
			$query = "SELECT * from horario where id_empleado = {$id} AND fecha= convert(date,'{$fecha}',120)";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if(sqlsrv_num_rows($rs)<=0)
			{
				$query2 = "INSERT INTO horario (id_empleado, fecha, horadeentrada, horadesalida) VALUES ({$id}, convert(date,'{$fecha}',120), convert(time,'{$entrada}', 108), convert(time,'{$salida}', 108))";
				sqlsrv_query($_SESSION['con'],$query2, $params, $options);
				$response=1; 
			}	

		}
		return $response;
	}

	static function obtenerNivelSlc($id)
	{
		$query="SELECT nivel FROM empleado where id = {$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			echo "<option value='{$fila['nivel']}' selected>{$fila['nivel']}</option>";
		}
		else
		{
			echo "<option value='0' selected>0</option>";
		}
	}
}
?>