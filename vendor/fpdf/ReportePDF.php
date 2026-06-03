<?php
require_once __DIR__ . '/fpdf.php';

/**
 * Clase PDF personalizada para Happy Jumping.
 * Se define AQUÍ, fuera de cualquier controlador,
 * para evitar el error "Class declarations may not be nested".
 */
class ReportePDF extends FPDF {

    public $filtroLabel = '';

    function Header() {
        $this->SetFillColor(127, 0, 255);   // Morado igual al sidebar admin
        $this->Rect(0, 0, 297, 22, 'F');
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 12, 'HAPPY JUMPING', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, 'Reporte de Reservas  |  Generado el ' . date('d/m/Y H:i:s') . '  |  ' . $this->filtroLabel, 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 6, 'Happy Jumping Peru  |  Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}
