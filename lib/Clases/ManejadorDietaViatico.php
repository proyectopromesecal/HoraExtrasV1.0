<?php 

class ManejadorDietaViatico
{
	static function obtenerNombre($id)
	{
		$query="SELECT no_oficio
				FROM dietaviatico
				WHERE id ={$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			if(sqlsrv_num_rows($rs)>0)
			{
				return $fila['no_oficio'];
			}
		}
	}
	
	static function obtenerRegiones($idD)
	{
		$query="SELECT nombre
				FROM region";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "<option value='{$fila['nombre']}'>{$fila['nombre']}</option>";
			}
		}
	}
	
	static function obtenerCentroSalud($idD)
	{
		$query="SELECT centro_salud.id as id, centro_salud.nombre as nombre
				FROM centro_salud, region
				WHERE region.id = {$idD}
				AND centro_salud.id_region = region.id";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "<option value='{$fila['id']}'>{$fila['nombre']}</option>";
			}
		}
	}
	
	static function obtenerFormulariosSlc()
	{
		$query="SELECT id, no_oficio
				FROM dietaviatico";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			echo "<option value=''> </option>";
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "<option value={$fila['id']}>{$fila['no_oficio']}</option>";
			}
		}
	}
	
	static function obtenerEmpleados($idS)
	{
		$query="SELECT empleado.id as id, empleado.nombre as nombre, t_cargo.nombre as cargo, t_departamento.nombre as departamento, cedula
				FROM empleado, dietaviatico, viatico_empleado, t_cargo, t_departamento
				WHERE empleado.id = viatico_empleado.id_empleado
				AND t_cargo.id = empleado.cargo
				AND t_departamento.id = empleado.departamento
				AND dietaviatico.id = viatico_empleado.id_formulario
				AND dietaviatico.id = {$idS}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		return $rs;
	}
	
	static function obtenerEmpleadosDpto($id,$departamento)
	{
		$sql=" SELECT empleado.id as id, empleado.nombre as nombre, t_cargo.nombre as cargo, t_departamento.nombre as departamento, cedula
			   FROM empleado, t_cargo, t_departamento
			   WHERE NOT EXISTS
			   (SELECT 1 FROM viatico_empleado WHERE id_formulario = {$id}
				AND id_empleado = empleado.id)
			   AND t_departamento.nombre = '{$departamento}' 
			   AND t_departamento.id = empleado.departamento
			   AND t_cargo.id = empleado.cargo
			   ORDER BY nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$sql, $params, $options);
		return $rs;
	}

	static function agregarEmpleados($idS,$arrayE)
	{
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		foreach($arrayE as $valor)
		{
			$queryD="SELECT *
					FROM viatico_empleado
					WHERE id_empleado = {$valor} AND id_formulario = {$idS}";

			$rsD = sqlsrv_query($_SESSION['con'],$queryD, $params, $options);
			
			if($rsD)
			{
				if(sqlsrv_num_rows($rsD)==0)
				{
					$query = "INSERT INTO viatico_empleado
					(id_empleado, id_formulario, total)
					VALUES
					({$valor},{$idS}, 0)";
					$rs = sqlsrv_query($_SESSION['con'],$query);				
				}
			}
		}
	}
	
	static function eliminarEmpleados($idS, $arrayE)
	{
		foreach($arrayE as $valor)
		{
			$query = "DELETE FROM viatico_empleado
					  WHERE id_formulario = {$idS} 
					  AND id_empleado ={$valor}";
			$rs = sqlsrv_query($_SESSION['con'],$query);
		}
	}
	
	static function obtenerBeneficiarios($dpto, $idS)
	{
		$idEmp = array();
		$query="SELECT empleado.id as id, cedula, empleado.nombre as nombre, t_cargo.nombre as cargo,posicion_viatico.posicion as concepto , total
				from empleado, dietaviatico, viatico_empleado, posicion_viatico, t_cargo
				where dietaviatico.departamento = '{$dpto}'
				AND empleado.id = viatico_empleado.id_empleado
				AND t_cargo.id = empleado.cargo
				AND dietaviatico.id = {$idS}
				AND tipo_viatico = posicion_viatico.id
				AND viatico_empleado.id_formulario = dietaviatico.id";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo"
					<tr class='tab_bg_1'>
						<td class='tab_bg_2'>{$fila['cedula']}</td>
						<td class='tab_bg_2'>{$fila['nombre']}</td>
						<td class='tab_bg_2'>{$fila['cargo']}</td>
						<td class='tab_bg_2'>{$fila['concepto']}</td>
						<td class='tab_bg_2'>{$fila['total']}</td>
					</tr>
				";
				$idEmp[] = $fila['id'];	
			}
			$_SESSION['bnfViaticos'] = $idEmp;
		}
	}

	static function calcularViatico($idF)
	{
		$arrFechaE = array();
		$arrFechaS = array();
		$arrHoraE = array();
		$arrHoraS = array();
		$arrLugar = array();
		$arrIDDestinos = array();
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	

		$queryCargar="SELECT * FROM destinos_viaticos WHERE id_viatico = {$idF}";
		$rsCargar = sqlsrv_query($_SESSION['con'],$queryCargar, $params, $options);
		while($filaC=sqlsrv_fetch_array($rsCargar, SQLSRV_FETCH_ASSOC))
		{
			$arrIDDestinos[] =$filaC['id'];
			$arrFechaE[]=$filaC['fecha_entrada'];
			$arrFechaS[]=$filaC['fecha_salida'];
			$arrHoraE[]=$filaC['hora_entrada'];
			$arrHoraS[]=$filaC['hora_salida'];
			$arrLugar[]=$filaC['lugar'];
		}			
		
		$query="SELECT empleado.id AS id, desayuno, almuerzo, cena, dormitorio
				FROM empleado, dietaviatico, viatico_empleado, posicion_viatico
				WHERE empleado.id = viatico_empleado.id_empleado
				AND dietaviatico.id = viatico_empleado.id_formulario
				AND dietaviatico.id ={$idF}
				AND empleado.id = viatico_empleado.id_empleado
				AND empleado.tipo_viatico = posicion_viatico.id";
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($filaEmp=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$tdes=0; $tal=0; $tcen=0; $tdor =0;
				$total =0;
				$dias;
				//echo "Empleado: ".$filaEmp['id'];
				for($f=0;$f<count($arrFechaE);$f++)
				{
					//echo "<br><br>"." Grupo de fecha: {$f}"."<br>";
					
					$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					
					$dias = (strtotime($arrFechaE[$f]->format('Y-m-d'))-strtotime($arrFechaS[$f]->format('Y-m-d')))/86400;
					$dias = abs($dias); $dias = floor($dias);					
					//echo "Dias ".$dias."<br>";
					for($x=0;$x<=$dias;$x++)
					{
						$horaE = explode(":", $arrHoraE[$f]->format('H:i:s'));
						$horaS = explode(":", $arrHoraS[$f]->format('H:i:s'));
						if($horaE[0] <= 7)
						{
							$desayuno = true;
							$tdes+=1;
						}
						if ($horaS[0] >= 12)
						{
							$almuerzo = true;
							$tal+=1;
						}
						if ($horaS[0]>=18)
						{
							$cena = true;
							$tcen+=1;
						}

						if($desayuno)
						{
							//echo "Desayuno: {$filaEmp['desayuno']}"."<br>";
							$total += $filaEmp['desayuno'];
						}
						if ($almuerzo)
						{
							//echo "Almuerzo: {$filaEmp['almuerzo']}"."<br>";
							$total += $filaEmp['almuerzo'];
						}
						if ($cena)
						{
							//echo "Cena: {$filaEmp['cena']}"."<br>";
							$total += $filaEmp['cena'];
						}
					}
					$dormitorio = $filaEmp['dormitorio'] * $dias;
					//echo "Dormitorio: {$dormitorio} ({$dias} dias)"."<br>";
					$total += $dormitorio;		
					$tdor= $dias;
						
					//$total = 0;
					$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
				}
				$query2="SELECT id
						 FROM viatico_empleado
						 WHERE id_empleado = {$filaEmp['id']}
						 AND id_formulario = {$idF}";
				$rs2 = sqlsrv_query($_SESSION['con'],$query2, $params, $options);
				if($rs2)
				{
					if(sqlsrv_num_rows($rs2) >0)
					{
						while($fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC))
						{
							$query3 ="  UPDATE viatico_empleado SET total ={$total} 
										WHERE id={$fila['id']}";
							sqlsrv_query($_SESSION['con'],$query3, $params, $options);							
						}
					}
				}
				
				//echo "Total: ".$total."<br>";
			}
		}
	}
	
	static function guardarDestinos($idS, $arrFechaE, $arrFechaS, $arrHoraE, $arrHoraS, $arrLugar)
	{
		$i =count($arrFechaE);
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		for($x=0;$x<$i;$x++)
		{
			$query="SELECT * 
					FROM destinos_viaticos
					WHERE id_viatico ={$idS}
					AND fecha_entrada = '{$arrFechaE[$x]}'
					AND fecha_salida = '{$arrFechaS[$x]}'
					AND hora_entrada = '{$arrHoraE[$x]}'
					AND hora_salida = '{$arrHoraS[$x]}'
					AND lugar = '{$arrLugar[$x]}'";
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				if(sqlsrv_num_rows($rs) == 0)
				{
					$query2="INSERT INTO destinos_viaticos
					(id_viatico, fecha_entrada, fecha_salida, hora_entrada, hora_salida, lugar)
					VALUES
					({$idS}, '{$arrFechaE[$x]}', '{$arrFechaS[$x]}', '{$arrHoraE[$x]}', '{$arrHoraS[$x]}', '{$arrLugar[$x]}' )";
					sqlsrv_query($_SESSION['con'],$query2, $params, $options);
				}
			}		
		}
	}
	
	static function eliminarDestino($id)
	{
		$query="DELETE FROM destinos_viaticos
				WHERE id={$id}";
		sqlsrv_query($_SESSION['con'],$query);
	}
	
	static function obtenerFormularios($dpto, $fechac, $fechas)
	{
		if(empty($departamento))
		{
			$query="SELECT dietaviatico.id as id, dietaviatico.no_oficio as no_oficio, dietaviatico.fecha_creacion as fecha_creacion
					FROM dietaviatico
					WHERE dietaviatico.usr = '{$_SESSION['usuario']}' ";
		}
		else
		{
			$query="SELECT dietaviatico.id as id, dietaviatico.no_oficio as no_oficio, dietaviatico.fecha_creacion as fecha_creacion
					FROM dietaviatico
					WHERE dietaviatico.usr = '{$_SESSION['usuario']}' ";
		}
		if (!empty($fechac))
		{
			$query.= " AND dietaviatico.fecha_creacion ='{$fechac}'";
		}
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		return $rs;
	}
	
	static function cantidadEmpleados($idS)
	{
		$query="SELECT count(id_empleado) as cantidad_empleados
				FROM empleado, viatico_empleado, dietaviatico
				WHERE empleado.id = viatico_empleado.id_empleado 
				AND dietaviatico.id = {$idS}
				AND dietaviatico.id = viatico_empleado.id_formulario";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) >0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['cantidad_empleados'];
			}
		}
	}
	
	static function generarNoOficio()
	{
		$nOficio="DYV".date('y')."-";
		$query="SELECT MAX( id ) AS id
				FROM dietaviatico";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$nOficio.=$fila['id'] +1;
			return $nOficio;
		}
		else
		{
			return null;
		}
	}
	
	static function obtenerPerfiles($perfil)
	{
		$query="SELECT id, posicion FROM posicion_viatico ORDER BY POSICION";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "<option value='{$fila['id']}'>{$fila['posicion']}</option>";
				if($perfil == $fila['id'])
				{
					echo "<option value='{$fila['id']}' selected>{$fila['posicion']}</option>";
				}						
			}
		}
	}

	static function obtenerBeneficiariosReporte($dpto, $idS)
	{
		$query="SELECT cedula, empleado.nombre as nombre, t_cargo.nombre as cargo,posicion_viatico.posicion as concepto , total
				from empleado, dietaviatico, viatico_empleado, posicion_viatico, t_cargo
				where dietaviatico.departamento = '{$dpto}'
				AND t_cargo.id = empleado.cargo
				AND empleado.id = viatico_empleado.id_empleado
				AND dietaviatico.id = {$idS}
				AND tipo_viatico = posicion_viatico.id
				AND viatico_empleado.id_formulario = dietaviatico.id";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			return $rs;
		}
	}
	
	static function solicitudesAprobadas($departamento, $fechac)
	{
		if(empty($departamento) || strcmp($departamento,"todos")==0)
		{
			$query="SELECT dietaviatico.id as id, no_oficio, dietaviatico.fecha_creacion as fecha_creacion
					FROM dietaviatico, solicitudes_autorizadas
					WHERE NOT EXISTS
					(SELECT 1 FROM viatico_pagado WHERE id_dietaviatico = dietaviatico.id)
					AND dietaviatico.id = solicitudes_autorizadas.id_solicitud
					AND tipo = 'Viatico'
					AND autorizado = 1";
		}
		else
		{
			$query="SELECT dietaviatico.id as id, no_oficio, dietaviatico.fecha_creacion as fecha_creacion
					FROM formulario_transporte, solicitudes_autorizadas
					WHERE NOT EXISTS
					(SELECT 1 FROM viatico_pagado WHERE id_dietaviatico = dietaviatico.id)
					AND dietaviatico.id = solicitudes_autorizadas.id_solicitud
					AND dietaviatico.departamento ='{$departamento}'
					AND tipo = 'Viatico'
					AND autorizado = 1";
		}
		if (!empty($fechac))
		{
			$query.= "AND dietaviatico.fecha_creacion ='{$fechac}'";
		}
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		return $rs;
	}
	
	static function pagarSolicitud($id)
	{
		$query="SELECT * FROM dietaviatico, viatico_pagado
				WHERE dietaviatico.id = viatico_pagado.id_dietaviatico
				AND dietaviatico.id = {$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				echo "<script>alert('Esta solicitud ya esta pagada');</script>";
			}
			else
			{
				$fecha = date("Y-m-d");
				$query2="INSERT INTO viatico_pagado
						(id_dietaviatico, fecha)
						VALUES
						({$id}, '{$fecha}')";
				$params = array();
				$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
				sqlsrv_query($_SESSION['con'],$query2, $params, $options);
			}
		}
	}
	
	static function verificarPagado($id)
	{
		$query="SELECT * FROM dietaviatico, viatico_pagado
				WHERE dietaviatico.id = viatico_pagado.id_dietaviatico
				AND dietaviatico.id = {$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				return "Si";
			}
			else
			{
				return "No";
			}
		}
	}
	
	static function verEstado($idS)
	{
		$query="SELECT autorizado
				FROM dietaviatico, solicitudes_autorizadas
				where dietaviatico.id = solicitudes_autorizadas.id_solicitud 
				and solicitudes_autorizadas.tipo ='Viatico'
				and dietaviatico.id = {$idS}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			$estado="";
			if(sqlsrv_num_rows($rs) >0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if($fila['autorizado']==1)
				{
					$estado = "Aprobada";
				}
				else if ($fila['autorizado']==0)
				{
					$estado = "Rechazada";
				}
			}
			else
			{
				if(ManejadorDietaViatico::cantidadEmpleados($idS) ==0)
				{
					$estado = "Nueva";
				}
				else
				{
					$estado = "Enviada";
				}
			}
			return $estado;
		}
	}
	
	static function obtenerReportePunch($id_viatico)
	{
		$m = new Manejador();
		$datos = array();
		$idD = array();
		$lugar =array();
		$fs = array();
		$fe = array();
		$he = array();
		$hs = array();
		$emp = array();
		$totalesbd = array();
		$totalespn = array();
		
		$query="SELECT *
				FROM destinos_viaticos
				WHERE id_viatico = {$id_viatico}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$idD[] = $fila['id'];
			$fs[] = $fila['fecha_salida'];
			$fe[] = $fila['fecha_entrada'];
			$hs[] = $fila['hora_salida'];
			$he[] = $fila['hora_entrada'];
			$lugar[] = $fila['lugar'];
		}
		
		$query="SELECT id_empleado, desayuno, almuerzo, cena, dormitorio
				FROM viatico_empleado, posicion_viatico, empleado
				WHERE id_formulario = {$id_viatico}
				AND empleado.id = viatico_empleado.id_empleado
				AND empleado.tipo_viatico = posicion_viatico.id";	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$empleado = $fila['id_empleado'];
			$emp[]=$fila['id_empleado'];
			$totalesbd[$empleado]=0;
			for($x=0;$x<count($fe);$x++)
			{
				$datos['nombre'][$empleado]=$m->obtenerNombre($empleado);
				$tdes=0; $tal=0; $tcen=0; $tdor =0;
				$total1 =0;
				$dias;
				//echo $m->obtenerNombre($empleado);
				//echo "<br>---FOR recorrido {$x} ---<br>";
				$sql="	SELECT empleado.nombre as nombre, horadeentrada, horadesalida
						FROM empleado, horario
						WHERE empleado.id = horario.id_empleado
						AND empleado.id = {$empleado}
						AND horario.fecha = '{$fe[$x]->format('Y-m-d')}'";
				$rs2 = sqlsrv_query($_SESSION['con'],$sql, $params, $options);
				if(sqlsrv_num_rows($rs2) > 0 )
				{
					while($filaEmp=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC))
					{		
						//echo "<br><br>"." Grupo de fecha: {$fe[$x]}"."<br>";
						$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
						
						$dias = (strtotime($fe[$x]->format('Y-m-d'))-strtotime($fs[$x]->format('Y-m-d')))/86400;
						$dias = abs($dias); $dias = floor($dias);					
						//echo "Dias ".$dias."<br>";
						$horaE = explode(":", $filaEmp['horadeentrada']->format('H:i:s'));
						$horaS = explode(":", $filaEmp['horadesalida']->format('H:i:s'));
						//echo $filaEmp['horadeentrada']."   ";
						//echo $filaEmp['horadesalida']."<br>";
						if($horaE[0] <= 7)
						{
							$desayuno = true;
							$tdes+=1;
						}
						if ($horaS[0] >= 12)
						{
							$almuerzo = true;
							$tal+=1;
						}
						if ($horaS[0]>=18)
						{
							$cena = true;
							$tcen+=1;
						}

						if($desayuno)
						{
							//echo "Desayuno: {$fila['desayuno']}"."<br>";
							$total1 += $fila['desayuno'];
						}

						if ($almuerzo)
						{
							//echo "Almuerzo: {$fila['almuerzo']}"."<br>";
							$total1 += $fila['almuerzo'];
						}

						if ($cena)
						{
							//echo "Cena: {$fila['cena']}"."<br>";
							$total1 += $fila['cena'];
						}

						$dormitorio = $fila['dormitorio'] * $dias;
						//echo "Dormitorio: {$dormitorio} ({$dias} dias)"."<br>";
						$total1 += $dormitorio;		
							
						//$total = 0;
						//$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					}	
					//echo "Total: ".$total1."<br><br>";
					//$totalesbd[$empleado]+=$total1;
					$totalesbd[$empleado]+=$total1;
				}
				else
				{
					//echo $m->obtenerNombre($empleado)." no tiene datos en esta fecha. <br>";
				}
			}				
		}
		
		//echo "<pre>";
		//echo var_dump($datos);
		//echo "</pre>";
		
		$arrFechaE = array();
		$arrFechaS = array();
		$arrHoraE = array();
		$arrHoraS = array();
		$arrLugar = array();
		$arrIDDestinos = array();
		
		$queryCargar="SELECT * FROM destinos_viaticos WHERE id_viatico = {$id_viatico}";
		$rsCargar = sqlsrv_query($_SESSION['con'],$queryCargar, $params, $options);
		while($filaC=sqlsrv_fetch_array($rsCargar, SQLSRV_FETCH_ASSOC))
		{
			$arrIDDestinos[] =$filaC['id'];
			$arrFechaE[]=$filaC['fecha_entrada'];
			$arrFechaS[]=$filaC['fecha_salida'];
			$arrHoraE[]=$filaC['hora_entrada'];
			$arrHoraS[]=$filaC['hora_salida'];
			$arrLugar[]=$filaC['lugar'];
		}			
		
		$query="SELECT empleado.id AS id, desayuno, almuerzo, cena, dormitorio
				FROM empleado, dietaviatico, viatico_empleado, posicion_viatico
				WHERE empleado.id = viatico_empleado.id_empleado
				AND dietaviatico.id = viatico_empleado.id_formulario
				AND dietaviatico.id ={$id_viatico}
				AND empleado.id = viatico_empleado.id_empleado
				AND empleado.tipo_viatico = posicion_viatico.id";
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			
			while($filaEmpt=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$tdes=0; $tal=0; $tcen=0;
				$total =0;
				$dias;
				$totalespn[$filaEmpt['id']]=0;
				//echo "Empleado: ".$filaEmpt['id'];
				for($f=0;$f<count($arrFechaE);$f++)
				{
					//echo "<br><br>"." Grupo de fecha: {$f}"."<br>";
					$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					
					$dias = (strtotime($arrFechaE[$f]->format('Y-m-d'))-strtotime($arrFechaS[$f]->format('Y-m-d')))/86400;
					$dias = abs($dias); $dias = floor($dias);					
					//echo "Dias ".$dias."<br>";
					for($x=0;$x<=$dias;$x++)
					{
						$horaE = explode(":", $arrHoraE[$f]->format('H:i:s'));
						$horaS = explode(":", $arrHoraS[$f]->format('H:i:s'));
						if($horaE[0] <= 7)
						{
							$desayuno = true;
							$tdes+=1;
						}
						if ($horaS[0] >= 12)
						{
							$almuerzo = true;
							$tal+=1;
						}
						if ($horaS[0]>=18)
						{
							$cena = true;
							$tcen+=1;
						}

						if($desayuno)
						{
							//echo "Desayuno: {$filaEmpt['desayuno']}"."<br>";
							$total += $filaEmpt['desayuno'];
						}
						if ($almuerzo)
						{
							//echo "Almuerzo: {$filaEmpt['almuerzo']}"."<br>";
							$total += $filaEmpt['almuerzo'];
						}
						if ($cena)
						{
							//echo "Cena: {$filaEmpt['cena']}"."<br>";
							$total += $filaEmpt['cena'];
						}
					}
					$dormitorio = $filaEmpt['dormitorio'] * $dias;
					//echo "Dormitorio: {$dormitorio} ({$dias} dias)"."<br>";
					$total += $dormitorio;
					
					
					//$total = 0;
					//$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
				}							
				//echo "Total: ".$total."<br>";
				$totalespn[$filaEmpt['id']]+= $total;
				//$totalespn[]+= $total;
			}
			$datos['totalpn'] = $totalespn;	
			$datos['totalbd'] = $totalesbd;	
		}
		for($c=0;$c<count($totalesbd);$c++)
		{
			$datos['debe'][$emp[$c]]=($totalespn[$emp[$c]]- $totalesbd[$emp[$c]]);
			//echo $m->obtenerNombre($emp[$c])." Debe devolver: ". ($totalesbd[$emp[$c]] - $totalespn[$emp[$c]]) * -1 ."<br><br>"; 
		}
		return array($datos,$emp);
	}
	
	static function eliminarEmpleadosS($idS)
	{
		$query="DELETE FROM viatico_empleado WHERE id_formulario={$idS}";
		$rs=sqlsrv_query($_SESSION['con'],$query);
	}
}


?>