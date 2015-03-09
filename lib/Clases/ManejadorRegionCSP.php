<?php
class ManejadorRegionCSP
{
	static function obtenerRegiones($preferido=0)
	{
		$query="SELECT t_cargo.id as id, t_cargo.nombre as nombre
				FROM t_cargo, t_departamento, departamentos
				WHERE t_departamento.id = {$idD}
				AND departamentos.id_cargo = t_cargo.id
				AND departamentos.id_departamento = t_departamento.id";
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
	
	static function obtenerCSP($preferido=0)
	{
		$query="SELECT centro_salud.id as id, centro_salud.nombre as nombre
				FROM centro_salud, region
				WHERE t_departamento.id = {$idD}
				AND departamentos.id_cargo = t_cargo.id
				AND departamentos.id_departamento = t_departamento.id";
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
	
	static function obtenerCentrosSlc()
	{
		$query="SELECT centro_salud.id AS id, centro_salud.nombre AS centro, region.nombre AS region
				FROM centro_salud, region
				WHERE centro_salud.id_region = region.id
				ORDER BY centro_salud.nombre asc";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "<option value='{$fila['id']}'>{$fila['centro']} - {$fila['region']}</option>";
			}
		}
	}
}