<?php
/**
 * ClienteCore — cliente HTTP hacia Core Institucional, en PHP puro.
 * Usado tanto por scripts/sync_core.php (sync periódico) como por
 * los controladores web (sync inmediato, ej. registro público).
 */
class ClienteCore
{
    private string $baseUrl;
    private string $token;
    private ?array $mapaTipoIdentificacion = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(getenv('CORE_API_URL') ?: '', '/');
        $this->token   = getenv('CORE_API_TOKEN') ?: '';

        if (!$this->baseUrl || !$this->token) {
            throw new Exception('CORE_API_URL o CORE_API_TOKEN no configurados en .env');
        }
    }

    private function request(string $metodo, string $path, array $data = []): array
    {
        $url = "{$this->baseUrl}/{$path}";
        if ($metodo === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init($url);
        $headers = [
            "Authorization: Bearer {$this->token}",
            'Accept: application/json',
        ];

        $opciones = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
        ];

        if ($metodo === 'POST') {
            $opciones[CURLOPT_POST] = true;
            $opciones[CURLOPT_POSTFIELDS] = json_encode($data);
            $headers[] = 'Content-Type: application/json';
            $opciones[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $opciones);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error de conexión a Core ({$path}): {$error}");
        }

        $json = json_decode($body, true);

        if ($httpCode === 422) {
            // Error de validación de Core (ej. cédula duplicada) — mensaje claro, no técnico
            $mensajes = $json['errors'] ?? $json['message'] ?? 'Datos inválidos';
            throw new CoreValidationException(
                is_array($mensajes) ? implode(', ', array_map('implode', $mensajes)) : $mensajes
            );
        }

        if ($httpCode >= 400) {
            throw new Exception("Core devolvió HTTP {$httpCode} en {$path}: " . substr($body, 0, 300));
        }

        if ($json === null) {
            throw new Exception("Respuesta de Core no es JSON válido en {$path}");
        }

        return $json;
    }

    public function tiposIdentificacion(): array
    {
        if ($this->mapaTipoIdentificacion === null) {
            $tipos = $this->request('GET', 'tipos-identificacion');
            $this->mapaTipoIdentificacion = [];
            foreach ($tipos as $tipo) {
                $this->mapaTipoIdentificacion[$tipo['codigo']] = $tipo['id'];
            }
        }
        return $this->mapaTipoIdentificacion;
    }

    public function idTipoIdentificacion(string $codigo): int
    {
        $mapa = $this->tiposIdentificacion();
        if (!isset($mapa[$codigo])) {
            throw new Exception("Tipo de identificación '{$codigo}' no existe en Core");
        }
        return $mapa[$codigo];
    }

    // Busca un ciudadano en Core por tipo+número. Null si no existe.
    public function buscarCiudadanoPorIdentificacion(string $tipoCodigo, string $numeroIdentificacion): ?array
    {
        $idTipo = $this->idTipoIdentificacion($tipoCodigo);
        $resultado = $this->request('GET', 'ciudadanos', [
            'tipo_identificacion_id' => $idTipo,
            'numero_identificacion'  => $numeroIdentificacion,
        ]);
        $data = $resultado['data'] ?? [];
        return $data[0] ?? null;
    }

    // Crea el ciudadano en Core y devuelve el objeto completo (incluye 'id' nuevo)
    public function crearCiudadano(array $datosPersonales): array
    {
        return $this->request('POST', 'ciudadanos', $datosPersonales);
    }
}

// Excepción específica para errores de validación de Core (ej. cédula duplicada),
// para que el controlador la muestre como mensaje amigable al usuario, no como error 500.
class CoreValidationException extends Exception {}