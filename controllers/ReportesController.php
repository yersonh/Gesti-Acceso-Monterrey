<?php

declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
class ReportesController
{
    private ReporteModel $model;

    public function __construct()
    {
        $this->model = new ReporteModel();
    }

    // ─── Valida y sanitiza los filtros del request ────────────────────────────

    private function getFiltros(): array
    {
        return [
            'fecha_inicio'   => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin'      => $_GET['fecha_fin']     ?? date('Y-m-d'),
            'dependencia_id' => isset($_GET['dependencia_id']) && $_GET['dependencia_id'] !== '' 
                                ? (int)$_GET['dependencia_id'] 
                                : null,
            'funcionario_id' => isset($_GET['funcionario_id']) && $_GET['funcionario_id'] !== '' 
                                ? (int)$_GET['funcionario_id'] 
                                : null,
        ];
    }

    // ─── Endpoint JSON para AJAX ──────────────────────────────────────────────

    public function api(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Solo SuperAdmin puede acceder
        $this->requireSuperAdmin();

        $reporte = $_GET['reporte'] ?? '';
        $filtros  = $this->getFiltros();

        try {
            $data = match ($reporte) {
                'ocupacion'    => $this->model->ocupacionPorDependencia($filtros),
                'horas_pico'   => $this->model->horasPico($filtros),
                'espera'       => $this->model->tiempoEsperaPromedio($filtros),
                'duracion'     => $this->model->duracionVisitas($filtros),
                'motivos'      => $this->model->rankingMotivos($filtros),
                'no_show'      => $this->model->inasistencias($filtros),
                'trazabilidad' => $this->model->trazabilidadCitas($filtros),
                'exportacion'  => $this->model->exportacionPlana($filtros),
                'satisfaccion'             => $this->model->satisfaccionGeneral($filtros),
                'satisfaccion_funcionario' => $this->model->satisfaccionPorFuncionario($filtros),
                'asistencia'               => $this->model->tasaAsistencia($filtros),
                'filtros'      => $this->getFiltrosData(),
                default        => throw new InvalidArgumentException("Tipo de reporte no reconocido."),
            };

            echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);

        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            error_log('ReportesController::api — ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor.']);
        }
    }

    // ─── Datos para poblar los selects de filtros ─────────────────────────────

    private function getFiltrosData(): array
    {
       return [
        'dependencias' => $this->model->getDependencias(),
        'funcionarios' => $this->model->getFuncionarios(),
        ];
    }

    // ─── Renderiza la vista del dashboard ─────────────────────────────────────

    public function dashboard(): void
    {
         $this->requireSuperAdmin();
        $usuarioId = $_SESSION['usuario_id'];
        $nombreCompleto = $_SESSION['usuario_nombre'] ?? 'SuperAdmin';
        
        // Calcular iniciales (igual que en la vista de ciudadanos)
        $partes = explode(' ', trim($nombreCompleto));
        $iniciales = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
        $primerNombre = $partes[0];
        
        // Pasar variables a la vista
        require_once __DIR__ . '/../views/superadmin/reportes.php';
    }

    // ─── Guard de seguridad ───────────────────────────────────────────────────

    private function requireSuperAdmin(): void
    {
        // Ajusta esto a como manejas sesiones en tu proyecto
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Superadmin') {
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/ajax/')) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'Acceso denegado.']);
                exit;
            }
            redirect('/login');
        }
    }

    // ─── NUEVO: Exportar a Excel ──────────────────────────────────────────────

    public function exportarExcel(): void
    {
        $this->requireSuperAdmin();

        require_once __DIR__ . '/../vendor/autoload.php';

        $reporte = $_GET['reporte'] ?? '';
        $filtros = $this->getFiltros();

        $data = match ($reporte) {
            'ocupacion'    => $this->model->ocupacionPorDependencia($filtros),
            'horas_pico'   => $this->model->horasPico($filtros),
            'espera'       => $this->model->tiempoEsperaPromedio($filtros),
            'duracion'     => $this->model->duracionVisitas($filtros),
            'motivos'      => $this->model->rankingMotivos($filtros),
            'no_show'      => $this->model->inasistencias($filtros),
            'trazabilidad' => $this->model->trazabilidadCitas($filtros),
            'exportacion'  => $this->model->exportacionPlana($filtros),
            'satisfaccion'             => $this->model->satisfaccionGeneral($filtros),
            'satisfaccion_funcionario' => $this->model->satisfaccionPorFuncionario($filtros),
            default        => [],
        };

        if (empty($data)) {
            http_response_code(204);
            exit;
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(ucfirst($reporte));

        // Logo
        $logoPath = __DIR__ . '/../public/imagenes/logoalcaldia.jpg';
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setWidth(60);
            $drawing->setHeight(60);
            $drawing->setWorksheet($sheet);
        }

        // Título
        $nombreReporte = strtoupper(str_replace('_', ' ', $reporte));
        $sheet->setCellValue('B1', 'Alcaldía Municipal de Monterrey · Casanare');
        $sheet->setCellValue('B2', "Reporte: {$nombreReporte}");
        $sheet->setCellValue('B3', "Período: {$filtros['fecha_inicio']} — {$filtros['fecha_fin']}   |   Generado: " . date('d/m/Y H:i'));

        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('B3')->getFont()->setSize(9);

        // Encabezados de tabla (fila 5)
        $headers = array_keys($data[0]);
        $col     = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', strtoupper(str_replace('_', ' ', $header)));
            $col++;
        }

        $lastCol     = chr(ord('A') + count($headers) - 1);
        $headerRange = "A5:{$lastCol}5";

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A5C38']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(22);

        // Datos (desde fila 6)
        $row = 6;
        foreach ($data as $i => $record) {
            $col = 'A';
            foreach ($record as $value) {
                $sheet->setCellValue($col . $row, $value ?? '');
                $col++;
            }

            $bgColor = $i % 2 === 0 ? 'FFFFFF' : 'F1F8F4';
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        $sheet->getStyle("A5:{$lastCol}" . ($row - 1))->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ]);

        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(16);
        $sheet->getRowDimension(4)->setRowHeight(10);

        // Capturar el XLSX en memoria para evitar conflictos con el buffer HTTP
        $writer = new Xlsx($spreadsheet);
        while (ob_get_level()) ob_end_clean();
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = "reporte_{$reporte}_{$filtros['fecha_inicio']}_{$filtros['fecha_fin']}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: max-age=0');

        echo $content;
        exit;
    }
}