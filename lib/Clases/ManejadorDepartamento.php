<?php 
class ManejadorDepartamento
{
	static function obtenerDepartamentos()
	{
		$query="SELECT *
				FROM t_departamento";
				
		$rs = sqlsrv_query($_SESSION['con'],$query);
		
		if($rs)
		{
			while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "	<tr class='tab_bg_2'>
							<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
							<td>{$fila['nombre']}</td>
						</tr>";
			}
		}
	}
	
	static function agregarCargos($idD,$arrayC)
	{
		foreach($arrayC as $valor)
		{
			$query = "INSERT INTO departamentos
			(id_cargo, id_departamento)
			VALUES
			({$valor},{$idD})";
			
			$rs = sqlsrv_query($_SESSION['con'],$query);
			
			if(!$rs)
			{
				echo mysql_error($rs);
			}
		}
	}
	
	static function eliminarCargos($idD, $arrayC)
	{
		foreach($arrayC as $valor)
		{
			$query = "DELETE FROM departamentos
					  WHERE id_departamento = {$idD} 
					  AND id_cargo ={$valor}";
					  
			$rs = sqlsrv_query($_SESSION['con'],$query);
			
			if(!$rs)
			{
				echo mysql_error($rs);
			}
		}
	}
	
	static function obtenerCargosDpto($id)
	{
		$query="SELECT t_cargo.nombre as cargo, t_cargo.id as id_cargo
				FROM t_cargo, t_departamento, departamentos
				WHERE t_departamento.id = {$id}
				AND departamentos.id_cargo = t_cargo.id
				AND departamentos.id_departamento = t_departamento.id";
		
		$rs = sqlsrv_query($_SESSION['con'],$query);
		
		if($rs)
		{
			return $rs;
		}
	}
	
	static function obtenerNombre($id)
	{
		$query="SELECT nombre from departamento where id={$id}";
		
		$rs = sqlsrv_query($_SESSION['con'],$query);
		$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
		
		return $fila['nombre'];
	}
}
?>