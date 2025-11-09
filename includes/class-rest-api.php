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
        // Endpoint principal do MCP (Claude Desktop)
        register_rest_route(self::NAMESPACE, '/mcp', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_mcp_request'),
            'permission_callback' => array($this, 'check_mcp_permission'),
        ));

        // Endpoint público para Remote MCP Server (Claude Web)
        register_rest_route(self::NAMESPACE, '/remote', array(
            'methods' => array('GET', 'POST', 'OPTIONS'),
            'callback' => array($this, 'handle_remote_mcp'),
            'permission_callback' => array($this, 'check_remote_permission'),
        ));

        // Endpoint SSE para streaming
        register_rest_route(self::NAMESPACE, '/sse', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_sse_stream'),
            'permission_callback' => array($this, 'check_remote_permission'),
        ));

        // Endpoint OAuth2
        register_rest_route(self::NAMESPACE, '/oauth/authorize', array(
            'methods' => 'GET',
            'callback' => array($this, 'oauth_authorize'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route(self::NAMESPACE, '/oauth/token', array(
            'methods' => 'POST',
            'callback' => array($this, 'oauth_token'),
            'permission_callback' => '__return_true',
        ));

        // Endpoint de configuração pública
        register_rest_route(self::NAMESPACE, '/config', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_public_config'),
            'permission_callback' => '__return_true',
        ));

        // Endpoint de teste de conexão
        register_rest_route(self::NAMESPACE, '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => '__return_true',
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

        // Endpoint para OAuth settings
        register_rest_route(self::NAMESPACE, '/oauth-settings', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_oauth_settings'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'save_oauth_settings'),
                'permission_callback' => array($this, 'check_admin_permission'),
            ),
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
     * Verifica permissão para Remote MCP (Claude Web)
     */
    public function check_remote_permission($request) {
        // Permite OPTIONS para CORS
        if ($request->get_method() === 'OPTIONS') {
            return true;
        }

        // Verifica Bearer token
        $auth = WP_Claude_MCP_JWT_Auth::authenticate_request();

        if (!$auth) {
            return new WP_Error(
                'unauthorized',
                'Token de autenticação inválido ou ausente',
                array('status' => 401)
            );
        }

        $request->set_param('_auth_user_id', $auth['user_id']);
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

    /**
     * Handler para Remote MCP Server (Claude Web)
     */
    public function handle_remote_mcp($request) {
        // Adiciona headers CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');

        if ($request->get_method() === 'OPTIONS') {
            return rest_ensure_response(array('status' => 'ok'));
        }

        // Para GET, retorna informações do servidor
        if ($request->get_method() === 'GET') {
            return rest_ensure_response(array(
                'name' => 'WordPress MCP Server',
                'version' => WP_CLAUDE_MCP_VERSION,
                'protocol' => WP_Claude_MCP_Server::MCP_VERSION,
                'status' => 'active',
                'site' => get_bloginfo('name'),
                'url' => get_site_url(),
            ));
        }

        // Para POST, processa requisições MCP
        return $this->handle_mcp_request($request);
    }

    /**
     * Retorna configuração pública
     */
    public function get_public_config($request) {
        header('Access-Control-Allow-Origin: *');

        return rest_ensure_response(array(
            'server_url' => rest_url('wp/v2/claude-mcp/remote'),
            'oauth_authorize_url' => rest_url('wp/v2/claude-mcp/oauth/authorize'),
            'oauth_token_url' => rest_url('wp/v2/claude-mcp/oauth/token'),
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
        ));
    }

    /**
     * OAuth: Autorização
     */
    public function oauth_authorize($request) {
        $client_id = $request->get_param('client_id');
        $redirect_uri = $request->get_param('redirect_uri');
        $state = $request->get_param('state');

        if (!$client_id || !$redirect_uri) {
            return new WP_Error('invalid_request', 'client_id e redirect_uri são obrigatórios');
        }

        // Verifica se o usuário está logado
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(add_query_arg($_GET, rest_url('wp/v2/claude-mcp/oauth/authorize'))));
            exit;
        }

        // Gera código de autorização
        $code = bin2hex(random_bytes(32));
        set_transient('wp_claude_mcp_oauth_code_' . $code, array(
            'client_id' => $client_id,
            'user_id' => get_current_user_id(),
            'redirect_uri' => $redirect_uri,
        ), 600); // 10 minutos

        // Redireciona com o código
        $redirect = add_query_arg(array(
            'code' => $code,
            'state' => $state,
        ), $redirect_uri);

        wp_redirect($redirect);
        exit;
    }

    /**
     * OAuth: Token
     */
    public function oauth_token($request) {
        $grant_type = $request->get_param('grant_type');
        $code = $request->get_param('code');
        $client_id = $request->get_param('client_id');
        $client_secret = $request->get_param('client_secret');

        if ($grant_type !== 'authorization_code') {
            return new WP_Error('unsupported_grant_type', 'Apenas authorization_code é suportado');
        }

        // Valida o código
        $code_data = get_transient('wp_claude_mcp_oauth_code_' . $code);
        if (!$code_data) {
            return new WP_Error('invalid_code', 'Código inválido ou expirado');
        }

        // Valida client_id
        $oauth_settings = get_option('wp_claude_mcp_oauth_settings', array());
        if (empty($oauth_settings['client_id']) || $oauth_settings['client_id'] !== $client_id) {
            return new WP_Error('invalid_client', 'client_id inválido');
        }

        // Valida client_secret se configurado
        if (!empty($oauth_settings['client_secret']) && $oauth_settings['client_secret'] !== $client_secret) {
            return new WP_Error('invalid_client', 'client_secret inválido');
        }

        // Deleta o código usado
        delete_transient('wp_claude_mcp_oauth_code_' . $code);

        // Gera token de acesso
        $token_result = WP_Claude_MCP_JWT_Auth::generate_token(
            $code_data['user_id'],
            'OAuth Token - ' . date('Y-m-d H:i:s'),
            array(),
            '+1 year'
        );

        if (!$token_result) {
            return new WP_Error('token_generation_failed', 'Falha ao gerar token');
        }

        return rest_ensure_response(array(
            'access_token' => $token_result['token'],
            'token_type' => 'Bearer',
            'expires_in' => 31536000, // 1 ano
        ));
    }

    /**
     * Obtém configurações OAuth
     */
    public function get_oauth_settings($request) {
        $settings = get_option('wp_claude_mcp_oauth_settings', array(
            'client_id' => '',
            'client_secret' => '',
        ));

        return rest_ensure_response($settings);
    }

    /**
     * Salva configurações OAuth
     */
    public function save_oauth_settings($request) {
        $client_id = $request->get_param('client_id');
        $client_secret = $request->get_param('client_secret');

        $settings = array(
            'client_id' => sanitize_text_field($client_id),
            'client_secret' => sanitize_text_field($client_secret),
        );

        update_option('wp_claude_mcp_oauth_settings', $settings);

        return rest_ensure_response(array('success' => true, 'settings' => $settings));
    }

    /**
     * Handler para SSE Stream
     */
    public function handle_sse_stream($request) {
        // Inicia stream SSE
        WP_Claude_MCP_SSE_Server::start_stream();

        // Envia evento de conexão
        WP_Claude_MCP_SSE_Server::send_event('connected', array(
            'server' => 'WordPress MCP',
            'version' => WP_CLAUDE_MCP_VERSION,
            'time' => time(),
        ));

        // Obtém método e parâmetros
        $method = $request->get_param('method');
        $params_json = $request->get_param('params');
        $params = $params_json ? json_decode($params_json, true) : array();

        if ($method) {
            // Processa requisição
            WP_Claude_MCP_SSE_Server::process_mcp_request($method, $params);
        } else {
            // Modo keep-alive
            $counter = 0;
            while ($counter < 60) { // 60 segundos máximo
                WP_Claude_MCP_SSE_Server::send_ping();
                sleep(1);
                $counter++;

                // Verifica se cliente desconectou
                if (connection_aborted()) {
                    break;
                }
            }
        }

        exit;
    }

    /**
     * Testa a conexão e valida o token
     */
    public function test_connection($request) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        $response = array(
            'status' => 'ok',
            'message' => 'Servidor MCP está funcionando',
            'endpoints' => array(
                'remote' => rest_url('wp/v2/claude-mcp/remote'),
                'sse' => rest_url('wp/v2/claude-mcp/sse'),
                'mcp' => rest_url('wp/v2/claude-mcp/mcp'),
            ),
            'version' => WP_CLAUDE_MCP_VERSION,
            'site' => get_bloginfo('name'),
        );

        // Tenta validar o token se fornecido
        $auth = WP_Claude_MCP_JWT_Auth::authenticate_request();

        if ($auth) {
            $response['token_valid'] = true;
            $response['user_id'] = $auth['user_id'];
            $user = get_userdata($auth['user_id']);
            $response['user_login'] = $user->user_login;
            $response['user_roles'] = $user->roles;
        } else {
            $response['token_valid'] = false;
            $response['token_provided'] = isset($_GET['token']) || isset($_SERVER['HTTP_AUTHORIZATION']);

            // Informações de debug
            if (isset($_GET['token'])) {
                $response['debug'] = array(
                    'token_length' => strlen($_GET['token']),
                    'token_prefix' => substr($_GET['token'], 0, 8) . '...',
                );
            }
        }

        return rest_ensure_response($response);
    }
}
