<?php 
	class ManejadorSolicitud
	{
		static function agregarEmpleados($idS,$arrayE)
		{
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			foreach($arrayE as $valor)
			{
				$queryCheck="SELECT * from solicitudes where id_solicitud={$idS} AND id_empleado={$valor}";
				$rsCheck=sqlsrv_query($_SESSION['con'],$queryCheck, $params, $options);
				if (sqlsrv_num_rows($rsCheck)==0) {
					$query="INSERT INTO solicitudes
							(id_solicitud, id_empleado)
							VALUES
							({$idS},{$valor})";
					$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
				}
			}
		}
		
		static function eliminarEmpleados($idS, $arrayE)
		{
			foreach($arrayE as $valor)
			{
				$query = "DELETE FROM solicitudes
						  WHERE id_solicitud = {$idS} 
						  AND id_empleado ={$valor}";
				$params = array();
				$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
				$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			}
		}
		
		static function obtenerSolicitudes($departamento, $usuario='')
		{
			$query="";
			if(empty($departamento))
			{
				$query="SELECT top 40 * from solicitudHE ";
			}
			else
			{
				$query="SELECT top 40 *
						FROM solicitudHE
						WHERE usr='{$usuario}'";
			}
			$query.=" order by fecha desc";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			return $rs;
		}
		
		static function obtenerTodasSolicitudes($departamento)
		{
			$query="";
			if(empty($departamento))
			{
				$query="SELECT * from solicitudHE";
			}
			else
			{
				$query="SELECT *
						FROM solicitudHE
						WHERE departamento ='{$departamento}'";
			}
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
			return $rs;
		}
		
		static function obtenerEmpleados($idS)
		{
			$query="SELECT empleado.id as id, empleado.nombre as nombre, t_cargo.nombre as cargo, t_departamento.nombre as departamento, cedula, sueldo
					FROM empleado, solicitudes, solicitudHE, t_cargo, t_departamento
					WHERE empleado.id = solicitudes.id_empleado
					AND t_cargo.id = empleado.cargo
					AND t_departamento.id = empleado.departamento
					AND solicitudHE.id = solicitudes.id_solicitud
					AND solicitudHE.id = {$idS}
					ORDER BY empleado.nombre";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			return $rs;
		}
		
		static function obtenerFormularios($dpto)
		{
			$query="SELECT solicitudhe.noOficio as noOficio
					FROM solicitudHE, solicitudes_autorizadas
					WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
					AND tipo = 'HoraExtra'
					AND autorizado = 1
					AND departamento = '{$dpto}'";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
			
			echo "<option value='ninguno'>Ninguno</option>";
			
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "<option value={$fila['noOficio']}>{$fila['noOficio']}</option>";
			}
		}
		
		static function obtenerNoOficio($id)
		{
			$query="SELECT noOficio
					FROM solicitudhe
					WHERE id={$id}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
			if($rs)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['noOficio'];
			}
			else
			{
				return "X";
			}
		}
		static function obtenerEmpleadosDpto($id,$departamento)
		{
			$sql="  SELECT empleado.id as id, empleado.cedula as cedula, empleado.nombre as nombre, t_cargo.nombre as cargo, t_departamento.nombre as departamento
					FROM empleado, t_cargo, t_departamento 
					WHERE NOT EXISTS
					(SELECT 1 FROM solicitudes WHERE id_solicitud = {$id}
					AND id_empleado = empleado.id)
					AND t_departamento.nombre= '{$departamento}' 
					AND t_cargo.id = empleado.cargo
					AND t_departamento.id = empleado.departamento
					ORDER BY nombre";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs= sqlsrv_query($_SESSION['con'],$sql, $params, $options);($sql);	
			return $rs;
		}
		
		static function obtenerSolicitud($id)
		{
			$query="SELECT * 
					FROM solicitudhe
					WHERE id={$id}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
			if($rs)
			{
				if(sqlsrv_num_rows($rs) >0)
				{
					return $rs;
				}
			}
		}
		
		static function validarDuplicado($noOficio)
		{
			$query="SELECT noOficio 
					FROM solicitudhe
					WHERE noOficio = '{$noOficio}'";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
			if($rs)
			{
				if(sqlsrv_num_rows($rs) >0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		static function cantidadEmpleados($idS)
		{
			$query="SELECT count(id_empleado) as cantidad_empleados
					FROM empleado, solicitudes, solicitudhe
					WHERE empleado.id = solicitudes.id_empleado and solicitudhe.id = solicitudes.id_solicitud and solicitudhe.id = {$idS}";
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
		
		static function verEstado($idS)
		{
			$query="SELECT autorizado
					FROM solicitudhe, solicitudes_autorizadas
					where solicitudhe.id = solicitudes_autorizadas.id_solicitud 
					and solicitudes_autorizadas.tipo ='HoraExtra'
					and solicitudhe.id = {$idS}";
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
					if(ManejadorSolicitud::cantidadEmpleados($idS) ==0)
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
		
		static function obtenerFiltros()
		{
			echo 	"<option value='estado'>Estado</option>
					 <option value='fecha'>Fecha de Creacion</option>
					 <option value='fecha2'>Fecha Solicitada</option>";
		}
		
		static function obtenerEstados()
		{
			echo "	<option value='todos'>Todas</option>
					<option value='Nueva'>Nueva</option>
					<option value='Enviada'>Enviada</option>
					<option value='Aprobada'>Aprobada</option>
					<option value='Rechazada'>Rechazada</option>";
		}
		
		static function solicitudesAprobadas($departamento, $fechac, $fechas, $usuario='')
		{
			if(empty($departamento)|| strcmp($departamento,"todos")==0)
			{
				$query="SELECT TOP 35 solicitudhe.fecha_creacion as fecha_creacion, solicitudhe.fecha as fecha, solicitudhe.id as id, solicitudhe.noOficio as noOficioHE
						FROM solicitudhe, solicitudes_autorizadas
						WHERE NOT EXISTS
						(SELECT 1 FROM horasextra_pagadas WHERE id_solicitud = solicitudhe.id)
						AND solicitudhe.id = solicitudes_autorizadas.id_solicitud
						AND tipo = 'HoraExtra'
						AND autorizado = 1 ";
			}
			else
			{
				$query="SELECT TOP 35 solicitudhe.fecha_creacion as fecha_creacion, solicitudhe.fecha as fecha, solicitudhe.id as id, solicitudhe.noOficio as noOficioHE
						FROM solicitudhe, solicitudes_autorizadas
						WHERE NOT EXISTS
						(SELECT 1 FROM horasextra_pagadas WHERE id_solicitud = solicitudhe.id)
						AND solicitudhe.id = solicitudes_autorizadas.id_solicitud
						AND tipo = 'HoraExtra'
						AND autorizado = 1 
						AND solicitudhe.usr = '{$usuario}'";
			}
			if (empty($fechac) && empty($fechas)) {
				$query.= " order by fecha desc";
			}
			if (!empty($fechac))
			{
				$query.= " AND fecha_creacion ='{$fechac}' ORDER BY fecha_creacion desc";
			}
			else if (!empty($fechas))
			{
				$query.= " AND fecha ='{$fechas}' ORDER BY fecha desc";
			}
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );			
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			return $rs;
		}

		static function obtenerBeneficiarios($idS)
		{
			$query="SELECT empleado.id as id, empleado.nombre as nombre, t_cargo.nombre as cargo, cedula
					FROM empleado, solicitudes, solicitudHE, t_cargo
					WHERE empleado.id = solicitudes.id_empleado
					AND solicitudHE.id = solicitudes.id_solicitud
					AND solicitudHE.id = {$idS}
					AND t_cargo.id = empleado.cargo";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
			return $rs;
		}
		
		static function generarNoOficio()
		{
			$nOficio="ATE".date('y')."-";
			$query="SELECT MAX( id ) AS id
					FROM solicitudhe";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
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
		
		static function contarHEAprobadas($dpto, $usr='')
		{
			if(empty($dpto) || strcmp($dpto,"todos")==0)
			{
				$query="SELECT COUNT( solicitudhe.id ) AS Cantidad
						FROM solicitudhe, solicitudes_autorizadas
						WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
						AND tipo =  'HoraExtra'
						AND autorizado =1";				
			}
			else
			{
				$query="SELECT COUNT( solicitudhe.id ) AS Cantidad
						FROM solicitudhe, solicitudes_autorizadas
						WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
						AND departamento = '{$dpto}'
						AND solicitudhe.usr = '{$usr}'
						AND tipo =  'HoraExtra'
						AND autorizado =1";			
			}
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['Cantidad'];	
			}
		}

		static function contarHERechazadas($dpto, $usr='')
		{
			if(empty($dpto) || strcmp($dpto,"todos")==0)
			{
				$query="SELECT COUNT( solicitudhe.id ) AS Cantidad
						FROM solicitudhe, solicitudes_autorizadas
						WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
						AND tipo =  'HoraExtra'
						AND autorizado =0";						
			}
			else
			{
				$query="SELECT COUNT( solicitudhe.id ) AS Cantidad
						FROM solicitudhe, solicitudes_autorizadas
						WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
						AND departamento = '{$dpto}'
						AND solicitudhe.usr = '{$usr}'
						AND tipo =  'HoraExtra'
						AND autorizado =0";			
			}
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['Cantidad'];	
			}
		}
		
		static function contarHEPendientes($dpto, $usr='')
		{
			if(empty($dpto) || strcmp($dpto,"todos")==0)
			{
				$query="SELECT COUNT(id) as Cantidad 
						FROM solicitudhe
						WHERE NOT EXISTS
						(SELECT 1 FROM solicitudes_autorizadas where solicitudes_autorizadas.id_solicitud = solicitudhe.id )";			
			}
			else
			{
				$query="SELECT COUNT(id) as Cantidad 
						FROM solicitudhe
						WHERE NOT EXISTS
						(SELECT 1 FROM solicitudes_autorizadas where solicitudes_autorizadas.id_solicitud = solicitudhe.id )
						AND departamento ='{$dpto}'
						AND solicitudhe.usr='{$usr}'";			
			}
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['Cantidad'];
			}
		}
		
		static function contarHENuevas($dpto, $usr='')
		{
			if(empty($dpto) || strcmp($dpto,"todos")==0)
			{
				$query="SELECT COUNT(solicitudhe.id) AS Cantidad
						FROM solicitudhe, solicitudes
						WHERE solicitudes.id_solicitud = solicitudhe.id 
						GROUP BY Solicitud
						HAVING COUNT( id_empleado ) =0";
			}
			else
			{
				$query="SELECT COUNT(solicitudhe.id) AS Cantidad
						FROM solicitudhe, solicitudes
						WHERE solicitudes.id_solicitud = solicitudhe.id 
						AND departamento='{$dpto}'
						AND solicitudhe.usr='{$usr}'
						GROUP BY Solicitud
						HAVING COUNT( id_empleado ) =0";			
			}
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['Cantidad'];
			}
		}
		
		static function verificarPagado($id)
		{
			$query="SELECT * FROM solicitudhe, horasextra_pagadas
					WHERE solicitudhe.id = horasextra_pagadas.id_solicitud
					AND solicitudhe.id = {$id}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);($query);
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
		
		static function pagarSolicitud($id)
		{
			$query="SELECT noOficio, fecha_creacion, solicitudhe.fecha  
					FROM solicitudhe, horasextra_pagadas
					WHERE solicitudhe.id = horasextra_pagadas.id_solicitud
					AND solicitudhe.id = {$id}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				if(sqlsrv_num_rows($rs)>0)
				{
					echo "<script>alert('Esta solicitud ya esta pagada');</script>";
				}
				else
				{
					$fecha = date("Y-m-d");
					$query2="INSERT INTO horasextra_pagadas
							(id_solicitud, fecha)
							VALUES
							({$id}, '{$fecha}')";
					sqlsrv_query($_SESSION['con'],$query2, $params, $options);
				}
			}
		}
		
		static function obtenerTransporte($idS)
		{
			$query="SELECT id_formulario_transporte
					FROM horaextra_transporte
					WHERE id_solicitudhe = {$idS}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if(sqlsrv_num_rows($rs)>0)
				{
					return $fila['id_formulario_transporte'];
				}
				else
				{
					return 0;
				}
			}
		}

		static function obtenerTotalHoras($idE, $fechai, $fechaf, $f, $idUsr)
		{
			$horas="";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

			$query="SELECT * from [dbo].[totalHorasTable] 
					(
						{$idE}
						,{$f}
						,'{$fechai}'
						,'{$fechaf}'
						,{$idUsr}
					)";
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			if($rs)
			{
				if(sqlsrv_num_rows($rs)>0)
				{
					while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
					{
						$horas.= $fila['hora']."/*".$fila['pago'];
					}
				}
				else
				{
					echo "no rows";
				}
			}
			return $horas;
		}

		static function reestablecerNumO()
		{
			$query = "SELECT * from solicitudhe";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if ($rs) {
				while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
					$temp = explode("-", $fila['noOficio']);
					if ($fila['id'] != $temp[1]) {
						$nuevoNoOficio = $temp[0]."-".$fila['id'];
						$query2="UPDATE solicitudhe set noOficio='{$nuevoNoOficio}' WHERE id={$fila['id']}";
						sqlsrv_query($_SESSION['con'], $query2, $params, $options);
					}
				}
			}
		}

		static function obtenerHistSolicitud($usr, $idS)
		{
			$data="";
			$query = "SELECT count(hist.id_empleado) guardados, sum(hist.pago) monto_guardado from solicitudhe she
						inner join solicitudes s on s.id_solicitud = she.id
						inner join empleado e on e.id = s.id_empleado
						inner join horario h on h.id_empleado = e.id and h.fecha = she.fecha
						inner join historial_empleado hist on hist.id_SHE = she.id and hist.id_empleado = e.id and hist.id_horario = h.id
						where she.usr='{$usr}'
						and she.id={$idS}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if ($rs) {
				if (sqlsrv_num_rows($rs)>0) {
					while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
						$data.=$fila['guardados'].";".$fila['monto_guardado'];
					}
				}
			}
			return $data;
		}

		static function obtenerFechaSolicitud($idS)
		{
			$temp="";
			$query = "SELECT fecha
						FROM solicitudhe 
						WHERE id={$idS}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if ($rs) {
				if (sqlsrv_num_rows($rs)>0) {
					$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
					$temp=$fila['fecha'];	
				}
			}
			return $temp;
		}
	}
?>