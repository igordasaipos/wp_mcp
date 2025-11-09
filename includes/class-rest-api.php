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

        // Endpoint SSE para streaming (GET) e mensagens (POST)
        register_rest_route(self::NAMESPACE, '/sse', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'handle_sse_stream'),
                'permission_callback' => array($this, 'check_remote_permission'),
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_sse_message'),
                'permission_callback' => array($this, 'check_remote_permission'),
            ),
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

        // Endpoint de debug de token (apenas para admin)
        register_rest_route(self::NAMESPACE, '/debug-token', array(
            'methods' => 'GET',
            'callback' => array($this, 'debug_token'),
            'permission_callback' => array($this, 'check_admin_permission'),
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

        // Permite GET sem autenticação (discovery endpoint)
        // Claude precisa descobrir as capacidades antes de autenticar
        if ($request->get_method() === 'GET') {
            return true;
        }

        // Para POST (execução de ferramentas), requer autenticação
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
        header('Content-Type: application/json');

        if ($request->get_method() === 'OPTIONS') {
            return rest_ensure_response(array('status' => 'ok'));
        }

        // Para GET, retorna lista de ferramentas (discovery endpoint)
        if ($request->get_method() === 'GET') {
            $tools_response = WP_Claude_MCP_Server::list_tools();
            $server_info = WP_Claude_MCP_Server::get_server_info();

            return rest_ensure_response(array(
                'protocolVersion' => WP_Claude_MCP_Server::MCP_VERSION,
                'serverInfo' => array(
                    'name' => $server_info['name'],
                    'version' => $server_info['version'],
                ),
                'capabilities' => array(
                    'tools' => array(),
                    'resources' => array(
                        'subscribe' => false,
                    ),
                    'prompts' => array(),
                ),
                'tools' => isset($tools_response['tools']) ? $tools_response['tools'] : array(),
                'authentication' => array(
                    'type' => 'bearer',
                    'required' => true,
                    'description' => 'Bearer token authentication required for tool execution. Add your token in the Authorization header as: Bearer YOUR_TOKEN',
                ),
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
     * Handler para mensagens POST enviadas ao SSE
     */
    public function handle_sse_message($request) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        $body = $request->get_json_params();

        if (!$body) {
            return new WP_Error('invalid_request', 'Corpo da requisição inválido', array('status' => 400));
        }

        // Processa como requisição MCP padrão
        return $this->handle_mcp_request($request);
    }

    /**
     * Handler para SSE Stream - Remote MCP Server via SSE
     */
    public function handle_sse_stream($request) {
        // Inicia stream SSE
        WP_Claude_MCP_SSE_Server::start_stream();

        // Envia informações do servidor (discovery)
        WP_Claude_MCP_SSE_Server::send_event('endpoint', array(
            'jsonrpc' => '2.0',
            'method' => 'initialize',
            'result' => array(
                'protocolVersion' => WP_Claude_MCP_Server::MCP_VERSION,
                'serverInfo' => array(
                    'name' => 'WordPress Claude MCP',
                    'version' => WP_CLAUDE_MCP_VERSION,
                ),
                'capabilities' => array(
                    'tools' => array(),
                    'resources' => array(
                        'subscribe' => false,
                    ),
                    'prompts' => array(),
                ),
            ),
        ));

        // Envia lista de ferramentas
        $tools_response = WP_Claude_MCP_Server::list_tools();
        WP_Claude_MCP_SSE_Server::send_event('message', array(
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'result' => $tools_response,
        ));

        // Mantém a conexão viva e aguarda mensagens
        $counter = 0;
        while ($counter < 300) { // 5 minutos máximo
            // Envia ping a cada 10 segundos
            if ($counter % 10 === 0) {
                WP_Claude_MCP_SSE_Server::send_ping();
            }

            // Verifica se há mensagens pendentes (via database ou file)
            // Por enquanto, apenas mantém a conexão viva

            sleep(1);
            $counter++;

            // Verifica se cliente desconectou
            if (connection_aborted()) {
                break;
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

    /**
     * Debug de token (apenas admin)
     */
    public function debug_token($request) {
        global $wpdb;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        $token = isset($_GET['token']) ? $_GET['token'] : '';

        if (empty($token)) {
            return rest_ensure_response(array(
                'error' => 'Token não fornecido',
                'usage' => '/debug-token?token=SEU_TOKEN',
            ));
        }

        $response = array(
            'token_provided' => $token,
            'token_length' => strlen($token),
            'token_sanitized' => sanitize_text_field($token),
            'token_sanitized_length' => strlen(sanitize_text_field($token)),
            'tokens_match' => ($token === sanitize_text_field($token)),
        );

        // Calcula o hash
        $token_hash = hash('sha256', $token);
        $token_hash_sanitized = hash('sha256', sanitize_text_field($token));

        $response['token_hash'] = $token_hash;
        $response['token_hash_sanitized'] = $token_hash_sanitized;
        $response['hashes_match'] = ($token_hash === $token_hash_sanitized);

        // Busca no banco
        $table_name = $wpdb->prefix . 'claude_mcp_tokens';

        $token_data = $wpdb->get_row($wpdb->prepare(
            "SELECT id, user_id, token_name, created_at, expires_at, is_active, last_used FROM $table_name WHERE token_hash = %s",
            $token_hash
        ));

        if ($token_data) {
            $response['database_found'] = true;
            $response['database_data'] = array(
                'id' => $token_data->id,
                'user_id' => $token_data->user_id,
                'token_name' => $token_data->token_name,
                'is_active' => $token_data->is_active,
                'created_at' => $token_data->created_at,
                'expires_at' => $token_data->expires_at,
                'last_used' => $token_data->last_used,
                'is_expired' => ($token_data->expires_at && strtotime($token_data->expires_at) < time()),
            );
        } else {
            $response['database_found'] = false;

            // Tenta com hash sanitizado
            $token_data_sanitized = $wpdb->get_row($wpdb->prepare(
                "SELECT id, user_id, token_name FROM $table_name WHERE token_hash = %s",
                $token_hash_sanitized
            ));

            if ($token_data_sanitized) {
                $response['found_with_sanitized_hash'] = true;
                $response['database_data'] = $token_data_sanitized;
            } else {
                $response['found_with_sanitized_hash'] = false;
            }
        }

        // Lista todos os tokens (apenas hash para segurança)
        $all_tokens = $wpdb->get_results(
            "SELECT id, token_name, SUBSTRING(token_hash, 1, 16) as hash_prefix, is_active, created_at, expires_at FROM $table_name ORDER BY created_at DESC LIMIT 10"
        );

        $response['all_tokens_sample'] = $all_tokens;

        return rest_ensure_response($response);
    }
}
