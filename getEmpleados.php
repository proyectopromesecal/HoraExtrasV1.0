<?php
	include("lib/motor.php");
	if(isset($_POST['valor']) && !empty($_POST['valor']))
	{
		$valor = $_POST['valor'];
		$rs = Manejador::obtenerEmpleados($valor);
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			echo "
			<tr>
				<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
				<td>{$fila['cedula']}</td>
				<td>{$fila['nombre']}</td>
				<td>{$fila['cargo']}</td>
				<td>{$fila['departamento']}</td>
				<td>{$fila['sueldo']}</td>
			</tr>";
		}
	}
