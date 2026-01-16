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
	
	static function obtenerBeneficiarios($idS)
	{
		$idEmp = array();
		$query="SELECT empleado.id as id, cedula, empleado.nombre as nombre, t_cargo.nombre as cargo,posicion_viatico.posicion as concepto , total
				from empleado, dietaviatico, viatico_empleado, posicion_viatico, t_cargo
				where empleado.id = viatico_empleado.id_empleado
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
					</tr>";
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

		$desayuno = false;
		$almuerzo = false; 
		$cena = false; 
		$dormitorio= false;
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
				$total=0;
				$dias=0;
				//echo "Empleado: ".$filaEmp['id'];
				for($f=0;$f<count($arrFechaE);$f++)
				{
					//echo "<br><br>"." Grupo de fecha: {$f}"."<br>";
					
					$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					$dias = (strtotime($arrFechaE[$f]->format('Y-m-d'))-strtotime($arrFechaS[$f]->format('Y-m-d')))/86400;
					$dias = abs($dias); $dias = floor($dias);									
					//echo "Dias ".$dias."<br>";
					$x=0;
					for($x=0;$x<=$dias;$x++)
					{
						$horaE = explode(":", $arrHoraE[$f]->format('H:i:s'));
						$horaS = explode(":", $arrHoraS[$f]->format('H:i:s'));
						if ($dias>=1)
						{
							if ($x==$dias) {
								if($arrHoraS[$f]->format('H:i:s') > '05:46')
								{
									//echo "desayuno x=dias entrada <br>";
									$desayuno = true;
								}

								if($arrHoraS[$f]->format('H:i:s') >='13:00') {
									$almuerzo=true;
								}

								if ($arrHoraS[$f]->format('H:i:s')>='18:00') {
									$cena = true;
								}
							}
							else
							{
								if ($x==0) {
									if($arrHoraE[$f]->format('H:i:s') <= '05:46' )
									{
										$desayuno = true;
									}

									if($arrHoraE[$f]->format('H:i:s') <='12:00' ) {//&& $arrHoraS[$f]->format('H:i:s') >='13:00'
										$almuerzo=true;
									}

									$cena = true;
								}
								else
								{
									$desayuno = true;
										
									$almuerzo=true;

									$cena = true;							
								}
							}
						}
						else
						{
							if($arrHoraE[$f]->format('H:i:s') <= '05:46')
							{
								$desayuno = true;
							}

							if($arrHoraE[$f]->format('H:i:s') <'12:00' && $arrHoraS[$f]->format('H:i:s') >='13:00' ) {
								$almuerzo=true;
							}

							if ($arrHoraS[$f]->format('H:i:s') >='18:00') {
								$cena = true;
							}
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
						$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					}
					if (ManejadorDietaViatico::esChofer($filaEmp['id']) && ManejadorDietaViatico::viajaConTecnico($idF)) {
						$dormitorio = ManejadorDietaViatico::obtenerDormitorioTecnico() * $dias;
					}
					else
					{
						$dormitorio = $filaEmp['dormitorio'] * $dias;
					}
					
					//echo "Dormitorio: {$dormitorio} ({$dias} dias)"."<br>";
					$total += $dormitorio;		
					$tdor= $dias;
						
					//$total = 0;
				}
				$query2="SELECT id
						 FROM viatico_empleado
						 WHERE id_empleado = {$filaEmp['id']}
						 AND id_formulario = {$idF}";
				$rs2 = sqlsrv_query($_SESSION['con'],$query2, $params, $options);
				if($rs2)
				{
					if(sqlsrv_num_rows($rs2) ==1)
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
	
	static function calcularViaticoT($idF)
	{
		$arrFechaE = array();
		$arrFechaS = array();
		$arrHoraE = array();
		$arrHoraS = array();
		$arrLugar = array();
		$arrIDDestinos = array();

		$desayuno = false;
		$almuerzo = false; 
		$cena = false; 
		$dormitorio= false;
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
				$total=0;
				$dias=0;
				//echo "Empleado: ".$filaEmp['id'];
				for($f=0;$f<count($arrFechaE);$f++)
				{
					//echo "<br><br>"." Grupo de fecha: {$f}"."<br>";
					
					$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					$dias = (strtotime($arrFechaE[$f]->format('Y-m-d'))-strtotime($arrFechaS[$f]->format('Y-m-d')))/86400;
					$dias = abs($dias); $dias = floor($dias);									
					//echo "Dias ".$dias."<br>";
					$x=0;
					for($x=0;$x<=$dias;$x++)
					{
						$horaE = explode(":", $arrHoraE[$f]->format('H:i:s'));
						$horaS = explode(":", $arrHoraS[$f]->format('H:i:s'));
						if ($dias>=1)
						{
							if ($x==$dias) {
								if($arrHoraS[$f]->format('H:i:s') > '05:46')
								{
									//echo "desayuno x=dias entrada <br>";
									$desayuno = true;
								}

								if($arrHoraS[$f]->format('H:i:s') >='13:00') {
									$almuerzo=true;
								}

								if ($arrHoraS[$f]->format('H:i:s')>='18:00') {
									$cena = true;
								}
							}
							else
							{
								if ($x==0) {
									if($arrHoraE[$f]->format('H:i:s') <= '05:46' )
									{
										$desayuno = true;
									}

									if($arrHoraE[$f]->format('H:i:s') <='12:00' ) {//&& $arrHoraS[$f]->format('H:i:s') >='13:00'
										$almuerzo=true;
									}

									$cena = true;
								}
								else
								{
									$desayuno = true;
										
									$almuerzo=true;

									$cena = true;							
								}
							}
						}
						else
						{
							if($arrHoraE[$f]->format('H:i:s') <= '05:46')
							{
								$desayuno = true;
							}

							if($arrHoraE[$f]->format('H:i:s') <'12:00' && $arrHoraS[$f]->format('H:i:s') >='13:00' ) {
								$almuerzo=true;
							}

							if ($arrHoraS[$f]->format('H:i:s') >='18:00') {
								$cena = true;
							}
						}

						if($desayuno)
						{
							//echo "Desayuno: {$filaEmp['desayuno']}"."<br>";
							$total += $filaEmp['desayuno'] * 1.05;
						}
						if ($almuerzo)
						{
							//echo "Almuerzo: {$filaEmp['almuerzo']}"."<br>";
							$total += $filaEmp['almuerzo'] * 1.05;
						}
						if ($cena)
						{
							//echo "Cena: {$filaEmp['cena']}"."<br>";
							$total += $filaEmp['cena']* 1.05;
						}
						$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					}
					if (ManejadorDietaViatico::esChofer($filaEmp['id']) && ManejadorDietaViatico::viajaConTecnico($idF)) {
						$dormitorio = ManejadorDietaViatico::obtenerDormitorioTecnico() * $dias;
					}
					else
					{
						$dormitorio = $filaEmp['dormitorio'] * $dias;
					}
					
					//echo "Dormitorio: {$dormitorio} ({$dias} dias)"."<br>";
					
					
					$total += $dormitorio * 1.05;		
					$tdor= $dias;
						
					//$total = 0;
				}
				$query2="SELECT id
						 FROM viatico_empleado
						 WHERE id_empleado = {$filaEmp['id']}
						 AND id_formulario = {$idF}";
				$rs2 = sqlsrv_query($_SESSION['con'],$query2, $params, $options);
				if($rs2)
				{
					if(sqlsrv_num_rows($rs2) ==1)
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
					$lugar=utf8_decode($arrLugar[$x]);
					$query2="INSERT INTO destinos_viaticos
					(id_viatico, fecha_entrada, fecha_salida, hora_entrada, hora_salida, lugar)
					VALUES
					({$idS}, '{$arrFechaE[$x]}', '{$arrFechaS[$x]}', '{$arrHoraE[$x]}', '{$arrHoraS[$x]}', '{$lugar}' )";
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
	
	static function obtenerFormularios($fechac, $fechas)
	{

		$query="SELECT TOP 300 dietaviatico.id as id, dietaviatico.no_oficio as no_oficio, dietaviatico.fecha_creacion as fecha_creacion, transporte
				FROM dietaviatico
				WHERE dietaviatico.usr = '{$_SESSION['usuario']}' ";

		if (!empty($fechac))
		{
			$query.= " AND dietaviatico.fecha_creacion ='{$fechac}' ";
		}
		$query.=" ORDER BY fecha_creacion desc";
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

	static function obtenerBeneficiariosReporte($idS)
	{
		$query="SELECT cedula, empleado.nombre as nombre, t_cargo.nombre as cargo , REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,total),1), '.00','')as total
				from empleado, dietaviatico, viatico_empleado, t_cargo
				where dietaviatico.id = {$idS}
				AND t_cargo.id = empleado.cargo
				AND empleado.id = viatico_empleado.id_empleado
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

	}
	
	static function eliminarEmpleadosS($idS)
	{
		$query="DELETE FROM viatico_empleado WHERE id_formulario={$idS}";
		$rs=sqlsrv_query($_SESSION['con'],$query);
	}

	static function obtenerChoferes()
	{
		$result = array();
		$query="SELECT id
				FROM empleado e
				where e.cargo in(39,41)
				ORDER BY nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
				$result[]=$fila['id'];
			}
			
		}
		return $result;
	}

	static function asignarChofer($chofer,$solicitud)
	{
		$query = "SELECT * from transporte_viatico 
					where id_chofer={$chofer} 
					AND id_viatico = {$solicitud} 
					AND usr='{$_SESSION['usuario']}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			if (sqlsrv_num_rows($rs) <= 1) {
				$fecha= date('Y-m-d H:i:s');
				$queryI = "INSERT into transporte_viatico (id_viatico, id_chofer, fecha, usr) VALUES ({$solicitud}, {$chofer}, convert(datetime,'{$fecha}', 120), '{$_SESSION['usuario']}')";
				$rsi = sqlsrv_query($_SESSION['con'],$queryI, $params, $options);

			}
		}
		else
		{
			if( ($errors = sqlsrv_errors() ) != null) {
		        foreach( $errors as $error ) {
		            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
		            echo "code: ".$error[ 'code']."<br />";
		            echo "message: ".$error[ 'message']."<br />";
		        }
	    	}
		}
		ManejadorDietaViatico::calcularViatico($solicitud);
	}

	static function obtenerCategoriaEmpSolicitud($idF)
	{
		$empleados = array();
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$query="SELECT posicion_viatico.posicion as categoria, desayuno, almuerzo, cena, dormitorio
				FROM empleado, posicion_viatico, dietaviatico, viatico_empleado
				where tipo_viatico = posicion_viatico.id
				AND empleado.id = viatico_empleado.id_empleado
				AND dietaviatico.id = viatico_empleado.id_formulario
				AND dietaviatico.id = {$idF}
				GROUP BY posicion_viatico.posicion, desayuno, almuerzo, cena, dormitorio";
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$empleados[] = $fila['categoria'].";".$fila['desayuno'].";".$fila['almuerzo'].";".$fila['cena'].";".$fila['dormitorio'];
			}
		}
		return $empleados;
	}

	static function obtenerDetalleViatico($idS)
	{
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
						if($arrHoraS[$f]->format('H:i:s') >= '05:46')
						{
							$tdes+=1;
						}

						if($arrHoraS[$f]->format('H:i:s') >='13:00') {
							$tal+=1;
						}

						if ($arrHoraS[$f]->format('H:i:s')>='18:00') {
							$tcen+=1;
						}
					}
					else
					{
						if ($x==0) {
							if($arrHoraE[$f]->format('H:i:s') <= '05:46' )
							{
								$tdes+=1;
							}

							if($arrHoraE[$f]->format('H:i:s') <='12:00' ) {//&& $arrHoraS[$f]->format('H:i:s') >='13:00'
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
					if($arrHoraE[$f]->format('H:i:s') <= '05:46' ){
						$tdes+=1;
					}

					if($arrHoraE[$f]->format('H:i:s') <='12:00' && $arrHoraS[$f]->format('H:i:s') >='13:00' ) {
						$tal+=1;
					}

					if ($arrHoraS[$f]->format('H:i:s') >='18:00') {
						$tcen+=1;
					}
				}
			}

			$tdor= $dias;
			$conteo[] = $fecha.";".$fecha2.";".$tdes.";".$tal.";".$tcen.";".$tdor;
			$tdes=0;$tal=0;$tcen=0;
		}
		return $conteo;
	}

	static function obtenerEmpleadosDisponiblesDV(){
		$query="select a.id, a.nombre, a.cedula, b.nombre cargo from empleado a
				inner join t_cargo b on a.cargo = b.id
				inner join grupo_empleados g on g.id_empleado = a.id
				WHERE g.id_secretaria={$_SESSION['id']}
				order by a.nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

		$rs =sqlsrv_query($_SESSION['con'],$query, $params, $options);

		if ($rs) {
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

	static function maquetarChoferesDisponibles()
	{
		$choferes = array();

		$choferes = ManejadorDietaViatico::obtenerChoferes();

		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

		foreach ($choferes as $id) {
			$query="SELECT alias.fecha_entrada, alias.fecha_salida, e.id,e.nombre,
					case when convert(date,GETDATE(), 112) between alias.fecha_entrada and alias.fecha_salida and
						convert(time,GETDATE(), 108) between alias.hora_entrada and alias.hora_salida
						then 'Ocupado' else 'Disponible' end estado, dv.no_oficio
					FROM empleado e
					left join (select a.*,dv.fecha_entrada, fecha_salida, dv.hora_entrada, dv.hora_salida from viatico_empleado a 
						right join destinos_viaticos dv on a.id_formulario = dv.id_viatico) alias on e.id = alias.id_empleado
					left join dietaviatico dv on dv.id = alias.id_formulario
					where e.id={$id} AND fecha_salida > GETDATE()";
			$rs =sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if ($rs) {
				if (sqlsrv_num_rows($rs)>0) {
					while ($fila = sqlsrv_fetch_array($rs,SQLSRV_FETCH_ASSOC)) {
						echo "	<tr class='tab_bg_2'>";
						if (strcmp($fila['estado'], 'Ocupado')==0) {
							echo "<td></td>";
						}
						else
						{
							echo "<td><input type='checkbox' name='chkChofer[]' value='{$fila['id']}'></td>";				
						}

						echo "
								<td>{$fila['nombre']}</td>
								<td>{$fila['estado']}</td>
								<td>{$fila['no_oficio']}</td>
								<td>{$fila['fecha_entrada']->format('Y-m-d')}</td>
								<td>{$fila['fecha_salida']->format('Y-m-d')}</td>
							</tr>";					
					}
				}
				else
				{
					echo "	
						<tr class='tab_bg_2'>
							<td><input type='checkbox' name='chkChofer[]' value='{$id}'></td>
						  	<td>{$_SESSION['m']->obtenerNombre($id)}</td>
						 	<td>Disponible</td>
						  	<td></td>
						  	<td></td>
						  	<td></td>
						</tr>";					
				}
			}

		}

	}

	static function requerirTransporte($id)
	{
		$query ="SELECT * from dietaviatico WHERE id={$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			if (sqlsrv_num_rows($rs) >0) {
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if ($fila['transporte'] ==0) {
					$query2 = "UPDATE dietaviatico set transporte=1 where id={$id}";
				}
				else
				{
					$query2 = "UPDATE dietaviatico set transporte=0 where id={$id}";
				}
				sqlsrv_query($_SESSION['con'],$query2, $params, $options);
			}
		}
	}

	static function esChofer($idE)
	{
		$query ="SELECT cargo from empleado WHERE id={$idE}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			if (sqlsrv_num_rows($rs) >0) {
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if ($fila['cargo']==41 || $fila['cargo']==42) {
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}

	static function obtenerDormitorioTecnico()
	{
		$query ="SELECT dormitorio from posicion_viatico WHERE id=123";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			if (sqlsrv_num_rows($rs) >0) {
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['dormitorio'];
			}
		}
	}

	static function viajaConTecnico($idF)
	{
		$query ="SELECT posicion_viatico.id
				FROM empleado, dietaviatico, viatico_empleado, posicion_viatico
				WHERE empleado.id = viatico_empleado.id_empleado
				AND dietaviatico.id = viatico_empleado.id_formulario
				AND dietaviatico.id ={$idF}
				AND empleado.id = viatico_empleado.id_empleado
				AND empleado.tipo_viatico = posicion_viatico.id";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$f=false;
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if ($rs) {
			if (sqlsrv_num_rows($rs) >0) {
				while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
				{
					if ($fila['id'] <>110) {
						$f= true;
					}					
				}
			}
			return $f;
		}
	}
}
?>