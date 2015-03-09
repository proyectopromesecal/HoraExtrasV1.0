<?php 
	class ManejadorSAutorizadas
	{
		static function obtenerFormulariosTransporte()
		{
			$query="SELECT formulario_transporte.id as id, formulario_transporte.fecha as fecha, formulario_transporte.no_oficio as no_oficio, solicitudhe.noOficio as HEnooficio 
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
			$query="SELECT * 
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
			$query="SELECT formulario_transporte.id as id, formulario_transporte.fecha as fecha, formulario_transporte.no_oficio as no_oficio, solicitudhe.noOficio as HEnooficio
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
			$query="SELECT solicitudhe.id as id, solicitudhe.fecha as fecha, programado, autorizado, id_solicitud, solicitudhe.departamento as departamento, usr
					FROM solicitudes_autorizadas, solicitudhe
					WHERE tipo='HoraExtra' and solicitudhe.id = solicitudes_autorizadas.id_solicitud";
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
			$query="SELECT dietaviatico.id as id, dietaviatico.no_oficio as no_oficio, dietaviatico.fecha_creacion as fecha, dietaviatico.departamento as departamento, autorizado, dietaviatico.usr 
					FROM solicitudes_autorizadas, dietaviatico
					WHERE tipo='Viatico' and dietaviatico.id = solicitudes_autorizadas.id_solicitud";
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
			$query="SELECT * 
					FROM dietaviatico
					WHERE NOT 
					EXISTS (

					SELECT 1 
					FROM solicitudes_autorizadas
					WHERE solicitudes_autorizadas.id_solicitud = dietaviatico.id
					and tipo='Viatico'
					)";
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