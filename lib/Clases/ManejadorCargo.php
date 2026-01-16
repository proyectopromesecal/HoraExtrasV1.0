<?php
class ManejadorCargo
{
	static function obtenerDepartamentos($preferido)
	{
		$query="SELECT t_departamento.nombre as nombre, t_departamento.id as id
				FROM t_departamento";
				
		$rs = sqlsrv_query($_SESSION['con'],$query);
		if($rs)
		{
			echo "<option value=''>No Tiene</option>";
			while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				if($fila['id'] == $preferido)
				{
					echo "<option value='{$fila['id']}' selected>{$fila['nombre']}</option>";
				}
				echo "<option value='{$fila['id']}'>{$fila['nombre']}</option>";
			}
		}
	}
	
	static function obtenerCargos()
	{
		$query="SELECT *
				FROM t_cargo";
		$rs = sqlsrv_query($_SESSION['con'],$query);
		if($rs)
		{
			return $rs;
		}
	}
	
	static function obtenerCargosDep($idD)
	{
		$query="SELECT t_cargo.id as id, t_cargo.nombre as nombre
				FROM t_cargo, t_departamento, departamentos
				WHERE t_departamento.id = {$idD}
				AND departamentos.id_cargo = t_cargo.id
				AND departamentos.id_departamento = t_departamento.id";
		$rs = sqlsrv_query($_SESSION['con'],$query);
		if($rs)
		{
			while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "<option value='{$fila['id']}'>{$fila['nombre']}</option>";
			}
		}
	}
	
	static function obtenerCargo($idC=0)
	{
		$query="SELECT id, nombre
				FROM t_cargo
				ORDER BY nombre";
		$rs = sqlsrv_query($_SESSION['con'],$query);
		if($rs)
		{
			while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)){
				if ($idC==$fila['id']) {
					echo "<option selected value='{$fila['id']}'>{$fila['nombre']}</option>";
				}
				echo "<option value='{$fila['id']}'>{$fila['nombre']}</option>";
			}
		}
	}
	
	static function evaluarSueldo($idC, $sueldo)
	{
		$query="SELECT * FROM t_cargo WHERE id={$idC}";
		
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				if($sueldo < $fila['sueldo_min'] || $sueldo > $fila['sueldo_max'])
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
}
?>