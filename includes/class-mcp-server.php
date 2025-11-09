<?php
/**
 * Servidor MCP (Model Context Protocol)
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Claude_MCP_Server {

    /**
     * Versão do protocolo MCP
     */
    const MCP_VERSION = '2024-11-05';

    /**
     * Informações do servidor
     */
    public static function get_server_info() {
        return array(
            'name' => 'WordPress Claude MCP',
            'version' => WP_CLAUDE_MCP_VERSION,
            'protocol_version' => self::MCP_VERSION,
            'capabilities' => array(
                'tools' => true,
                'resources' => true,
                'prompts' => true,
            ),
        );
    }

    /**
     * Lista todas as ferramentas disponíveis
     */
    public static function list_tools() {
        $tools = WP_Claude_MCP_Tools::get_available_tools();

        return array(
            'tools' => $tools,
        );
    }

    /**
     * Executa uma ferramenta
     */
    public static function call_tool($tool_name, $arguments = array()) {
        return WP_Claude_MCP_Tools::execute_tool($tool_name, $arguments);
    }

    /**
     * Lista recursos disponíveis
     */
    public static function list_resources() {
        return array(
            'resources' => array(
                array(
                    'uri' => 'wordpress://site/info',
                    'name' => 'Site Information',
                    'description' => 'Informações gerais do site WordPress',
                    'mimeType' => 'application/json',
                ),
                array(
                    'uri' => 'wordpress://site/settings',
                    'name' => 'Site Settings',
                    'description' => 'Configurações do site',
                    'mimeType' => 'application/json',
                ),
            ),
        );
    }

    /**
     * Lê um recurso
     */
    public static function read_resource($uri) {
        switch ($uri) {
            case 'wordpress://site/info':
                return array(
                    'contents' => array(
                        array(
                            'uri' => $uri,
                            'mimeType' => 'application/json',
                            'text' => json_encode(array(
                                'name' => get_bloginfo('name'),
                                'description' => get_bloginfo('description'),
                                'url' => get_site_url(),
                                'admin_email' => get_option('admin_email'),
                                'wordpress_version' => get_bloginfo('version'),
                                'language' => get_bloginfo('language'),
                            ), JSON_PRETTY_PRINT),
                        ),
                    ),
                );

            case 'wordpress://site/settings':
                return array(
                    'contents' => array(
                        array(
                            'uri' => $uri,
                            'mimeType' => 'application/json',
                            'text' => json_encode(array(
                                'timezone' => get_option('timezone_string'),
                                'date_format' => get_option('date_format'),
                                'time_format' => get_option('time_format'),
                                'posts_per_page' => get_option('posts_per_page'),
                                'default_category' => get_option('default_category'),
                            ), JSON_PRETTY_PRINT),
                        ),
                    ),
                );

            default:
                return new WP_Error('invalid_resource', 'Recurso não encontrado');
        }
    }

    /**
     * Lista prompts disponíveis
     */
    public static function list_prompts() {
        return array(
            'prompts' => array(
                array(
                    'name' => 'create_post',
                    'description' => 'Auxiliar na criação de um novo post',
                    'arguments' => array(
                        array(
                            'name' => 'topic',
                            'description' => 'Tópico do post',
                            'required' => true,
                        ),
                    ),
                ),
                array(
                    'name' => 'analyze_site',
                    'description' => 'Analisar e fornecer insights sobre o site',
                    'arguments' => array(),
                ),
            ),
        );
    }

    /**
     * Obtém um prompt
     */
    public static function get_prompt($prompt_name, $arguments = array()) {
        switch ($prompt_name) {
            case 'create_post':
                $topic = isset($arguments['topic']) ? $arguments['topic'] : 'geral';
                return array(
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => array(
                                'type' => 'text',
                                'text' => "Vou te ajudar a criar um post sobre: {$topic}. Para isso, preciso que você me forneça mais detalhes sobre o que gostaria de abordar.",
                            ),
                        ),
                    ),
                );

            case 'analyze_site':
                $site_info = self::read_resource('wordpress://site/info');
                $site_data = json_decode($site_info['contents'][0]['text'], true);

                return array(
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => array(
                                'type' => 'text',
                                'text' => "Analise este site WordPress:\n" . json_encode($site_data, JSON_PRETTY_PRINT),
                            ),
                        ),
                    ),
                );

            default:
                return new WP_Error('invalid_prompt', 'Prompt não encontrado');
        }
    }
}
