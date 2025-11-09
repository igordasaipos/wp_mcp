<?php
/**
 * Classe de autenticação JWT
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Claude_MCP_JWT_Auth {

    /**
     * Gera um novo token JWT
     */
    public static function generate_token($user_id, $token_name, $capabilities = array(), $expires_in = null) {
        global $wpdb;

        // Gera um token aleatório
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);

        // Define a expiração (padrão: 1 ano)
        $expires_at = $expires_in ? date('Y-m-d H:i:s', strtotime($expires_in)) : date('Y-m-d H:i:s', strtotime('+1 year'));

        // Insere no banco de dados
        $table_name = $wpdb->prefix . 'claude_mcp_tokens';
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'token_name' => $token_name,
                'token_hash' => $token_hash,
                'capabilities' => json_encode($capabilities),
                'expires_at' => $expires_at,
                'is_active' => 1,
            ),
            array('%d', '%s', '%s', '%s', '%s', '%d')
        );

        if ($wpdb->insert_id) {
            return array(
                'token' => $token,
                'token_id' => $wpdb->insert_id,
                'expires_at' => $expires_at,
            );
        }

        return false;
    }

    /**
     * Valida um token JWT
     */
    public static function validate_token($token) {
        global $wpdb;

        $token_hash = hash('sha256', $token);
        $table_name = $wpdb->prefix . 'claude_mcp_tokens';

        $token_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE token_hash = %s AND is_active = 1",
            $token_hash
        ));

        if (!$token_data) {
            return false;
        }

        // Verifica se o token expirou
        if ($token_data->expires_at && strtotime($token_data->expires_at) < time()) {
            return false;
        }

        // Atualiza o último uso
        $wpdb->update(
            $table_name,
            array('last_used' => current_time('mysql')),
            array('id' => $token_data->id),
            array('%s'),
            array('%d')
        );

        return array(
            'user_id' => $token_data->user_id,
            'capabilities' => json_decode($token_data->capabilities, true),
            'token_id' => $token_data->id,
        );
    }

    /**
     * Revoga um token
     */
    public static function revoke_token($token_id, $user_id = null) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'claude_mcp_tokens';

        $where = array('id' => $token_id);
        $where_format = array('%d');

        // Se user_id for fornecido, adiciona à condição WHERE
        if ($user_id) {
            $where['user_id'] = $user_id;
            $where_format[] = '%d';
        }

        return $wpdb->update(
            $table_name,
            array('is_active' => 0),
            $where,
            array('%d'),
            $where_format
        );
    }

    /**
     * Lista todos os tokens de um usuário
     */
    public static function list_user_tokens($user_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'claude_mcp_tokens';

        $tokens = $wpdb->get_results($wpdb->prepare(
            "SELECT id, token_name, created_at, last_used, expires_at, is_active, capabilities
             FROM $table_name
             WHERE user_id = %d
             ORDER BY created_at DESC",
            $user_id
        ));

        return $tokens;
    }

    /**
     * Autentica uma requisição usando o header Authorization
     */
    public static function authenticate_request() {
        $auth_header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

        if (empty($auth_header)) {
            return false;
        }

        // Extrai o token do header "Bearer {token}"
        if (preg_match('/Bearer\s+(.+)/i', $auth_header, $matches)) {
            $token = $matches[1];
            return self::validate_token($token);
        }

        return false;
    }
}
