<?php 

if (!defined('Horas_Laborables')) define('Horas_Laborables', 8);
if (!defined('Porciento_Bono_Extra')) define('Porciento_Bono_Extra', 0.30);
if (!defined('Promedio_DiasxMes')) define('Promedio_DiasxMes', 21.67);
	
class Calculo
{
	function calcularPorcientoSueldo($sueldoBruto)
	{
		$porciento = $sueldoBruto * Porciento_Bono_Extra;
		return $porciento;
	}
	function calcularSalarioDiario($sueldoBruto)
	{
		$salarioDiario = $sueldoBruto/Promedio_DiasxMes;
		return $salarioDiario;
	}
	
	function calcularHoraNormal($salarioDiario)
	{
		$horaNormal = $salarioDiario/ Horas_Laborables;
		return $horaNormal;
	}
	
	function calcularHoraFeriada($horaNormal)
	{
		$horaFeriada = $horaNormal * Porciento_Bono_Extra;
		return $horaFeriada;
	}
	
	function calcularHoraExtraNormal($cantidadHoras, $sueldo)
	{
		$costoHora = $this->calcularSalarioDiario($sueldo);
		$costoHora = $this->calcularHoraNormal($costoHora);
		$resultado = $costoHora * $cantidadHoras;
		return $resultado;
	}
	
	function calcularHoraExtraFeriada($cantidadHoras, $sueldo)
	{
		$costoHora = $this->calcularSalarioDiario($sueldo);
		$costoHora = $this->calcularHoraNormal($costoHora);
		$costoHora += $this->calcularHoraFeriada($costoHora);
		$resultado = $costoHora * $cantidadHoras;
		return $resultado;	
	}
}
?>