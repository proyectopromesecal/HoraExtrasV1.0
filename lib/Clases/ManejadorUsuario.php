<?php
class ManejadorUsuario
{
	static function obtenerTipos($preferido)
	{
		if($preferido!='')
		{
			echo "<option value='{$preferido}'>{$preferido}</option>";
		}
		echo "<option value='Secretaria'>Secretaria</option>
			  <option value='Administrador'>Administrador</option>
			  <option value='Autorizador'>Autorizador</option>
			  <option value='Viewer'>Viewer</option>
			  <option value='Pago'>Pago</option>
			  <option value='Asistente'>Asistente</option>";
	}
	
	static function obtenerUsuarios()
	{
		$query=" SELECT usuario.id as id, usuario, tipo, empleado.nombre as nombre
			   FROM usuario, empleado
			   WHERE empleado.id = usuario.empleado
			   AND tipo <> 'SuperAdmin'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		return $rs;
	}
	
	static function obtenerEmpleados($preferido)
	{
		$query=" SELECT id, nombre
			   FROM empleado
			   WHERE nombre <> 'Admin'
			   Order By nombre";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				if($preferido == $fila['id'])
				{
					echo "<option value='{$fila['id']}' selected>{$fila['nombre']}</option>";
					$preferido=0;
				}
				echo "<option value='{$fila['id']}'>{$fila['nombre']}</option>";
			}
		}
	}
	
	static function obtenerDepartamento($idE)
	{
		$query="SELECT t_departamento.nombre as departamento
				FROM empleado, t_departamento
				WHERE t_departamento.id = empleado.departamento
				AND empleado.id ={$idE}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['departamento'];
			}
		}
	}
	
	static function obtenerTiposUsuarios()
	{
		$a = array();
		$query="SELECT distinct tipo 
				FROM usuario";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$a[]= $fila['tipo'];
			}
			return $a;
		}
	}

	static function crearSesion($usuario, $usr='')
	{
		$query="SELECT tipo, empleado
				FROM usuario
				WHERE ((usuario = '{$usuario}'))";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs)>0) 
		{
			$fila = sqlsrv_fetch_array( $rs, SQLSRV_FETCH_ASSOC);
			$_SESSION['usuario'] = $usuario;
			$_SESSION['tipo']= $fila['tipo'];
			
			if(strcmp("SuperAdmin", $_SESSION['tipo'])==0)
			{
				if (empty($usr)) 
				{
					$_SESSION['dpto'] = 'todos';
					$_SESSION['id']= ManejadorUsuario::obtenerIdSe($_SESSION['usuario']);	
				}
				else
				{
					$_SESSION['usuario'] = $usr;
					$_SESSION['id']= ManejadorUsuario::obtenerIdSe($_SESSION['usuario']);	
					$_SESSION['tipo']= ManejadorUsuario::obtenerTipo($_SESSION['id']);
					$depto = ManejadorUsuario::obtenerDepartamento(ManejadorUsuario::obtenerIdEmpleado($_SESSION['id']));
					$_SESSION['dpto'] = utf8_decode($depto);
				}
			}
			else
			{
				$_SESSION['dpto']= ManejadorUsuario::obtenerDepartamento($fila['empleado']);
				$_SESSION['id']= ManejadorUsuario::obtenerIdSe($usuario);
			}		
		}
	}

	static function obtenerIdSe($usuario)
	{
		$query="SELECT id
				FROM usuario
				WHERE usuario ='{$usuario}'";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['id'];
			}
		}
	}

	static function obtenerIdEmpleado($id)
	{
		$query="SELECT empleado
				FROM usuario
				WHERE id= {$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['empleado'];
			}
		}
	}

	static function obtenerTipo($id)
	{
		$query="SELECT tipo
				FROM usuario
				WHERE id= {$id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			if(sqlsrv_num_rows($rs)>0)
			{
				$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				return $fila['tipo'];
			}
		}
	}

	static function obtenerUsuariosSlc()
	{
		$rs = ManejadorUsuario::obtenerUsuarios();
		if ($rs)
		{
			echo "<option value=''>None</option>";
			while ($fila= sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
				echo "<option value='{$fila['usuario']}'>{$fila['usuario']}</option>";
			}
		}
	}
}
?>