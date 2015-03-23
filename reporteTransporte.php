<?php
require('fpdf/fpdf.php');
include('lib/motor.php');
global $tabla;
	
	if(isset($_GET['s']) && isset($_GET['she']))
	{
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );			
		$query="select a.nombre, e.area,d.nombre departamento, e.fecha,c.pago, g.nooficio
				from empleado a
				inner join solicitudhe g on g.id = {$_GET['she']}
				inner join formulario_transporte e on e.id = {$_GET['s']}
				inner join horario b on a.id = b.id_empleado and b.fecha = g.fecha and horadesalida > case when dbo.validarNoLaboral(g.fecha) = 0 then '16:59:59' else '01:00:00' end
				inner join pago_transporte c on a.id = c.id_empleado and c.id_formulario_transporte = e.id
				inner join t_departamento d on a.departamento = d.id
				inner join horaextra_transporte f on f.id_formulario_transporte = e.id and f.id_solicitudhe = g.id
				inner join solicitudes_autorizadas h on h.id_solicitud = g.id and h.tipo = 'HoraExtra' and h.autorizado = 1
				inner join solicitudes i on i.id_empleado = a.id and i.id_solicitud = g.id 
				where a.nivel=0";

		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		$queryTotal="
				select ROUND(sum(pago),2) as totalpago
				from empleado a
				inner join solicitudhe g on g.id = {$_GET['she']}
				inner join formulario_transporte e on e.id = {$_GET['s']}
				inner join horario b on a.id = b.id_empleado and b.fecha = g.fecha and horadesalida > case when dbo.validarNoLaboral(g.fecha) = 0 then '16:59:59' else '01:00:00' end
				inner join pago_transporte c on a.id = c.id_empleado and c.id_formulario_transporte = e.id
				inner join t_departamento d on a.departamento = d.id
				inner join horaextra_transporte f on f.id_formulario_transporte = e.id and f.id_solicitudhe = g.id
				inner join solicitudes_autorizadas h on h.id_solicitud = g.id and h.tipo = 'HoraExtra' and h.autorizado = 1
				inner join solicitudes i on i.id_empleado = a.id and i.id_solicitud = g.id 
				where a.nivel=0";
		$rsTotal = sqlsrv_query($_SESSION['con'],$queryTotal, $params, $options);
		if($rs)
		{
			if($rsTotal)
			{
				$data;
				$data = sqlsrv_fetch_array($rsTotal, SQLSRV_FETCH_ASSOC);
				if(sqlsrv_num_rows($rs) >0)
				{
					while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
					{
						$tabla[]=$fila['nombre'].";".$fila['area'].";".$fila['departamento'].";".$fila['fecha']->format('d/m/Y').";".$fila['pago'];				
					}	
					$tabla[]=$data['totalpago'];
					
				}	
			}
		}
	}
	else
	{
		header('Location:pagoTransporte.php');
	}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		$this->Image('imagenes/logo-promosecal.png',10,10,50, 20);
		$this->Image('imagenes/farmacia-logo.png',235,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(10);
		// Movernos a la derecha
		// Título
		$this->Cell(240, 36, "Reporte pago de transporte de la solicitud ".ManejadorSolicitud::obtenerNoOficio($_GET['she']),0,0, 'C');
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
		if(empty($tabla))
		{
			$this->Cell(190,8,"                                          El pago de Transporte no se ha generado debido a:",'C');
			$this->Ln();
			$this->Cell(190,8,"     La hora de salida de los empleados  de esta solicitud no sobrepaso el tiempo correspondiente.",'C');
		}
		else
		{
			//tabla		
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			
			$w = array
			(0 => 60,
			 1 => 45,
			 2 => 85,
			 3 => 15,
			 4 => 15);
			$header= array('Nombre', 'Area', 'Departamento', 'Fecha','Pago');
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',6);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			// Datos
			foreach($tabla as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				if($row == end($tabla))
				{
					$this->Cell(205,6,"Total",1,'LR');
					$this->Cell(15,6,$columna[0]." RD$",1,0,'LR');
					$this->Ln();
				}
				else
				{
					$this->Cell($w[0],8,$columna[0],1,0,'LR');
					$this->Cell($w[1],8,$columna[1],1,0,'LR');
					$this->Cell($w[2],8,$columna[2],1,0,'LR');
					$this->Cell($w[3],8,$columna[3],1,0,'LR');	
					$this->Cell($w[4],8,$columna[4]." RD$",1,0,'LE');		
					$this->Ln();				
				}		
			}
			$this->Cell(array_sum($w),0,'','T');		
		}
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('L', 'mm', 'A4');
$pdf->SetLeftMargin(35);
$pdf->AliasNbPages();
$pdf->AddPage();
//$pdf->SetFont('Times','B',12);
$pdf->Body($tabla);
$pdf->Output();
?>