<?php 
class ManejadorTablaViatico
{
	static function obtenerViaticos()
	{
		$query="SELECT posicion_viatico.id as id, posicion, desayuno, almuerzo, cena, dormitorio, grupo_viatico.nombre as grupo
				FROM posicion_viatico, grupo_viatico WHERE grupo_viatico.id = posicion_viatico.grupo";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "	<tr class='tab_bg_2'>
							<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
							<td>{$fila['posicion']}</td>
							<td>{$fila['grupo']}</td>
							<td>{$fila['desayuno']}</td>
							<td>{$fila['almuerzo']}</td>
							<td>{$fila['cena']}</td>
							<td>{$fila['dormitorio']}</td>
						</tr>";
			}
		}
	}
	
	static function obtenerGrupo($preferido)
	{
		$query="SELECT * FROM grupo_viatico";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				if($preferido==0)
				{
					echo "
						<option value='{$fila['id']}'>{$fila['nombre']}</option>
					";
					
				}
				else
				{
					if($preferido==$fila['id'])
					{
						echo "
							<option value='{$fila['id']}'>{$fila['nombre']}</option>
						";							
					}
				}

			}
		}
	}
}
?>