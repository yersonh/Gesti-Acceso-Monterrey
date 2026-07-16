<?php
require_once __DIR__ . '/../models/RecepcionModel.php';

class RecepcionController {

    private function requireRecepcionista(): void {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Recepcionista') {
            redirect('/login');
        }
    }

    public function dashboard(): void {
        $this->requireRecepcionista();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $accion = $_POST['accion'] ?? '';

            try {
                if ($accion === 'registrar_ingreso') {
                    $id = (int)($_POST['id_cita'] ?? 0);
                    if (!RecepcionModel::registrarIngreso($id)) {
                        throw new Exception('No se pudo registrar el ingreso. Verifique que la cita siga confirmada.');
                    }
                    $_SESSION['flash_mensaje'] = 'Ingreso registrado correctamente.';

                } elseif ($accion === 'registrar_salida') {
                    $id   = (int)($_POST['id'] ?? 0);
                    $tipo = $_POST['tipo'] ?? 'cita';
                    $ok = $tipo === 'espontanea'
                        ? RecepcionModel::registrarSalidaEspontanea($id)
                        : RecepcionModel::registrarSalida($id);
                    if (!$ok) throw new Exception('No se pudo registrar la salida.');
                    $_SESSION['flash_mensaje'] = 'Salida registrada correctamente.';

                } elseif ($accion === 'visita_espontanea') {
                    $ciudadanoId          = (int)($_POST['ciudadano_id'] ?? 0);
                    $nombres              = trim($_POST['nombres'] ?? '');
                    $apellidos            = trim($_POST['apellidos'] ?? '');
                    $tipoIdentificacion   = $_POST['tipo_identificacion'] ?? '';
                    $numeroIdentificacion = trim($_POST['numero_identificacion'] ?? '');
                    $telefono             = trim($_POST['telefono'] ?? '');
                    $email                = trim($_POST['email'] ?? '');
                    $whatsapp             = trim($_POST['whatsapp'] ?? '');
                    $direccion            = trim($_POST['direccion'] ?? '');
                    $proveniencia         = trim($_POST['proveniencia'] ?? '');
                    $dependenciaId        = (int)($_POST['dependencia_id'] ?? 0);
                    $funcionarioId        = (int)($_POST['funcionario_id'] ?? 0);
                    $motivo               = trim($_POST['motivo'] ?? '');
                    $notas                = trim($_POST['notas'] ?? '');

                    $tiposPermitidos = ['CC', 'CE', 'TI', 'PA', 'RC', 'NIT', 'PEP'];

                    if (!$nombres || !$apellidos) {
                        throw new Exception('Nombres y apellidos son obligatorios.');
                    }
                    if (!in_array($tipoIdentificacion, $tiposPermitidos, true)) {
                        throw new Exception('Tipo de identificación inválido.');
                    }
                    if (!$numeroIdentificacion) {
                        throw new Exception('El número de identificación es obligatorio.');
                    }
                    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('El correo electrónico no es válido.');
                    }
                    if (!$dependenciaId) {
                        throw new Exception('Debe seleccionar una dependencia.');
                    }
                    if (!$funcionarioId) {
                        throw new Exception('Debe seleccionar un funcionario.');
                    }
                    if (!$motivo) {
                        throw new Exception('El motivo de la visita es obligatorio.');
                    }

                    $idVisita = RecepcionModel::registrarVisitaEspontanea([
                        'ciudadano_id'          => $ciudadanoId,
                        'nombres'               => $nombres,
                        'apellidos'             => $apellidos,
                        'tipo_identificacion'   => $tipoIdentificacion,
                        'numero_identificacion' => $numeroIdentificacion,
                        'telefono'              => $telefono     ?: null,
                        'email'                 => $email        ?: null,
                        'whatsapp'              => $whatsapp     ?: null,
                        'direccion'             => $direccion    ?: null,
                        'proveniencia'          => $proveniencia ?: null,
                        'funcionario_id'        => $funcionarioId,
                        'dependencia_id'        => $dependenciaId,
                        'motivo'                => $motivo,
                        'notas'                 => $notas ?: null,
                    ]);

                    $_SESSION['flash_mensaje'] = "Visita espontánea registrada (#$idVisita).";
                }
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }

            redirect('/recepcion');
        }

        $mensaje = $_SESSION['flash_mensaje'] ?? '';
        $error   = $_SESSION['flash_error']   ?? '';
        unset($_SESSION['flash_mensaje'], $_SESSION['flash_error']);

        $registros    = RecepcionModel::obtenerRegistrosDia();
        $alertas      = RecepcionModel::obtenerAlertasDia(15);
        $dependencias = Dependencia::getActivas();

        require_once BASE_PATH . '/views/recepcion/dashboard.php';
    }

    public function verificarCiudadano(): void {
        $this->requireRecepcionista();
        header('Content-Type: application/json');

        $numero = trim($_GET['numero'] ?? '');
        if (!$numero) {
            echo json_encode(['encontrado' => false]);
            exit;
        }

        $ciudadano = RecepcionModel::buscarCiudadanoLocal($numero);
        echo json_encode($ciudadano
            ? ['encontrado' => true, 'ciudadano' => $ciudadano]
            : ['encontrado' => false]);
        exit;
    }
}
