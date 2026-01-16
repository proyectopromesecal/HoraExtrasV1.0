<?php 

class Empleado
{
	private $id;
	private $nombre;
	private $departamento;
	private $cedula;
	private $sueldo;
	private $cargo;
	private $codigo_empleado;
	private $horario_especial;
	private $tipo_viatico;
	private $nivel;
	
	function setID($id)
	{
		$this->id = $id;
	}
	
	function setNombre($nombre)
	{
		$this->nombre = $nombre;
	}
	
	function setDepartamento($departamento)
	{
		$this->departamento = $departamento;
	}
	
	function setCedula($cedula)
	{
		$this->cedula= $cedula;
	}	
	
	function setSueldo($sueldo)
	{
		$this->sueldo = $sueldo;
	}
	
	function setCargo($cargo)
	{
		$this->cargo = $cargo;
	}
	
	function setCodigoEmpleado($cod)
	{
		$this->codigo_empleado = $cod;
	}
	
	function setHorarioEspecial($especial)
	{
		$this->horario_especial = $especial;
	}
	
	function setTipoViatico($viatico)
	{
		$this->tipo_viatico = $viatico;
	}
	
	function getID()
	{
		return $this->id;
	}

	function getNombre()
	{
		return $this->nombre;
	}
	
	function getDepartamento()
	{
		return $this->departamento;
	}
	
	function getCedula()
	{
		return $this->cedula;
	}
	
	function getSueldo()
	{
		return $this->sueldo;
	}
	
	function getCargo()
	{
		return $this->cargo;
	}
	
	function getCodigoEmpleado()
	{
		return $this->codigo_empleado;
	}
	
	function getHorarioEspecial()
	{
		return $this->horario_especial;
	}
	
	function getTipoViatico()
	{
		return $this->tipo_viatico;
	}

	function setNivel($nivel)
	{
		$this->nivel = $nivel;
	}
	
	function getNivel()
	{
		return $this->nivel;
	}
	
	function guardar()
	{
		if ($this->getID() > 0)
		{
			$query="UPDATE empleado SET
				nombre = '{$this->nombre}',
				departamento = '{$this->departamento}',
				cargo = '{$this->cargo}',
				cedula = '{$this->cedula}',
				codigo_empleado = '{$this->codigo_empleado}',
				sueldo = '{$this->sueldo}',
				horario_especial = {$this->horario_especial},
				tipo_viatico ={$this->tipo_viatico},
				nivel = {$this->nivel}
				WHERE
				id = '{$this->id}'";
			sqlsrv_query($_SESSION['con'],$query);
		}
		else
		{
			$query="INSERT INTO empleado 
					(nombre, departamento, cedula, sueldo, cargo, codigo_empleado, horario_especial, tipo_viatico, nivel)
					VALUES
					('{$this->nombre}', '{$this->departamento}', '{$this->cedula}', '{$this->sueldo}', '{$this->cargo}', '{$this->codigo_empleado}', {$this->horario_especial}, {$this->tipo_viatico},{$this->nivel})";
			sqlsrv_query($_SESSION['con'],$query);
			
			$queryScope = "select SCOPE_IDENTITY() scope";
			$rs = sqlsrv_query($_SESSION['con'],$queryScope);
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			
			$this->setID($fila['scope']);
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM empleado WHERE  id = '{$this->id}'";
		
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs) > 0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->id = $fila["id"];
			$this->nombre = $fila["nombre"];
			$this->departamento = $fila["departamento"];
			$this->cargo = $fila["cargo"];
			$this->cedula = $fila["cedula"];
			$this->codigo_empleado = $fila["codigo_empleado"];
			$this->sueldo = $fila["sueldo"];
			$this->horario_especial = $fila["horario_especial"];
			$this->tipo_viatico = $fila["tipo_viatico"];
			$this->nivel = $fila["nivel"];
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM empleado
				WHERE id = '{$codigo}'";
				sqlsrv_query($_SESSION['con'],$query);
	}
}

?>