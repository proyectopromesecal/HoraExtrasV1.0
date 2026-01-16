<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
set_time_limit(0);
global $tabla;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

$dp=0;
if (isset($_GET['dp'])) {
	if (strcmp($_GET['dp'], 'todos')!=0) {
		$dp=1;
	}
}

if(isset($_GET['fi']) && isset($_GET['ff']))
{
	$query="select  a.nombre,a.cedula,h.nombre area,REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') pago from empleado a
			inner join pago_transporte b on a.id = b.id_empleado
			inner join formulario_transporte c on b.id_formulario_transporte = c.id 
			and	c.fecha BETWEEN  '{$_GET['fi']}' AND  '{$_GET['ff']}'
			inner join t_departamento h on a.departamento = h.id
			inner join horaextra_transporte e on e.id_formulario_transporte = c.id
			inner join solicitudhe f on e.id_solicitudhe = f.id
			inner join solicitudes_autorizadas g on f.id = g.id_solicitud and g.tipo = 'HoraExtra' and g.autorizado = 1
			inner join horario d on a.id = d.id_empleado  and  d.fecha = f.fecha
			where a.nivel =0 ";
    if ($dp) {
    	$query.=" and h.id={$_GET['dp']} ";
    }

	if (strcmp($_SESSION['tipo'], "Asistente")==0) {
		$query.="and f.usr in (

					SELECT a.usuario
					FROM [horasextra].[dbo].[usuario] a
					inner join empleado b on a.empleado =  b.id
					inner join t_departamento c on b.departamento = c.id
					where b.departamento in (

						select b.id from  usuario c
						inner join empleado a on c.empleado = a.id
						inner join t_departamento b on a.departamento = b.id or a.departamento = b.subDepId 
						where c.id = {$_SESSION['id']}
						
					)

				) ";
	}
	else if (strcmp($_SESSION['tipo'], "Secretaria")==0)
	{
		$query.=" AND f.usr = (SELECT usuario from usuario where id = {$_SESSION['id']} ) ";
	}
	
	$query.=" group by a.cedula, a.nombre ,h.nombre
			order by a.nombre ";
	
	$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$tabla[]=$fila['nombre'].";".$fila['cedula'].";".$fila['area'].";".$fila['pago'];
		}
		$tabla[]='';
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
		$this->Cell(185, 36, "Reporte de pago de transporte en horas extraordinarias",0,0, 'C');
		$this->Ln(10);
		$this->SetFont('Arial','',14);
		$this->Cell(186, 36,"Reportar: DESDE: {$_GET['fi']} HASTA: {$_GET['ff']}",0,0,'C');
		// Salto de línea
		$this->Ln(30);
	}

		// Pie de página
	function Footer()
	{
		$this->setLeftMargin(10);
		$this->SetY(-45);

		$this->SetFont('Arial','B',7);
		$this->Cell(10,7, "",1,0);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,7, "Preparado por:",1,0,'C');
		$this->Cell(60,7, "Aprobado por:",1,0,'C');
		$this->Cell(60,7, "Revisado por:",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',7);
		$this->Cell(10,7, "Firma",1,0);
		$this->SetFont('Arial','',9);
		$this->Cell(60,7, "",1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',6.5);
		$this->Cell(10,7, "Nombre",1,0);
		$this->SetFont('Arial','B',7);
		$this->Cell(60,7, Manejador::obtenerNombreCompleto($_SESSION['id']),1,0,'C');
		$this->Cell(60,7, Manejador::obtenerEncargado(Manejador::obtenerIdDpto($_SESSION['dpto'])),1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',7);
		$this->Cell(10,7, "Rol",1,0);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,7, Manejador::obtenerCargo($_SESSION['id']),1,0,'C');
		$this->Cell(60,7, "Encargado",1,0,'C');
		$this->SetFont('Arial','B',7.5);
		$this->Cell(60,7, Manejador::obtenerDepartamentoN(5),1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',7);
		$this->Cell(10,7, "Fecha",1,0);
		$this->SetFont('Arial','B',9);
		$this->Cell(60,7, date('Y-m-d H:i:s'),1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		$this->setLeftMargin(10);
	}
	
	function Body($tabla)
	{
		//tabla		
		$this->SetFillColor(255,0,0);
		$this->SetDrawColor(0);
		$this->SetLineWidth(.3);
		$this->setLeftMargin(10);
		
		$w = array
		(0 => 60,
		 1 => 20,
         2 => 90,
		 3 => 20);
		 
		$header= array('Nombre', 'Cedula','Area', 'Pago (RD$)');
		for($i=0;$i<count($header);$i++)
		{
			$this->Cell($w[$i],10,$header[$i],1,0,'C');
		}
		$this->Ln();
		$this->SetFont('Times','B',6);
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
		$total=0;
		$c=0;
		
		// Datos
		foreach($tabla as $row)
		{
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			$c+=1;
			if($row == end($tabla))
			{
				$this->Cell(170,6,"Total",'LR');
				$this->Cell(20,6,number_format($total,2,'.',','),1,0,'C');
			}
			else
			{
				$total+= (float)str_replace(",", "", $columna[3]);
				$this->Cell($w[0],8,utf8_decode($columna[0]),1,0,'C');
				$this->Cell($w[1],8,$columna[1],1,0,'C');
				$this->Cell($w[2],8,utf8_decode($columna[2]),1,0,'C');	
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
$pdf->setAutoPageBreak(true,50);
$pdf->SetFont('Times','B',11);
$pdf->Body($tabla);
$pdf->Output();
?>