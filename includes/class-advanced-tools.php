<?php
/**
 * Ferramentas MCP Avançadas para modificação completa do WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Claude_MCP_Advanced_Tools {

    /**
     * Retorna ferramentas avançadas
     */
    public static function get_advanced_tools() {
        return array(
            // ========== PLUGINS ==========
            array(
                'name' => 'wordpress_list_plugins',
                'description' => 'Lista todos os plugins instalados',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(),
                ),
            ),
            array(
                'name' => 'wordpress_activate_plugin',
                'description' => 'Ativa um plugin',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'plugin' => array(
                            'type' => 'string',
                            'description' => 'Caminho do plugin (ex: plugin-folder/plugin-file.php)',
                        ),
                    ),
                    'required' => array('plugin'),
                ),
            ),
            array(
                'name' => 'wordpress_deactivate_plugin',
                'description' => 'Desativa um plugin',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'plugin' => array(
                            'type' => 'string',
                            'description' => 'Caminho do plugin',
                        ),
                    ),
                    'required' => array('plugin'),
                ),
            ),
            array(
                'name' => 'wordpress_delete_plugin',
                'description' => 'Deleta um plugin (deve estar desativado)',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'plugin' => array(
                            'type' => 'string',
                            'description' => 'Caminho do plugin',
                        ),
                    ),
                    'required' => array('plugin'),
                ),
            ),

            // ========== TEMAS ==========
            array(
                'name' => 'wordpress_list_themes',
                'description' => 'Lista todos os temas instalados',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(),
                ),
            ),
            array(
                'name' => 'wordpress_activate_theme',
                'description' => 'Ativa um tema',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'theme' => array(
                            'type' => 'string',
                            'description' => 'Slug do tema',
                        ),
                    ),
                    'required' => array('theme'),
                ),
            ),
            array(
                'name' => 'wordpress_delete_theme',
                'description' => 'Deleta um tema (não pode estar ativo)',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'theme' => array(
                            'type' => 'string',
                            'description' => 'Slug do tema',
                        ),
                    ),
                    'required' => array('theme'),
                ),
            ),

            // ========== MENUS ==========
            array(
                'name' => 'wordpress_list_menus',
                'description' => 'Lista todos os menus de navegação',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(),
                ),
            ),
            array(
                'name' => 'wordpress_create_menu',
                'description' => 'Cria um novo menu',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'name' => array(
                            'type' => 'string',
                            'description' => 'Nome do menu',
                        ),
                    ),
                    'required' => array('name'),
                ),
            ),
            array(
                'name' => 'wordpress_delete_menu',
                'description' => 'Deleta um menu',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'menu_id' => array(
                            'type' => 'number',
                            'description' => 'ID do menu',
                        ),
                    ),
                    'required' => array('menu_id'),
                ),
            ),

            // ========== CONFIGURAÇÕES ==========
            array(
                'name' => 'wordpress_get_option',
                'description' => 'Obtém valor de uma opção do WordPress',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'option_name' => array(
                            'type' => 'string',
                            'description' => 'Nome da opção',
                        ),
                    ),
                    'required' => array('option_name'),
                ),
            ),
            array(
                'name' => 'wordpress_update_option',
                'description' => 'Atualiza uma opção do WordPress',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'option_name' => array(
                            'type' => 'string',
                            'description' => 'Nome da opção',
                        ),
                        'option_value' => array(
                            'description' => 'Valor da opção (pode ser string, número, array, etc)',
                        ),
                    ),
                    'required' => array('option_name', 'option_value'),
                ),
            ),
            array(
                'name' => 'wordpress_list_options',
                'description' => 'Lista opções do WordPress (configurações)',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'search' => array(
                            'type' => 'string',
                            'description' => 'Buscar por nome de opção',
                        ),
                    ),
                ),
            ),

            // ========== USUÁRIOS AVANÇADO ==========
            array(
                'name' => 'wordpress_create_user',
                'description' => 'Cria um novo usuário',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'username' => array(
                            'type' => 'string',
                            'description' => 'Nome de usuário',
                        ),
                        'email' => array(
                            'type' => 'string',
                            'description' => 'Email',
                        ),
                        'password' => array(
                            'type' => 'string',
                            'description' => 'Senha',
                        ),
                        'role' => array(
                            'type' => 'string',
                            'description' => 'Papel do usuário',
                            'default' => 'subscriber',
                        ),
                    ),
                    'required' => array('username', 'email', 'password'),
                ),
            ),
            array(
                'name' => 'wordpress_update_user',
                'description' => 'Atualiza dados de um usuário',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'user_id' => array(
                            'type' => 'number',
                            'description' => 'ID do usuário',
                        ),
                        'email' => array(
                            'type' => 'string',
                            'description' => 'Novo email',
                        ),
                        'role' => array(
                            'type' => 'string',
                            'description' => 'Novo papel',
                        ),
                        'display_name' => array(
                            'type' => 'string',
                            'description' => 'Nome de exibição',
                        ),
                    ),
                    'required' => array('user_id'),
                ),
            ),
            array(
                'name' => 'wordpress_delete_user',
                'description' => 'Deleta um usuário',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'user_id' => array(
                            'type' => 'number',
                            'description' => 'ID do usuário',
                        ),
                        'reassign' => array(
                            'type' => 'number',
                            'description' => 'ID do usuário para reatribuir posts',
                        ),
                    ),
                    'required' => array('user_id'),
                ),
            ),

            // ========== CATEGORIAS E TAGS AVANÇADO ==========
            array(
                'name' => 'wordpress_create_category',
                'description' => 'Cria uma nova categoria',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'name' => array(
                            'type' => 'string',
                            'description' => 'Nome da categoria',
                        ),
                        'description' => array(
                            'type' => 'string',
                            'description' => 'Descrição',
                        ),
                        'parent' => array(
                            'type' => 'number',
                            'description' => 'ID da categoria pai',
                        ),
                    ),
                    'required' => array('name'),
                ),
            ),
            array(
                'name' => 'wordpress_update_category',
                'description' => 'Atualiza uma categoria',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'category_id' => array(
                            'type' => 'number',
                            'description' => 'ID da categoria',
                        ),
                        'name' => array(
                            'type' => 'string',
                            'description' => 'Novo nome',
                        ),
                        'description' => array(
                            'type' => 'string',
                            'description' => 'Nova descrição',
                        ),
                    ),
                    'required' => array('category_id'),
                ),
            ),
            array(
                'name' => 'wordpress_delete_category',
                'description' => 'Deleta uma categoria',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'category_id' => array(
                            'type' => 'number',
                            'description' => 'ID da categoria',
                        ),
                    ),
                    'required' => array('category_id'),
                ),
            ),

            // ========== COMENTÁRIOS AVANÇADO ==========
            array(
                'name' => 'wordpress_approve_comment',
                'description' => 'Aprova um comentário',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'comment_id' => array(
                            'type' => 'number',
                            'description' => 'ID do comentário',
                        ),
                    ),
                    'required' => array('comment_id'),
                ),
            ),
            array(
                'name' => 'wordpress_spam_comment',
                'description' => 'Marca comentário como spam',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'comment_id' => array(
                            'type' => 'number',
                            'description' => 'ID do comentário',
                        ),
                    ),
                    'required' => array('comment_id'),
                ),
            ),
            array(
                'name' => 'wordpress_delete_comment',
                'description' => 'Deleta um comentário',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'comment_id' => array(
                            'type' => 'number',
                            'description' => 'ID do comentário',
                        ),
                        'force_delete' => array(
                            'type' => 'boolean',
                            'description' => 'Deletar permanentemente',
                            'default' => false,
                        ),
                    ),
                    'required' => array('comment_id'),
                ),
            ),

            // ========== POSTS AVANÇADO ==========
            array(
                'name' => 'wordpress_delete_post',
                'description' => 'Deleta um post ou página',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type' => 'number',
                            'description' => 'ID do post',
                        ),
                        'force_delete' => array(
                            'type' => 'boolean',
                            'description' => 'Deletar permanentemente',
                            'default' => false,
                        ),
                    ),
                    'required' => array('post_id'),
                ),
            ),
            array(
                'name' => 'wordpress_duplicate_post',
                'description' => 'Duplica um post',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type' => 'number',
                            'description' => 'ID do post a duplicar',
                        ),
                        'status' => array(
                            'type' => 'string',
                            'description' => 'Status do post duplicado',
                            'default' => 'draft',
                        ),
                    ),
                    'required' => array('post_id'),
                ),
            ),

            // ========== SISTEMA ==========
            array(
                'name' => 'wordpress_get_system_info',
                'description' => 'Obtém informações do sistema WordPress',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(),
                ),
            ),
            array(
                'name' => 'wordpress_clear_cache',
                'description' => 'Limpa o cache do WordPress',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'type' => array(
                            'type' => 'string',
                            'enum' => array('all', 'transients', 'object'),
                            'default' => 'all',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Executa ferramenta avançada
     */
    public static function execute_tool($tool_name, $arguments = array()) {
        $method_name = 'tool_' . str_replace('wordpress_', '', $tool_name);

        if (method_exists(__CLASS__, $method_name)) {
            return call_user_func(array(__CLASS__, $method_name), $arguments);
        }

        return new WP_Error('invalid_tool', 'Ferramenta avançada não encontrada');
    }

    // ========== IMPLEMENTAÇÃO DAS FERRAMENTAS ==========

    /**
     * Lista plugins
     */
    private static function tool_list_plugins($args) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());

        $plugins = array();
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            $plugins[] = array(
                'file' => $plugin_file,
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'author' => $plugin_data['Author'],
                'description' => $plugin_data['Description'],
                'is_active' => in_array($plugin_file, $active_plugins),
            );
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($plugins, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ativa plugin
     */
    private static function tool_activate_plugin($args) {
        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $result = activate_plugin($args['plugin']);

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Plugin ativado com sucesso',
                        'plugin' => $args['plugin'],
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Desativa plugin
     */
    private static function tool_deactivate_plugin($args) {
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        deactivate_plugins($args['plugin']);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Plugin desativado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Deleta plugin
     */
    private static function tool_delete_plugin($args) {
        if (!function_exists('delete_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $result = delete_plugins(array($args['plugin']));

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Plugin deletado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Lista temas
     */
    private static function tool_list_themes($args) {
        $themes = wp_get_themes();
        $current_theme = wp_get_theme();

        $theme_list = array();
        foreach ($themes as $theme_slug => $theme) {
            $theme_list[] = array(
                'slug' => $theme_slug,
                'name' => $theme->get('Name'),
                'version' => $theme->get('Version'),
                'author' => $theme->get('Author'),
                'description' => $theme->get('Description'),
                'is_active' => ($current_theme->get_stylesheet() === $theme_slug),
            );
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($theme_list, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ativa tema
     */
    private static function tool_activate_theme($args) {
        switch_theme($args['theme']);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Tema ativado com sucesso',
                        'theme' => $args['theme'],
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Deleta tema
     */
    private static function tool_delete_theme($args) {
        if (!function_exists('delete_theme')) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }

        $result = delete_theme($args['theme']);

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Tema deletado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Lista menus
     */
    private static function tool_list_menus($args) {
        $menus = wp_get_nav_menus();
        $menu_list = array();

        foreach ($menus as $menu) {
            $menu_list[] = array(
                'id' => $menu->term_id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'count' => $menu->count,
            );
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($menu_list, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Cria menu
     */
    private static function tool_create_menu($args) {
        $menu_id = wp_create_nav_menu($args['name']);

        if (is_wp_error($menu_id)) {
            return $menu_id;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'menu_id' => $menu_id,
                        'message' => 'Menu criado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Deleta menu
     */
    private static function tool_delete_menu($args) {
        $result = wp_delete_nav_menu($args['menu_id']);

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Menu deletado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Obtém opção
     */
    private static function tool_get_option($args) {
        $value = get_option($args['option_name']);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'option_name' => $args['option_name'],
                        'value' => $value,
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Atualiza opção
     */
    private static function tool_update_option($args) {
        $result = update_option($args['option_name'], $args['option_value']);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => $result,
                        'option_name' => $args['option_name'],
                        'message' => $result ? 'Opção atualizada' : 'Opção não foi alterada',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Lista opções
     */
    private static function tool_list_options($args) {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';
        $query = "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name NOT LIKE '%\_transient\_%'";

        if ($search) {
            $query .= $wpdb->prepare(" AND option_name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $query .= " LIMIT 50";

        $options = $wpdb->get_results($query);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($options, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Cria usuário
     */
    private static function tool_create_user($args) {
        $user_id = wp_create_user($args['username'], $args['password'], $args['email']);

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        if (isset($args['role'])) {
            $user = new WP_User($user_id);
            $user->set_role($args['role']);
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'user_id' => $user_id,
                        'message' => 'Usuário criado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Atualiza usuário
     */
    private static function tool_update_user($args) {
        $user_data = array('ID' => $args['user_id']);

        if (isset($args['email'])) {
            $user_data['user_email'] = $args['email'];
        }

        if (isset($args['display_name'])) {
            $user_data['display_name'] = $args['display_name'];
        }

        $user_id = wp_update_user($user_data);

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        if (isset($args['role'])) {
            $user = new WP_User($args['user_id']);
            $user->set_role($args['role']);
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Usuário atualizado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Deleta usuário
     */
    private static function tool_delete_user($args) {
        if (!function_exists('wp_delete_user')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }

        $reassign = isset($args['reassign']) ? $args['reassign'] : null;
        $result = wp_delete_user($args['user_id'], $reassign);

        if (!$result) {
            return new WP_Error('delete_failed', 'Falha ao deletar usuário');
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Usuário deletado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Cria categoria
     */
    private static function tool_create_category($args) {
        $cat_args = array('cat_name' => $args['name']);

        if (isset($args['description'])) {
            $cat_args['category_description'] = $args['description'];
        }

        if (isset($args['parent'])) {
            $cat_args['category_parent'] = $args['parent'];
        }

        $category_id = wp_insert_category($cat_args);

        if (is_wp_error($category_id)) {
            return $category_id;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'category_id' => $category_id,
                        'message' => 'Categoria criada com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Atualiza categoria
     */
    private static function tool_update_category($args) {
        $cat_args = array('cat_ID' => $args['category_id']);

        if (isset($args['name'])) {
            $cat_args['cat_name'] = $args['name'];
        }

        if (isset($args['description'])) {
            $cat_args['category_description'] = $args['description'];
        }

        $result = wp_update_category($cat_args);

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Categoria atualizada',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Deleta categoria
     */
    private static function tool_delete_category($args) {
        $result = wp_delete_category($args['category_id']);

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Categoria deletada',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Aprova comentário
     */
    private static function tool_approve_comment($args) {
        $result = wp_set_comment_status($args['comment_id'], 'approve');

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => $result,
                        'message' => 'Comentário aprovado',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Marca como spam
     */
    private static function tool_spam_comment($args) {
        $result = wp_spam_comment($args['comment_id']);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => $result,
                        'message' => 'Comentário marcado como spam',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Deleta comentário
     */
    private static function tool_delete_comment($args) {
        $force = isset($args['force_delete']) ? $args['force_delete'] : false;
        $result = wp_delete_comment($args['comment_id'], $force);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => $result,
                        'message' => 'Comentário deletado',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Deleta post
     */
    private static function tool_delete_post($args) {
        $force = isset($args['force_delete']) ? $args['force_delete'] : false;
        $result = wp_delete_post($args['post_id'], $force);

        if (!$result) {
            return new WP_Error('delete_failed', 'Falha ao deletar post');
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Post deletado',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Duplica post
     */
    private static function tool_duplicate_post($args) {
        $post = get_post($args['post_id']);

        if (!$post) {
            return new WP_Error('post_not_found', 'Post não encontrado');
        }

        $new_post = array(
            'post_title' => $post->post_title . ' (cópia)',
            'post_content' => $post->post_content,
            'post_status' => isset($args['status']) ? $args['status'] : 'draft',
            'post_type' => $post->post_type,
            'post_author' => get_current_user_id(),
        );

        $new_post_id = wp_insert_post($new_post);

        if (is_wp_error($new_post_id)) {
            return $new_post_id;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'new_post_id' => $new_post_id,
                        'message' => 'Post duplicado com sucesso',
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Informações do sistema
     */
    private static function tool_get_system_info($args) {
        global $wp_version;

        $info = array(
            'wordpress_version' => $wp_version,
            'php_version' => PHP_VERSION,
            'mysql_version' => $GLOBALS['wpdb']->db_version(),
            'theme' => wp_get_theme()->get('Name'),
            'plugins_count' => count(get_option('active_plugins', array())),
            'posts_count' => wp_count_posts()->publish,
            'pages_count' => wp_count_posts('page')->publish,
            'users_count' => count_users()['total_users'],
            'site_url' => get_site_url(),
            'home_url' => get_home_url(),
            'upload_max_size' => size_format(wp_max_upload_size()),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        );

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($info, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Limpa cache
     */
    private static function tool_clear_cache($args) {
        $type = isset($args['type']) ? $args['type'] : 'all';

        if ($type === 'all' || $type === 'transients') {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");
        }

        if ($type === 'all' || $type === 'object') {
            wp_cache_flush();
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'message' => 'Cache limpo com sucesso',
                        'type' => $type,
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }
}
