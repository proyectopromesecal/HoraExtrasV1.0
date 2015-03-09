<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');

global $tabla;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
if(isset($_GET['f']))
{
	$query="SELECT empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, convert(varchar, fecha,  103 ) AS fecha, convert(varchar, horadeentrada,  108 ) AS horadeentrada, convert(varchar, horadesalida,  108 ) AS horadesalida
			FROM empleado, horario, t_cargo, t_departamento
			WHERE fecha = '{$_GET['f']}'
			AND empleado.id = horario.id_empleado
			AND t_cargo.id = empleado.cargo
			AND t_departamento.id = empleado.departamento
			order by departamento";
	$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$tabla[]=$fila['nombre'].";".$fila['departamento'].";".$fila['fecha'].";".$fila['horadeentrada'].";".$fila['horadesalida'];			
		}
	}
}
else
{
	header('Location:generadorReportes.php');
}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		$this->Image('../imagenes/logo-promosecal.png',12,11,50, 20);
		$this->Image('../imagenes/farmacia-logo.png',150,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(20);
		// Movernos a la derecha
		// Título
		$this->Cell(190, 15, "Reporte de Entrada y Salida",0,0, 'C');
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
	
	function Body($tabla)
	{
		$this->setLeftMargin(10);
		//tabla
		if(!empty($tabla))
		{			
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			$this->SetFont('Times','B',11);
			$w = array
			(0 => 62,
			 1 => 80,
			 2 => 15,
			 3 => 15,
			 4 => 15);
			 
			$header= array('Nombre', 'Departamento', 'Fecha', 'Entrada', 'Salida');
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',6);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			// Datos
			foreach($tabla as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				$this->Cell($w[0],6,$columna[0],1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->Cell($w[2],6,$columna[2],1,0,'C');
				$this->Cell($w[3],6,$columna[3],1,0,'C');
				$this->Cell($w[4],6,$columna[4],1,0,'C');			
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene empleados en la fecha seleccionada.", 0,1);
		}
	}
}
// Creación del objeto de la clase heredada
$pdf=new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Body($tabla);
$pdf->Output();
?>