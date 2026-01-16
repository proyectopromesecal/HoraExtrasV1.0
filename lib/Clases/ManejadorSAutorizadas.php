<?php 
	class ManejadorSAutorizadas
	{
		static function obtenerFormulariosTransporte()
		{
			$query="SELECT TOP 35 formulario_transporte.id as id, formulario_transporte.fecha as fecha, formulario_transporte.no_oficio as no_oficio, solicitudhe.noOficio as HEnooficio 
					FROM formulario_transporte, solicitudhe, horaextra_transporte
					WHERE NOT 
					EXISTS (

					SELECT 1 
					FROM solicitudes_autorizadas
					WHERE solicitudes_autorizadas.id_solicitud = formulario_transporte.id and tipo='Transporte'
					)
					AND solicitudhe.id = horaextra_transporte.id_solicitudhe
					AND formulario_transporte.id = horaextra_transporte.id_formulario_transporte";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs =sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if($rs)
			{
				return $rs;
			}
		}
		
		static function obtenerSolicitudesHE()
		{
			$query="SELECT TOP 50 * 
					FROM solicitudhe
					WHERE NOT 
					EXISTS (

					SELECT 1 
					FROM solicitudes_autorizadas
					WHERE solicitudes_autorizadas.id_solicitud = solicitudhe.id and tipo='HoraExtra'
					)";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs =sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if($rs)
			{
				return $rs;
			}
		}
		
		static function obtenerTransporte()
		{
			$query="SELECT TOP 35 formulario_transporte.id as id, formulario_transporte.fecha as fecha, formulario_transporte.no_oficio as no_oficio, solicitudhe.noOficio as HEnooficio
					FROM solicitudes_autorizadas, formulario_transporte, solicitudhe, horaextra_transporte
					WHERE tipo='Transporte' and formulario_transporte.id = solicitudes_autorizadas.id_solicitud
					AND solicitudhe.id = horaextra_transporte.id_solicitudhe
					AND formulario_transporte.id = horaextra_transporte.id_formulario_transporte";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if($rs)
			{
				return $rs;
			}
		}
		
		static function obtenerHorasExtra()
		{
			$query="SELECT TOP 500 solicitudhe.id as id, solicitudhe.fecha as fecha, programado, autorizado, id_solicitud, solicitudhe.departamento as departamento, usr
					FROM solicitudes_autorizadas, solicitudhe
					WHERE tipo='HoraExtra' and solicitudhe.id = solicitudes_autorizadas.id_solicitud
					ORDER BY solicitudhe.fecha desc";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if($rs)
			{
				return $rs;
			}
		}
		
		static function obtenerDietaViatico()
		{
			$query="SELECT TOP 35 dv.id as id, dv.no_oficio as no_oficio, dv.fecha_creacion as fecha, dv.departamento as departamento, autorizado, dv.usr 
					FROM solicitudes_autorizadas saut
					inner join dietaviatico dv on dv.id = saut.id_solicitud
					WHERE tipo='Viatico' and dv.id = saut.id_solicitud
					AND dv.usr in (
						SELECT a.usuario
						FROM [horasextra].[dbo].[usuario] a
						inner join empleado b on a.empleado =  b.id
						where b.departamento in (
							SELECT b.id from  usuario c
							inner join empleado a on c.empleado = a.id
							inner join t_departamento b on a.departamento = b.id or a.departamento = b.subDepId 
							where c.usuario = '{$_SESSION['usuario']}'))";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if($rs)
			{
				return $rs;
			}
		}
		
		static function obtenerSolicitudesDV()
		{
			$query="SELECT TOP 35 * 
					FROM dietaviatico dv
					WHERE NOT 
					EXISTS 
					(
						SELECT 1 
						FROM solicitudes_autorizadas s
						WHERE s.id_solicitud = dv.id
						and tipo='Viatico' 
					) 
					AND dv.usr in (
						SELECT a.usuario
						FROM [horasextra].[dbo].[usuario] a
						inner join empleado b on a.empleado =  b.id
						where b.departamento in (
							select b.id from  usuario c
							inner join empleado a on c.empleado = a.id
							inner join t_departamento b on a.departamento = b.id or a.departamento = b.subDepId 
							where c.usuario = '{$_SESSION['usuario']}'))";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs =sqlsrv_query($_SESSION['con'], $query, $params, $options);
			if($rs)
			{
				return $rs;
			}
		}
		
		static function obtenerComentario($idS)
		{
			$query="SELECT comentario FROM solicitudes_autorizadas
					WHERE id_solicitud = {$idS}";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'], $query, $params, $options);
			$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			return $fila['comentario'];
		}


	}
?>