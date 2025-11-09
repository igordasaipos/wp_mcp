<?php
/**
 * Servidor SSE para comunicação em tempo real com Claude
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Claude_MCP_SSE_Server {

    /**
     * Inicia stream SSE
     */
    public static function start_stream() {
        // Headers para SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Nginx
        header('Access-Control-Allow-Origin: *');

        // Desabilita buffering do PHP
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Mantém a conexão viva
        set_time_limit(0);
        ignore_user_abort(true);
    }

    /**
     * Envia evento SSE
     */
    public static function send_event($event_type, $data, $id = null) {
        if ($id !== null) {
            echo "id: {$id}\n";
        }

        echo "event: {$event_type}\n";
        echo "data: " . json_encode($data) . "\n\n";

        // Força envio imediato
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }

    /**
     * Envia mensagem de erro
     */
    public static function send_error($message, $code = 'error') {
        self::send_event('error', array(
            'code' => $code,
            'message' => $message,
        ));
    }

    /**
     * Envia mensagem de sucesso
     */
    public static function send_success($message, $data = array()) {
        self::send_event('success', array(
            'message' => $message,
            'data' => $data,
        ));
    }

    /**
     * Envia progresso de operação
     */
    public static function send_progress($message, $percentage = 0) {
        self::send_event('progress', array(
            'message' => $message,
            'percentage' => $percentage,
        ));
    }

    /**
     * Envia evento de conclusão
     */
    public static function send_complete($data = array()) {
        self::send_event('complete', $data);
    }

    /**
     * Mantém conexão viva
     */
    public static function send_ping() {
        self::send_event('ping', array('timestamp' => time()));
    }

    /**
     * Processa requisição MCP via SSE
     */
    public static function process_mcp_request($method, $params = array()) {
        try {
            self::send_progress('Processando requisição...', 0);

            switch ($method) {
                case 'tools/list':
                    $result = WP_Claude_MCP_Server::list_tools();
                    self::send_progress('Listando ferramentas...', 50);
                    self::send_complete($result);
                    break;

                case 'tools/call':
                    if (!isset($params['name'])) {
                        self::send_error('Nome da ferramenta não especificado');
                        return;
                    }

                    $tool_name = $params['name'];
                    $arguments = isset($params['arguments']) ? $params['arguments'] : array();

                    self::send_progress("Executando {$tool_name}...", 25);

                    $result = WP_Claude_MCP_Server::call_tool($tool_name, $arguments);

                    if (is_wp_error($result)) {
                        self::send_error($result->get_error_message());
                    } else {
                        self::send_progress('Processando resultado...', 75);
                        self::send_complete($result);
                    }
                    break;

                case 'resources/list':
                    $result = WP_Claude_MCP_Server::list_resources();
                    self::send_complete($result);
                    break;

                case 'resources/read':
                    if (!isset($params['uri'])) {
                        self::send_error('URI do recurso não especificado');
                        return;
                    }
                    $result = WP_Claude_MCP_Server::read_resource($params['uri']);
                    self::send_complete($result);
                    break;

                case 'prompts/list':
                    $result = WP_Claude_MCP_Server::list_prompts();
                    self::send_complete($result);
                    break;

                case 'prompts/get':
                    if (!isset($params['name'])) {
                        self::send_error('Nome do prompt não especificado');
                        return;
                    }
                    $arguments = isset($params['arguments']) ? $params['arguments'] : array();
                    $result = WP_Claude_MCP_Server::get_prompt($params['name'], $arguments);
                    self::send_complete($result);
                    break;

                default:
                    self::send_error('Método desconhecido: ' . $method);
            }

        } catch (Exception $e) {
            self::send_error($e->getMessage());
        }
    }
}
