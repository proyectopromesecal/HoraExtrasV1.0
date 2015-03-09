<?php 
	class Usuario
	{
		private $id;
		private $usuario;
		private $pass;
		private $tipo;
		private $empleado;
		
		public function setUsuario($usuario)
		{
			$this->usuario = $usuario;
		}
		
		public function setPass($pass)
		{
			$this->pass = $pass;
		}
		
		public function setTipo($tipo)
		{
			$this->tipo = $tipo;
		}
		
		public function setID($id)
		{
			$this->id = $id;
		}
		
		public function setEmpleado($empleado)
		{
			$this->empleado = $empleado;
		}
		
		public function getUsuario()
		{
			return $this->usuario;
		}
		
		public function getPass()
		{
			return $this->pass;
		}
		
		public function getTipo()
		{
			return $this->tipo;
		}
		
		public function getID()
		{
			return $this->id;
		}	
		
		public function getEmpleado()
		{
			return $this->empleado;
		}

		function guardar()
		{
			if ($this->getID() > 0)
			{
				$query="UPDATE usuario SET
					usuario = '{$this->getUsuario()}',
					pass = '{$this->getPass()}',
					tipo = '{$this->getTipo()}',
					empleado = '{$this->getEmpleado()}'
					WHERE
					id = '{$this->getId()}'";
				sqlsrv_query($_SESSION['con'],$query);
			}
			else
			{
				$query="INSERT INTO usuario 
					(usuario, pass, tipo, empleado)
					VALUES
					('{$this->getUsuario()}', '{$this->getPass()}', '{$this->getTipo()}', '{$this->getEmpleado()}')";
				sqlsrv_query($_SESSION['con'],$query);
				$queryScope = "select SCOPE_IDENTITY() scope";
				$rs = sqlsrv_query($_SESSION['con'],$queryScope);
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				$this->setID($fila['scope']);
			}		
		}
		
		function cargar()
		{
			$query="SELECT * FROM usuario WHERE id = '{$this->id}'";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
			
			if(sqlsrv_num_rows($rs) > 0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				$this->setID($fila["id"]);
				$this->setUsuario($fila["usuario"]);
				$this->setPass($fila["pass"]);
				$this->setTipo($fila["tipo"]);
				$this->setEmpleado($fila["empleado"]);
			}
		}
		
		function eliminar($codigo)
		{
			$query="DELETE FROM usuario
					WHERE id = '{$codigo}'";
					sqlsrv_query($_SESSION['con'],$query);
		}		
	}
?>