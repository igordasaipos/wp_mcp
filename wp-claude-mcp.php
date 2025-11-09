<?php
/**
 * Plugin Name: WordPress Claude MCP Integration
 * Plugin URI: https://github.com/igordasaipos/wp_mcp
 * Description: Integração do WordPress com Claude AI usando Model Context Protocol (MCP) - 35 ferramentas + SSE
 * Version: 1.1.0
 * Author: Igor da Saipos
 * Author URI: https://github.com/igordasaipos
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-claude-mcp
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Evita acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Define constantes do plugin
define('WP_CLAUDE_MCP_VERSION', '1.1.0');
define('WP_CLAUDE_MCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_CLAUDE_MCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_CLAUDE_MCP_PLUGIN_FILE', __FILE__);

/**
 * Classe principal do plugin
 */
class WP_Claude_MCP {

    /**
     * Instância única do plugin
     */
    private static $instance = null;

    /**
     * Retorna a instância única do plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor privado (Singleton)
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Carrega as dependências do plugin
     */
    private function load_dependencies() {
        require_once WP_CLAUDE_MCP_PLUGIN_DIR . 'includes/class-jwt-auth.php';
        require_once WP_CLAUDE_MCP_PLUGIN_DIR . 'includes/class-mcp-server.php';
        require_once WP_CLAUDE_MCP_PLUGIN_DIR . 'includes/class-mcp-tools.php';
        require_once WP_CLAUDE_MCP_PLUGIN_DIR . 'includes/class-advanced-tools.php';
        require_once WP_CLAUDE_MCP_PLUGIN_DIR . 'includes/class-sse-server.php';
        require_once WP_CLAUDE_MCP_PLUGIN_DIR . 'includes/class-admin-ui.php';
        require_once WP_CLAUDE_MCP_PLUGIN_DIR . 'includes/class-rest-api.php';
    }

    /**
     * Inicializa os hooks do WordPress
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Ativa o plugin
     */
    public function activate() {
        // Cria as tabelas necessárias
        $this->create_database_tables();

        // Define opções padrão
        add_option('wp_claude_mcp_enabled', true);
        add_option('wp_claude_mcp_jwt_secret', wp_generate_password(64, true, true));

        // Limpa o cache de rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Desativa o plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Cria as tabelas do banco de dados
     */
    private function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'claude_mcp_tokens';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            token_name varchar(255) NOT NULL,
            token_hash varchar(255) NOT NULL,
            capabilities text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            last_used datetime,
            expires_at datetime,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY token_hash (token_hash)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Inicialização do plugin
     */
    public function init() {
        // Carrega traduções
        load_plugin_textdomain('wp-claude-mcp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Registra as rotas da REST API
     */
    public function register_rest_routes() {
        $rest_api = new WP_Claude_MCP_REST_API();
        $rest_api->register_routes();
    }

    /**
     * Adiciona menu de administração
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Claude MCP', 'wp-claude-mcp'),
            __('Claude MCP', 'wp-claude-mcp'),
            'manage_options',
            'wp-claude-mcp',
            array($this, 'render_admin_page'),
            'dashicons-admin-network',
            30
        );
    }

    /**
     * Renderiza a página de administração
     */
    public function render_admin_page() {
        WP_Claude_MCP_Admin_UI::render_settings_page();
    }

    /**
     * Carrega os assets do admin
     */
    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_wp-claude-mcp' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'wp-claude-mcp-admin',
            WP_CLAUDE_MCP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WP_CLAUDE_MCP_VERSION
        );

        wp_enqueue_script(
            'wp-claude-mcp-admin',
            WP_CLAUDE_MCP_PLUGIN_URL . 'assets/js/admin.js',
            array('wp-element', 'wp-components', 'wp-i18n'),
            WP_CLAUDE_MCP_VERSION,
            true
        );

        wp_localize_script('wp-claude-mcp-admin', 'wpClaudeMCP', array(
            'apiUrl' => rest_url('mcp/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'siteUrl' => get_site_url(),
        ));
    }
}

// Inicializa o plugin
function wp_claude_mcp_init() {
    return WP_Claude_MCP::get_instance();
}

// Inicia o plugin
wp_claude_mcp_init();
