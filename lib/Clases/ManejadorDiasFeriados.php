<?php 
class ManejadorDiasFeriados
{
	static function obtenerDiasFeriados()
	{
		$query="SELECT *
				FROM dias_feriados
				ORDER BY fecha";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				echo "	<tr class='tab_bg_2'>
							<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
							<td>{$fila['fecha']->format('d/m/Y')}</td>
							<td>{$fila['motivo']}</td>
						</tr>";
			}
		}
	}
}
?>