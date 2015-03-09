<?php
	class SolicitudAutorizada
	{
		private $id;
		private $id_solicitud;
		private $noOficio;
		private $autorizado;
		private $comentario;
		private $tipo;
		private $fecha_c;
		
		public function getId(){
			return $this->id;
		}

		public function setId($id){
			$this->id = $id;
		}

		public function getId_solicitud(){
			return $this->id_solicitud;
		}

		public function setId_solicitud($id_solicitud){
			$this->id_solicitud = $id_solicitud;
		}

		public function getNoOficio(){
			return $this->noOficio;
		}

		public function setNoOficio($noOficio){
			$this->noOficio = $noOficio;
		}

		public function getAutorizado(){
			return $this->autorizado;
		}

		public function setAutorizado($autorizado){
			$this->autorizado = $autorizado;
		}

		public function getComentario(){
			return $this->comentario;
		}

		public function setComentario($comentario){
			$this->comentario = $comentario;
		}

		public function getTipo(){
			return $this->tipo;
		}

		public function setTipo($tipo){
			$this->tipo = $tipo;
		}
	
		public function getFecha_c(){
			return $this->fecha_c;
		}

		public function setFecha_c($fecha_c){
			$this->fecha_c = $fecha_c;
		}
		
		function guardar()
		{
			$id=0;
			if ($this->getId() > 0)
			{
				$query="UPDATE solicitudes_autorizadas SET
						id_solicitud = {$this->id_solicitud},
						noOficio = '{$this->noOficio}',
						autorizado = {$this->autorizado},
						comentario = '{$this->comentario}',
						tipo = '{$this->tipo}'
						WHERE
						id = '{$this->id}'";
				sqlsrv_query($_SESSION['con'],$query);
			}
			else
			{	
				$queryCheck="SELECT * from solicitudes_autorizadas where id_solicitud = {$this->getId_Solicitud()}";
				$params = array();
				$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
				$rsCheck = sqlsrv_query($_SESSION['con'],$queryCheck, $params, $options);
				if (sqlsrv_num_rows($rsCheck)==0)
				{
					$query="INSERT INTO solicitudes_autorizadas 
							(id_solicitud, noOficio, autorizado, comentario, tipo, fecha_c)
							VALUES
							({$this->getId_Solicitud()},'{$this->getNoOficio()}', {$this->getAutorizado()}, '{$this->getComentario()}', '{$this->getTipo()}', '{$this->getFecha_c()}')";
					sqlsrv_query($_SESSION['con'],$query);
				    $res = sqlsrv_query($_SESSION['con'],"SELECT @@identity AS id"); 
				    if ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) { 
				        $id = $row["id"]; 
				    } 
					$this->setID($id);		
				}
			}
		}
		
		function cargar($tipo)
		{
			$query="SELECT * FROM solicitudes_autorizadas WHERE id_solicitud = {$this->getId()} and tipo = '{$tipo}'";
			$params = array();
			$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
			
			if(sqlsrv_num_rows($rs) > 0)
			{
				$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
				$this->setId($fila["id"]);
				$this->setId_Solicitud($fila["id_solicitud"]);
				$this->setNoOficio($fila["noOficio"]);
				$this->setAutorizado($fila["autorizado"]);
				$this->setComentario($fila["comentario"]);
				$this->setTipo($fila["tipo"]);
			}
		}
		
		function eliminar($codigo, $tipo)
		{
			$query="DELETE FROM solicitudes_autorizadas
					WHERE id = '{$codigo}' and tipo ='{$tipo}'";
			sqlsrv_query($_SESSION['con'],$query);
		}
	}
?>