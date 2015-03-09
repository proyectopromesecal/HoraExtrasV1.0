<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
set_time_limit(0);
global $tabla;
	
	if(isset($_GET['fi']) && isset($_GET['ff']))
	{
		$query="
				select  a.nombre,a.cedula, c.area , sum(b.pago) pago from empleado a
				inner join pago_transporte b on a.id = b.id_empleado
				inner join formulario_transporte c on b.id_formulario_transporte = c.id and  c.fecha
																	  BETWEEN  '{$_GET['fi']}' AND  '{$_GET['ff']}'
				inner join horaextra_transporte e on e.id_formulario_transporte = c.id
				inner join solicitudhe f on e.id_solicitudhe = f.id
				inner join solicitudes_autorizadas g on f.id = g.id_solicitud and g.tipo = 'HoraExtra' and g.autorizado = 1

				inner join horario d on a.id = d.id_empleado  and  d.fecha = f.fecha and if(validarNoLaboral(f.fecha) = 0, d.horadesalida >= '17:00:00' , d.horadesalida > '01:00:00' )

				where a.id not in(1255, 146, 124, 102, 136)

				
				group by a.cedula, a.nombre
				order by a.nombre
				";
		$rs = mysql_query($query);
		if($rs)
		{
			while($fila=mysql_fetch_assoc($rs))
			{
				$tabla[]=$fila['nombre'].";".$fila['cedula'].";".$fila['area'].";".$fila['pago'];
			}
		}
		$query="select ROUND(sum(pago),2) totalpago from empleado a
				inner join pago_transporte b on a.id = b.id_empleado
				inner join formulario_transporte c on b.id_formulario_transporte = c.id and  c.fecha
																	  BETWEEN  '{$_GET['fi']}' AND  '{$_GET['ff']}'
				inner join horaextra_transporte e on e.id_formulario_transporte = c.id
				inner join solicitudhe f on e.id_solicitudhe = f.id
				inner join solicitudes_autorizadas g on f.id = g.id_solicitud and g.tipo = 'HoraExtra' and g.autorizado = 1

				inner join horario d on a.id = d.id_empleado  and  d.fecha = f.fecha and if(validarNoLaboral(f.fecha) = 0, d.horadesalida >= '17:00:00' , d.horadesalida > '01:00:00' )

				where a.id not in(1255, 146, 124, 102, 136)";
		$rs=mysql_query($query);
		if($rs)
		{
			$fila=mysql_fetch_assoc($rs);
			$tabla[]=$fila['totalpago'];
		}
	}
	else
	{
		header('Location:reporteTransporte.php');
	}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		$this->Image('../imagenes/logo-promosecal.png',15,10,50, 20);
		$this->Image('../imagenes/farmacia-logo.png',145,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(10);
		// Movernos a la derecha
		// Título
		$this->Cell(180, 36, "Reporte de pago de transporte en horas extraordinarias",0,0, 'C');
		$this->Ln(10);
		$this->SetFont('Arial','',14);
		$this->Cell(180, 36,"Reportar: DESDE: {$_GET['fi']} HASTA: {$_GET['ff']}",0,0,'C');
		// Salto de línea
		$this->Ln(30);
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
		//tabla		
		$this->SetFillColor(255,0,0);
		$this->SetDrawColor(0);
		$this->SetLineWidth(.3);
		$this->setLeftMargin(15);
		
		$w = array
		(0 => 60,
		 1 => 25,
         2 => 55,
		 3 => 35);
		 
		$header= array('Nombre', 'Cedula','Area', 'Pago (RD$)');
		for($i=0;$i<count($header);$i++)
		{
			$this->Cell($w[$i],10,$header[$i],1,0,'C');
		}
		$this->Ln();
		$this->SetFont('Times','B',7);
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
		// Datos
		foreach($tabla as $row)
		{
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			if($row == end($tabla))
			{
				$this->Cell(140,6,"Total",'LR');
				$this->Cell(35,6,$columna[0],1,0,'C');
			}
			else
			{
				$this->Cell($w[0],8,$columna[0],1,0,'C');
				$this->Cell($w[1],8,$columna[1],1,0,'C');
				$this->Cell($w[2],8,$columna[2],1,0,'C');	
				$this->Cell($w[3],8,$columna[3],1,0,'C');			
			}
			$this->Ln();
		}
		$this->Cell(array_sum($w),0,'','T');
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