<?php 

	class SolicitudHE
	{
		private $id;
		private $noOficio;
		private $objetivo;
		private $descripcion;
		private $alcance;
		private $programado;
		private $tiempoEstimado;
		private $fecha;
		private $fecha_creacion;
		private $departamento;
		private $hora;
		private $usuario;
		
		
		function setUsuario($usuario)
		{
			$this->usuario = $usuario;
		}
		
		function getUsuario()
		{
			return $this->usuario;
		}		
		
		function setID($id)
		{
			$this->id = $id;
		}
		
		function setNoOficio($noOficio)
		{
			$this->noOficio = $noOficio;
		}
		function setObjetivo($objetivo)
		{
			$this->objetivo = $objetivo;
		}
		
		function setDescripcion($descripcion)
		{
			$this->descripcion = $descripcion;
		}
		
		function setAlcance($alcance)
		{
			$this->alcance = $alcance;
		}
		
		function setProgramado($programado)
		{
			$this->programado = $programado;
		}
		
		function setTiempoEstimado($tiempoEstimado)
		{
			$this->tiempoEstimado = $tiempoEstimado;
		}
		
		function setFecha($fecha)
		{
			$this->fecha = $fecha;
		}
		
		function setFechaCreacion($fechaC)
		{
			$this->fecha_creacion = $fechaC;
		}		
		
		function setDepartamento($departamento)
		{
			$this->departamento = $departamento;
		}
		
		function setHora($hora)
		{
			$this->hora = $hora;
		}
		
		function getID()
		{
			return $this->id;
		}
		
		function getNoOficio()
		{
			return $this->noOficio;
		}
		
		function getObjetivo()
		{
			return $this->objetivo;
		}
		
		function getDescripcion()
		{
			return $this->descripcion;
		}
		
		function getAlcance()
		{
			return $this->alcance;
		}
		
		function getProgramado()
		{
			return $this->programado;
		}
		
		function getTiempoEstimado()
		{
			return $this->tiempoEstimado;
		}
		
		function getFecha()
		{
			return $this->fecha;
		}
		
		function getFechaCreacion()
		{
			return $this->fecha_creacion;
		}
		
		function getDepartamento()
		{
			return $this->departamento;
		}
		
		function getHora()
		{
			return $this->hora;
		}
		
		function guardar()
		{
			$id=0;
			if ($this->getID() > 0)
			{
				$query="UPDATE solicitudHE SET
					noOficio = '{$this->noOficio}',
					objetivo = '{$this->objetivo}',
					descripcion = '{$this->descripcion}',
					alcance = '{$this->alcance}',
					programado = '{$this->programado}',
					tiempoEstimado = '{$this->tiempoEstimado}',
					fecha = '{$this->fecha}',
					departamento ='{$this->departamento}'
					WHERE
					id = '{$this->id}'";
				sqlsrv_query($_SESSION['con'],$query);
			}
			else
			{
				$query="INSERT INTO solicitudHE 
						(noOficio, objetivo, descripcion, alcance, programado, tiempoEstimado, fecha, fecha_creacion,departamento, hora, usr)
						VALUES
						('{$this->noOficio}', '{$this->objetivo}', '{$this->descripcion}', '{$this->alcance}', '{$this->programado}', '{$this->tiempoEstimado}', '{$this->fecha}', '{$this->fecha_creacion}', '{$this->departamento}', '{$this->hora}', '{$this->usuario}')";
				sqlsrv_query($_SESSION['con'],$query);

			    $res = sqlsrv_query($_SESSION['con'],"SELECT @@identity AS id"); 
			    if ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) { 
			        $id = $row["id"]; 
			    } 
				$this->setID($id);
			}		
		}
		
		function cargar()
		{
			$query="SELECT * FROM solicitudHE WHERE  id = '{$this->id}'";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );	
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			
			if(sqlsrv_num_rows($rs) > 0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				$this->id = $fila["id"];
				$this->noOficio = $fila["noOficio"];
				$this->objetivo = $fila["objetivo"];
				$this->descripcion = $fila["descripcion"];
				$this->alcance = $fila["alcance"];
				$this->programado = $fila["programado"];
				$this->tiempoEstimado = $fila["tiempoEstimado"];
				$this->fecha = $fila["fecha"];
				$this->departamento = $fila["departamento"];
				$this->usuario = $fila["usr"];
			}
		}
		
		function eliminar($codigo)
		{
			$query="DELETE FROM solicitudHE
					WHERE id = '{$codigo}'";
					sqlsrv_query($_SESSION['con'], $query);
		}
	}
?>