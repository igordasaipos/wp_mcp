<?php
/**
 * Endpoints REST API para MCP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Claude_MCP_REST_API {

    /**
     * Namespace da API
     */
    const NAMESPACE = 'wp/v2/claude-mcp';

    /**
     * Registra as rotas da REST API
     */
    public function register_routes() {
        // Endpoint principal do MCP
        register_rest_route(self::NAMESPACE, '/mcp', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_mcp_request'),
            'permission_callback' => array($this, 'check_mcp_permission'),
        ));

        // Endpoint para gerenciamento de tokens
        register_rest_route(self::NAMESPACE, '/tokens', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'list_tokens'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_token'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
        ));

        register_rest_route(self::NAMESPACE, '/tokens/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_token'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
    }

    /**
     * Verifica permissão MCP (via JWT)
     */
    public function check_mcp_permission($request) {
        $auth = WP_Claude_MCP_JWT_Auth::authenticate_request();

        if (!$auth) {
            return new WP_Error(
                'unauthorized',
                'Token de autenticação inválido ou ausente',
                array('status' => 401)
            );
        }

        // Armazena as informações de autenticação na requisição
        $request->set_param('_auth_user_id', $auth['user_id']);
        $request->set_param('_auth_capabilities', $auth['capabilities']);

        return true;
    }

    /**
     * Verifica permissão de administrador
     */
    public function check_admin_permission($request) {
        return current_user_can('manage_options');
    }

    /**
     * Processa requisições MCP
     */
    public function handle_mcp_request($request) {
        $body = $request->get_json_params();

        if (!isset($body['method'])) {
            return new WP_Error('invalid_request', 'Método não especificado', array('status' => 400));
        }

        $method = $body['method'];
        $params = isset($body['params']) ? $body['params'] : array();

        switch ($method) {
            case 'initialize':
                return $this->handle_initialize($params);

            case 'tools/list':
                return WP_Claude_MCP_Server::list_tools();

            case 'tools/call':
                if (!isset($params['name'])) {
                    return new WP_Error('invalid_params', 'Nome da ferramenta não especificado');
                }
                $arguments = isset($params['arguments']) ? $params['arguments'] : array();
                return WP_Claude_MCP_Server::call_tool($params['name'], $arguments);

            case 'resources/list':
                return WP_Claude_MCP_Server::list_resources();

            case 'resources/read':
                if (!isset($params['uri'])) {
                    return new WP_Error('invalid_params', 'URI do recurso não especificado');
                }
                return WP_Claude_MCP_Server::read_resource($params['uri']);

            case 'prompts/list':
                return WP_Claude_MCP_Server::list_prompts();

            case 'prompts/get':
                if (!isset($params['name'])) {
                    return new WP_Error('invalid_params', 'Nome do prompt não especificado');
                }
                $arguments = isset($params['arguments']) ? $params['arguments'] : array();
                return WP_Claude_MCP_Server::get_prompt($params['name'], $arguments);

            default:
                return new WP_Error('unknown_method', 'Método desconhecido: ' . $method);
        }
    }

    /**
     * Inicialização do servidor MCP
     */
    private function handle_initialize($params) {
        return array(
            'protocolVersion' => WP_Claude_MCP_Server::MCP_VERSION,
            'serverInfo' => WP_Claude_MCP_Server::get_server_info(),
            'capabilities' => array(
                'tools' => array(),
                'resources' => array(
                    'subscribe' => false,
                ),
                'prompts' => array(),
            ),
        );
    }

    /**
     * Lista tokens do usuário atual
     */
    public function list_tokens($request) {
        $user_id = get_current_user_id();
        $tokens = WP_Claude_MCP_JWT_Auth::list_user_tokens($user_id);

        return rest_ensure_response($tokens);
    }

    /**
     * Cria um novo token
     */
    public function create_token($request) {
        $user_id = get_current_user_id();
        $token_name = $request->get_param('token_name');
        $capabilities = $request->get_param('capabilities');
        $expires_in = $request->get_param('expires_in');

        if (empty($token_name)) {
            return new WP_Error('invalid_params', 'Nome do token é obrigatório', array('status' => 400));
        }

        $result = WP_Claude_MCP_JWT_Auth::generate_token(
            $user_id,
            $token_name,
            $capabilities ? $capabilities : array(),
            $expires_in
        );

        if (!$result) {
            return new WP_Error('token_creation_failed', 'Falha ao criar token', array('status' => 500));
        }

        return rest_ensure_response($result);
    }

    /**
     * Deleta um token
     */
    public function delete_token($request) {
        $user_id = get_current_user_id();
        $token_id = $request->get_param('id');

        $result = WP_Claude_MCP_JWT_Auth::revoke_token($token_id, $user_id);

        if ($result === false) {
            return new WP_Error('token_deletion_failed', 'Falha ao deletar token', array('status' => 500));
        }

        return rest_ensure_response(array('success' => true));
    }
}
