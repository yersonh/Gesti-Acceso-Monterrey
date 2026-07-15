<?php

class WhatsApp {

    private static function token(): string {
        return $_ENV['META_WHATSAPP_TOKEN'] ?? getenv('META_WHATSAPP_TOKEN') ?? '';
    }

    private static function phoneNumberId(): string {
        return $_ENV['META_PHONE_NUMBER_ID'] ?? getenv('META_PHONE_NUMBER_ID') ?? '';
    }

    private static function templateName(): string {
        return $_ENV['META_WHATSAPP_TEMPLATE'] ?? getenv('META_WHATSAPP_TEMPLATE') ?? 'valoracion_visita';
    }

    public static function configurado(): bool {
        return !empty(self::token()) && !empty(self::phoneNumberId());
    }

    /**
     * Envía el link de valoración por WhatsApp usando Meta Cloud API.
     * La plantilla debe tener dos parámetros en el body: {{1}} nombre, {{2}} link.
     * Retorna true si Meta aceptó el mensaje.
     */
    public static function enviarLinkValoracion(string $numero, string $nombre, string $link): bool {
        if (!self::configurado()) return false;

        $url  = 'https://graph.facebook.com/v19.0/' . self::phoneNumberId() . '/messages';
        $body = json_encode([
            'messaging_product' => 'whatsapp',
            'to'                => $numero,
            'type'              => 'template',
            'template'          => [
                'name'       => self::templateName(),
                'language'   => ['code' => 'es'],
                'components' => [[
                    'type'       => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $nombre],
                        ['type' => 'text', 'text' => $link],
                    ],
                ]],
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . self::token(),
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('WhatsApp Meta API HTTP ' . $httpCode . ': ' . $response);
            return false;
        }

        $data = json_decode($response, true);
        return !empty($data['messages'][0]['id']);
    }
}
