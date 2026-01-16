<?php
require('fpdf/fpdf.php');
include('lib/motor.php');
if(!isset($_SESSION)){
	session_start();
}
global $temp;
$total;$tb;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

if(isset($_SESSION['id']))
{
	$mes = date('m');
	$anio = date('Y');
	$query="SELECT * from (select a.id, a.nombre , cedula, e.nombre as cargo,
			ROUND( SUM( pago ) , 2 )  pago, sueldo *0.30  porciento  
			from empleado a
			inner join historial_empleado c on a.id = c.id_empleado
			inner join t_cargo e on e.id = a.cargo 
			inner join horario b on a.id = b.id_empleado and b.id = c.id_horario 
			AND MONTH( b.fecha ) = {$mes} AND YEAR(b.fecha) = {$anio}
			inner join grupo_empleados grupo on grupo.id_empleado = a.id 
			inner join usuario us on grupo.id_secretaria =us.id
			WHERE us.id ={$_SESSION['id']}
			GROUP BY a.cedula,a.id,a.nombre,e.nombre, a.sueldo) x
			order by x.nombre";
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
	
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$temp[] = $fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['pago'].";".$fila['porciento'];
		}		
	}
}
else
{
	header('Location:seleccionados.php');
}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		$this->SetLeftMargin(7);
		// Logo
		$this->Image('imagenes/logo-promosecal.png',14,10,50, 20);
		$this->Image('imagenes/farmacia-logo.png',145,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','I',11);
		$this->Ln(20);
		// Movernos a la derecha
		// Título
		$this->SetFont('Arial','B',15);
		$this->Cell(195,25,'Reporte de acumulado en horas extraordinarias periodo '. date('Y-m'),0,0,'C');			
		// Salto de línea
		$this->Ln(20);
	}

	// Pie de página
	function Footer()
	{
		// Posición: a 1,5 cm del final
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Número de página
		$this->Cell(0,10,date('d/m/Y'), 0,0,'C');
		$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function ImprovedTable($header, $data)
	{
		$this->SetLeftMargin(10);
		if(!empty($data))
		{
			// Anchuras de las columnas
			$w = array
			(0 => 65,
			 1 => 15,
			 2 => 60,
			 3 => 25,
			 4 => 25,);
			// Cabeceras
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFillColor(220,235,255);
			$this->SetTextColor(0);
			$this->SetFont('Times','B',7);
			// Datos
			foreach($data as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				if($row == end($data))
				{
					$this->Cell($w[0],8,$columna[0],1,'LR');
					$this->Cell($w[1],8,$columna[1],1,'LR');
					$this->Cell($w[2],8,$columna[2],1,'LR');
					$this->Cell($w[3],8,$columna[3],1,'LR');			
					$this->Cell($w[4],8,$columna[4],1,'LR');	
					$this->Ln();
				}
				else
				{
					$this->Cell($w[0],8,$columna[0],1,'LR');
					$this->Cell($w[1],8,$columna[1],1,'LR');
					$this->Cell($w[2],8,$columna[2],1,'LR');
					$this->Cell($w[3],8,$columna[3],1,'LR');			
					$this->Cell($w[4],8,$columna[4],1,'LR');			
					$this->Ln();			
				}				
			}
			// Línea de cierre
			$this->Cell(array_sum($w),0,'','T');			
		}
	}
}
// Creación del objeto de la clase heredada
$header = array('Nombre', 'Cedula','Cargo', 'Acumulado','Limite');
$pdf=new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->ImprovedTable($header, $temp);
$pdf->Output();
?>