<?php 
if (!defined('Pago_Transporte')) define('Pago_Transporte', 200);
class ManejadorTransporte
{	
	static function obtenerFormularios($departamento)
	{
		$query;
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		if(empty($departamento))
		{
			$query="SELECT * from formulario_transporte";
		}
		else
		{
			$query="SELECT * from formulario_transporte WHERE departamento='{$departamento}'";
		}
		
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
		return $rs;
	}
	
	static function agregarEmpleados($idS,$arrayE)
	{
		foreach($arrayE as $valor)
		{
			//buscar si tiene registros previos
			$queryD="SELECT *
					FROM pago_transporte
					WHERE id_empleado = {$valor} AND id_formulario_transporte = {$idS}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
			$rsD = sqlsrv_query($_SESSION['con'], $queryD, $params, $options);
			$query="";
			if($rsD)
			{
				$filaD=sqlsrv_fetch_array($rsD, SQLSRV_FETCH_ASSOC);
				//si es empleado con  horario especial
				if ($_SESSION['m']->empleadoEspecial($valor) ) {
					$query2 = " SELECT e.id,
								case 
								when h.horadeentrada between '00:00:00' and '07:00:00' and h.horadesalida > '16:59:59' and  dbo.validarNoLaboral(tr.fecha) = 0 then '400' 
								when  h.horadeentrada between '00:00:00' and '07:00:00'  and  dbo.validarNoLaboral(tr.fecha) = 0 or h.horadesalida > '16:59:59' and  dbo.validarNoLaboral(tr.fecha) = 0  then '200'
								when dbo.validarNoLaboral(tr.fecha) = 1 then '200'
								when h.horadesalida < '16:59:59' and h.horadeentrada >'07:00:00' then '0'  end pago 
								from horario h
								inner join empleado e on h.id_empleado = e.id
								inner join formulario_transporte tr on tr.fecha = h.fecha
								where e.horario_especial =1 and tr.id = {$idS} and e.id = {$valor}";
					$rs2 = sqlsrv_query($_SESSION['con'], $query2, $params, $options);	
					if ($rs2) {
						$pago=0;
						if (sqlsrv_num_rows($rs2)>=1) {
							$fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC);
							$pago = $fila['pago'];

							if(sqlsrv_num_rows($rsD)==0)
							{
								if ($pago!=0) {
									$query = "INSERT INTO pago_transporte
									(id_empleado, id_formulario_transporte, pago)
									VALUES
									({$valor},{$idS}, {$pago})";									
								}
							}
							else 
							{
								if ($pago!=$filaD['pago']) {
									$query = "UPDATE pago_transporte SET pago = {$pago} where id={$filaD['id']}";								
								}
							}
						}
					}	
				}
				else
				{
					$query2 = " SELECT case when horadesalida > case when dbo.validarNoLaboral(tr.fecha) = 1 then '01:00:00' else '16:59:59' end then '200' else '0' end pago 
								from horario h
								inner join formulario_transporte tr on tr.fecha = h.fecha
								inner join empleado e on e.id = h.id_empleado
								where tr.id = {$idS} and e.id = {$valor}";
					$rs2 = sqlsrv_query($_SESSION['con'], $query2, $params, $options);	
					if ($rs2) {
						if (sqlsrv_num_rows($rs2)>=1) {
							$fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC);
							if(sqlsrv_num_rows($rsD)==0)
							{
								if ($fila['pago']==200) {
									$query = "INSERT INTO pago_transporte
											(id_empleado, id_formulario_transporte, pago)
										 	VALUES
										 	({$valor},{$idS}, ".Pago_Transporte.")";									
								}				
							}
							else
							{
								if ($filaD['pago']!= $fila['pago']) {
									$query = "UPDATE pago_transporte SET pago = {$fila['pago']} where id={$filaD['id']}";	
								}
							}

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
				}	
				if (!empty($query)) {
					$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);	
				}
			}
			else
			{
				//echo "";
			}
		}
	}
	
	static function eliminarEmpleados($idS, $arrayE)
	{
		foreach($arrayE as $valor)
		{
			$query = "DELETE FROM pago_transporte
					  WHERE id_formulario_transporte = {$idS} 
					  AND id_empleado ={$valor}";
			$rs = sqlsrv_query($_SESSION['con'], $query);
			if(!$rs)
			{
				echo mysql_error($rs);
			}
		}
	}
	
	static function obtenerNombre($id)
	{
		$query="SELECT no_oficio
				FROM formulario_transporte
				WHERE id ={$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
		if($rs)
		{
			$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			return $fila['no_oficio'];
		}
	}
	
	static function obtenerEmpleados($idS)
	{
		$query="SELECT empleado.id as id, empleado.nombre as nombre, t_cargo.nombre as cargo, t_departamento.nombre as departamento, cedula, pago_transporte.pago as pago
				FROM empleado, formulario_transporte, pago_transporte, t_cargo, t_departamento
				WHERE empleado.id = pago_transporte.id_empleado
				AND t_cargo.id = empleado.cargo
				AND t_departamento.id = empleado.departamento
				AND formulario_transporte.id = pago_transporte.id_formulario_transporte
				AND formulario_transporte.id = {$idS}
				AND empleado.id = pago_transporte.id_empleado";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
		return $rs;
	}
	
	static function obtenerEmpleadosDpto($departamento)
	{
		$query=" SELECT *
			   FROM empleado 
			   WHERE departamento = '{$departamento}' 
			   ORDER BY nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs= sqlsrv_query($_SESSION['con'], $query, $params, $options);	
		return $rs;
	}	
	
	static function verEstado($idS)
	{
		$query="SELECT autorizado
				FROM formulario_transporte, solicitudhe,horaextra_transporte, solicitudes_autorizadas
				where solicitudhe.id = solicitudes_autorizadas.id_solicitud 
				AND horaextra_transporte.id_formulario_transporte = formulario_transporte.id
				AND horaextra_transporte.id_solicitudhe = solicitudhe.id
				AND formulario_transporte.id = {$idS}
				and solicitudes_autorizadas.tipo ='HoraExtra'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
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
				if(ManejadorTransporte::cantidadEmpleados($idS) ==0)
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
	
	static function solicitudesAprobadas($departamento, $fechac, $fechas, $usuario='')
	{
		if(empty($departamento) || strcmp($departamento,"todos")==0)
		{
			$query="SELECT solicitudhe.id as idS, formulario_transporte.id as id, area, formulario_transporte.fecha as fecha, formulario_transporte.fecha_creacion as fecha_creacion
					FROM formulario_transporte, solicitudes_autorizadas, solicitudhe, horaextra_transporte
					WHERE NOT 
					EXISTS (

					SELECT 1 
					FROM transporte_pagado
					WHERE id_formulario_transporte = formulario_transporte.id
					)
					AND horaextra_transporte.id_formulario_transporte = formulario_transporte.id
					AND horaextra_transporte.id_solicitudhe = solicitudhe.id
					AND solicitudes_autorizadas.id_solicitud = solicitudhe.id
					AND tipo =  'HoraExtra'
					AND autorizado =1 ";
		}
		else
		{
			$query="SELECT solicitudhe.id as idS, formulario_transporte.id as id, area, formulario_transporte.fecha as fecha, formulario_transporte.fecha_creacion as fecha_creacion
					FROM formulario_transporte, solicitudes_autorizadas, solicitudhe, horaextra_transporte
					WHERE NOT 
					EXISTS (

					SELECT 1 
					FROM transporte_pagado
					WHERE id_formulario_transporte = formulario_transporte.id
					)
					AND horaextra_transporte.id_formulario_transporte = formulario_transporte.id
					AND horaextra_transporte.id_solicitudhe = solicitudhe.id
					AND solicitudes_autorizadas.id_solicitud = solicitudhe.id
					AND tipo =  'HoraExtra'
					AND autorizado =1
					AND solicitudhe.departamento ='{$departamento}'
					AND solicitudhe.usr = '{$usuario}'";
		}
		if (!empty($fechac))
		{
			$query.= " AND formulario_transporte.fecha_creacion ='{$fechac}'";
		}
		else if (!empty($fechas))
		{
			$query.= " AND formulario_transporte.fecha ='{$fechas}'";
		}
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
		return $rs;
	}
	
	static function cantidadEmpleados($idS)
	{
		$query="SELECT count(id_empleado) as cantidad_empleados
				FROM empleado, pago_transporte, formulario_transporte
				WHERE empleado.id = pago_transporte.id_empleado and formulario_transporte.id = pago_transporte.id_formulario_transporte and formulario_transporte.id = {$idS}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );			
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs) >0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['cantidad_empleados'];
			}
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
	
	static function asignarTransporte($idT, $idS)
	{
		$query2="INSERT INTO horaextra_transporte
				(id_solicitudhe, id_formulario_transporte)
				VALUES
				({$idS}, {$idT})";
		sqlsrv_query($_SESSION['con'], $query2);
	}

	static function verificarHeTrans($idT)
	{
		$flag=false;
		$query="SELECT * FROM horaextra_transporte
				WHERE id_formulario_transporte ={$idT}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
		{
			if($rs)
			{
				if(sqlsrv_num_rows($rs)===0)
				{
					$flag=true;
				}				
			}
		}
		return $flag;
	}
	
	static function eliminarRelacionHETR($idT, $idS)
	{
		$query="DELETE FROM horaextra_transporte
				WHERE id_solicitudhe = {$idS} AND id_formulario_transporte ={$idT}";
		$rs = sqlsrv_query($_SESSION['con'], $query);
	}
	
	static function verificarPagado($id)
	{
		$query="SELECT * FROM formulario_transporte, transporte_pagado
				WHERE formulario_transporte.id = transporte_pagado.id_formulario_transporte
				AND formulario_transporte.id = {$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'], $query, $params, $options);
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
		$query="SELECT * FROM formulario_transporte, transporte_pagado
				WHERE formulario_transporte.id = transporte_pagado.id_formulario_transporte
				AND formulario_transporte.id = {$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'], $query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				echo "<script>alert('Esta solicitud ya esta pagada');</script>";
			}
			else
			{
				$fecha = date("Y-m-d");
				$query2="INSERT INTO transporte_pagado
						(id_formulario_transporte, fecha)
						VALUES
						({$id}, '{$fecha}')";
				sqlsrv_query($_SESSION['con'], $query2);
			}
		}
	}

	static function generarNoOficio()
	{
		$nOficio="TRS".date('y')."-";
		$query="SELECT MAX( id ) AS id
				FROM formulario_transporte";
		$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
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
	
	static function obtenerSolicitudHE($idT)
	{
		$query="SELECT solicitudhe.noOficio as no_oficio
				FROM solicitudhe, horaextra_transporte, formulario_transporte
				WHERE formulario_transporte.id = {$idT}
				AND horaextra_transporte.id_solicitudhe = solicitudhe.id
				AND horaextra_transporte.id_formulario_transporte = formulario_transporte.id";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'], $query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['no_oficio'];
			}
		}
	}
	
	static function comprobarAprobacion($idT)
	{
		$query="SELECT solicitudhe.noOficio as noOoficio
				FROM solicitudhe, formulario_transporte, horaextra_transporte, solicitudes_autorizadas
				WHERE formulario_transporte.id = {$idT}
				AND horaextra_transporte.id_formulario_transporte = formulario_transporte.id
				AND horaextra_transporte.id_solicitudhe = solicitudhe.id
				AND solicitudes_autorizadas.id_solicitud = solicitudhe.id
				AND tipo ='HoraExtra'
				AND autorizado=1";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'], $query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	static function obtenerOrdenamientos()
	{
		echo "
			<option value='empleado.nombre'>Nombre</option>
			<option value='cargo'>Cargo</option>
			<option value='departamento'>Departamento</option>
			<option value='fecha'>Fecha</option>
			<option value='pago'>Pago</option>
		";
	}

	static function validarHorasExtra($desde, $hasta, $usr)
	{
		$flag='';
		$query="SELECT s.id, s.noOficio as noOficio from solicitudhe s
				inner join solicitudes_autorizadas sa on sa.id_solicitud = s.id
				WHERE usr='{$usr}'
				AND fecha between '{$desde}' AND '{$hasta}'
				AND autorizado=1 and tipo='HoraExtra'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs=sqlsrv_query($_SESSION['con'], $query, $params, $options);

		if (sqlsrv_num_rows($rs)>0) {
			while ($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
				$queryV="SELECT h.id from historial_empleado h
				inner join solicitudhe s on s.id = h.id_she
				inner join horario hor on hor.id = h.id_horario
				WHERE s.id ={$fila['id']}";
				$rsV=sqlsrv_query($_SESSION['con'], $queryV, $params, $options);
				if(sqlsrv_num_rows($rsV)<=0){
					$flag.="{$fila['noOficio']} ";
					break;
				}
			}
		}
		else
		{
			$flag = "No se detectaron solicitudes creadas en ese rango";
		}
		return $flag;
	}
}
?>