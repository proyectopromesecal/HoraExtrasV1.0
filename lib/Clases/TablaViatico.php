<?php 
class TablaViatico
{
	private	$id;
	private	$posicion;
	private $grupo;
	private	$desayuno;
	private	$almuerzo;
	private	$cena;
	private $dormitorio;

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getPosicion(){
		return $this->posicion;
	}

	public function setPosicion($posicion){
		$this->posicion = $posicion;
	}
	
	public function getGrupo(){
		return $this->grupo;
	}

	public function setGrupo($grupo){
		$this->grupo = $grupo;
	}

	public function getDesayuno(){
		return $this->desayuno;
	}

	public function setDesayuno($desayuno){
		$this->desayuno = $desayuno;
	}

	public function getAlmuerzo(){
		return $this->almuerzo;
	}

	public function setAlmuerzo($almuerzo){
		$this->almuerzo = $almuerzo;
	}

	public function getCena(){
		return $this->cena;
	}

	public function setCena($cena){
		$this->cena = $cena;
	}
	
	public function getDormitorio(){
		return $this->dormitorio;
	}

	public function setDormitorio($dormitorio){
		$this->dormitorio = $dormitorio;
	}
	
	function guardar()
	{
		if ($this->getId() > 0)
		{
			$query="UPDATE posicion_viatico SET
				posicion = '{$this->getPosicion()}',
				grupo = {$this->getGrupo()},
				desayuno = {$this->getDesayuno()},
				almuerzo = {$this->getAlmuerzo()},
				cena = {$this->getCena()},
				dormitorio ={$this->getDormitorio()}
				WHERE
				id = {$this->getId()}";
			sqlsrv_query($_SESSION['con'],$query);
		}
		else
		{
			$query="INSERT INTO posicion_viatico 
				(posicion, grupo, desayuno, almuerzo, cena, dormitorio)
				VALUES
				('{$this->getPosicion()}', {$this->getGrupo()},{$this->getDesayuno()}, {$this->getAlmuerzo()}, {$this->getCena()}, {$this->getDormitorio()})";
			sqlsrv_query($_SESSION['con'],$query);
			$queryScope = "select SCOPE_IDENTITY() scope";
			$rs = sqlsrv_query($_SESSION['con'],$queryScope);
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->setID($fila['scope']);
		}		
	}
	
	function cargar()
	{
		$query="SELECT * FROM posicion_viatico WHERE id = {$this->id}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
		
		if(sqlsrv_num_rows($rs) > 0)
		{
			$fila = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
			$this->setId($fila["id"]);
			$this->setPosicion($fila["posicion"]);
			$this->setGrupo($fila["grupo"]);
			$this->setDesayuno($fila["desayuno"]);
			$this->setAlmuerzo($fila["almuerzo"]);
			$this->setCena($fila["cena"]);
			$this->setDormitorio($fila["dormitorio"]);
		}
	}
	
	function eliminar($codigo)
	{
		$query="DELETE FROM posicion_viatico
				WHERE id = {$codigo}";
				sqlsrv_query($_SESSION['con'],$query);
	}	
}
?>