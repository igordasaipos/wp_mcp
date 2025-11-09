<?php
/**
 * Ferramentas MCP para WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Claude_MCP_Tools {

    /**
     * Retorna todas as ferramentas disponíveis
     */
    public static function get_available_tools() {
        $basic_tools = array(
            // Posts
            array(
                'name' => 'wordpress_search_posts',
                'description' => 'Busca posts no WordPress por título, conteúdo ou autor',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'search' => array(
                            'type' => 'string',
                            'description' => 'Termo de busca',
                        ),
                        'limit' => array(
                            'type' => 'number',
                            'description' => 'Número máximo de resultados',
                            'default' => 10,
                        ),
                        'post_type' => array(
                            'type' => 'string',
                            'description' => 'Tipo de post (post, page, etc)',
                            'default' => 'post',
                        ),
                    ),
                    'required' => array('search'),
                ),
            ),
            array(
                'name' => 'wordpress_create_post',
                'description' => 'Cria um novo post ou página no WordPress',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'title' => array(
                            'type' => 'string',
                            'description' => 'Título do post',
                        ),
                        'content' => array(
                            'type' => 'string',
                            'description' => 'Conteúdo do post (HTML ou Markdown)',
                        ),
                        'status' => array(
                            'type' => 'string',
                            'enum' => array('publish', 'draft', 'pending'),
                            'description' => 'Status do post',
                            'default' => 'draft',
                        ),
                        'post_type' => array(
                            'type' => 'string',
                            'description' => 'Tipo de post',
                            'default' => 'post',
                        ),
                        'categories' => array(
                            'type' => 'array',
                            'description' => 'IDs das categorias',
                            'items' => array('type' => 'number'),
                        ),
                        'tags' => array(
                            'type' => 'array',
                            'description' => 'Tags do post',
                            'items' => array('type' => 'string'),
                        ),
                    ),
                    'required' => array('title', 'content'),
                ),
            ),
            array(
                'name' => 'wordpress_update_post',
                'description' => 'Atualiza um post existente',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type' => 'number',
                            'description' => 'ID do post',
                        ),
                        'title' => array(
                            'type' => 'string',
                            'description' => 'Novo título',
                        ),
                        'content' => array(
                            'type' => 'string',
                            'description' => 'Novo conteúdo',
                        ),
                        'status' => array(
                            'type' => 'string',
                            'enum' => array('publish', 'draft', 'pending', 'trash'),
                            'description' => 'Novo status',
                        ),
                    ),
                    'required' => array('post_id'),
                ),
            ),
            array(
                'name' => 'wordpress_get_post',
                'description' => 'Obtém detalhes de um post específico',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type' => 'number',
                            'description' => 'ID do post',
                        ),
                    ),
                    'required' => array('post_id'),
                ),
            ),

            // Categorias e Tags
            array(
                'name' => 'wordpress_list_categories',
                'description' => 'Lista todas as categorias do site',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'hide_empty' => array(
                            'type' => 'boolean',
                            'description' => 'Esconder categorias vazias',
                            'default' => false,
                        ),
                    ),
                ),
            ),

            // Mídia
            array(
                'name' => 'wordpress_search_media',
                'description' => 'Busca arquivos de mídia',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'search' => array(
                            'type' => 'string',
                            'description' => 'Termo de busca',
                        ),
                        'media_type' => array(
                            'type' => 'string',
                            'enum' => array('image', 'video', 'audio', 'application'),
                            'description' => 'Tipo de mídia',
                        ),
                        'limit' => array(
                            'type' => 'number',
                            'default' => 10,
                        ),
                    ),
                ),
            ),

            // Usuários
            array(
                'name' => 'wordpress_list_users',
                'description' => 'Lista usuários do site',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'role' => array(
                            'type' => 'string',
                            'description' => 'Filtrar por papel (administrator, editor, author, etc)',
                        ),
                        'limit' => array(
                            'type' => 'number',
                            'default' => 10,
                        ),
                    ),
                ),
            ),

            // Comentários
            array(
                'name' => 'wordpress_get_comments',
                'description' => 'Obtém comentários do site',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type' => 'number',
                            'description' => 'Filtrar por post',
                        ),
                        'status' => array(
                            'type' => 'string',
                            'enum' => array('approve', 'hold', 'spam', 'trash'),
                            'description' => 'Status do comentário',
                        ),
                        'limit' => array(
                            'type' => 'number',
                            'default' => 10,
                        ),
                    ),
                ),
            ),

            // Analytics
            array(
                'name' => 'wordpress_get_statistics',
                'description' => 'Obtém estatísticas do site',
                'inputSchema' => array(
                    'type' => 'object',
                    'properties' => array(),
                ),
            ),
        );

        // Adiciona ferramentas avançadas
        $advanced_tools = WP_Claude_MCP_Advanced_Tools::get_advanced_tools();

        return array_merge($basic_tools, $advanced_tools);
    }

    /**
     * Executa uma ferramenta
     */
    public static function execute_tool($tool_name, $arguments = array()) {
        $method_name = 'tool_' . str_replace('wordpress_', '', $tool_name);

        // Tenta executar ferramenta básica
        if (method_exists(__CLASS__, $method_name)) {
            return call_user_func(array(__CLASS__, $method_name), $arguments);
        }

        // Tenta executar ferramenta avançada
        $result = WP_Claude_MCP_Advanced_Tools::execute_tool($tool_name, $arguments);
        if (!is_wp_error($result) || $result->get_error_code() !== 'invalid_tool') {
            return $result;
        }

        return new WP_Error('invalid_tool', 'Ferramenta não encontrada');
    }

    /**
     * Ferramenta: Buscar posts
     */
    private static function tool_search_posts($args) {
        $query_args = array(
            's' => $args['search'],
            'posts_per_page' => isset($args['limit']) ? $args['limit'] : 10,
            'post_type' => isset($args['post_type']) ? $args['post_type'] : 'post',
            'post_status' => 'any',
        );

        $query = new WP_Query($query_args);
        $posts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'author' => get_the_author(),
                    'date' => get_the_date('c'),
                    'status' => get_post_status(),
                    'url' => get_permalink(),
                    'type' => get_post_type(),
                );
            }
            wp_reset_postdata();
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'found' => $query->found_posts,
                        'posts' => $posts,
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Criar post
     */
    private static function tool_create_post($args) {
        $post_data = array(
            'post_title' => $args['title'],
            'post_content' => $args['content'],
            'post_status' => isset($args['status']) ? $args['status'] : 'draft',
            'post_type' => isset($args['post_type']) ? $args['post_type'] : 'post',
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Adiciona categorias
        if (isset($args['categories']) && is_array($args['categories'])) {
            wp_set_post_categories($post_id, $args['categories']);
        }

        // Adiciona tags
        if (isset($args['tags']) && is_array($args['tags'])) {
            wp_set_post_tags($post_id, $args['tags']);
        }

        $post = get_post($post_id);

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'post_id' => $post_id,
                        'post_url' => get_permalink($post_id),
                        'edit_url' => get_edit_post_link($post_id, 'raw'),
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Atualizar post
     */
    private static function tool_update_post($args) {
        $post_data = array(
            'ID' => $args['post_id'],
        );

        if (isset($args['title'])) {
            $post_data['post_title'] = $args['title'];
        }

        if (isset($args['content'])) {
            $post_data['post_content'] = $args['content'];
        }

        if (isset($args['status'])) {
            $post_data['post_status'] = $args['status'];
        }

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'success' => true,
                        'post_id' => $args['post_id'],
                        'updated' => true,
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Obter post
     */
    private static function tool_get_post($args) {
        $post = get_post($args['post_id']);

        if (!$post) {
            return new WP_Error('post_not_found', 'Post não encontrado');
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode(array(
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'content' => $post->post_content,
                        'excerpt' => $post->post_excerpt,
                        'status' => $post->post_status,
                        'type' => $post->post_type,
                        'author' => get_the_author_meta('display_name', $post->post_author),
                        'date' => $post->post_date,
                        'modified' => $post->post_modified,
                        'url' => get_permalink($post->ID),
                    ), JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Listar categorias
     */
    private static function tool_list_categories($args) {
        $categories = get_categories(array(
            'hide_empty' => isset($args['hide_empty']) ? $args['hide_empty'] : false,
        ));

        $result = array();
        foreach ($categories as $category) {
            $result[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count,
                'description' => $category->description,
            );
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($result, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Buscar mídia
     */
    private static function tool_search_media($args) {
        $query_args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => isset($args['limit']) ? $args['limit'] : 10,
        );

        if (isset($args['search'])) {
            $query_args['s'] = $args['search'];
        }

        if (isset($args['media_type'])) {
            $query_args['post_mime_type'] = $args['media_type'];
        }

        $query = new WP_Query($query_args);
        $media = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $media[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => wp_get_attachment_url(get_the_ID()),
                    'type' => get_post_mime_type(),
                    'date' => get_the_date('c'),
                );
            }
            wp_reset_postdata();
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($media, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Listar usuários
     */
    private static function tool_list_users($args) {
        $query_args = array(
            'number' => isset($args['limit']) ? $args['limit'] : 10,
        );

        if (isset($args['role'])) {
            $query_args['role'] = $args['role'];
        }

        $users = get_users($query_args);
        $result = array();

        foreach ($users as $user) {
            $result[] = array(
                'id' => $user->ID,
                'username' => $user->user_login,
                'display_name' => $user->display_name,
                'email' => $user->user_email,
                'roles' => $user->roles,
                'registered' => $user->user_registered,
            );
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($result, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Obter comentários
     */
    private static function tool_get_comments($args) {
        $query_args = array(
            'number' => isset($args['limit']) ? $args['limit'] : 10,
        );

        if (isset($args['post_id'])) {
            $query_args['post_id'] = $args['post_id'];
        }

        if (isset($args['status'])) {
            $query_args['status'] = $args['status'];
        }

        $comments = get_comments($query_args);
        $result = array();

        foreach ($comments as $comment) {
            $result[] = array(
                'id' => $comment->comment_ID,
                'post_id' => $comment->comment_post_ID,
                'author' => $comment->comment_author,
                'email' => $comment->comment_author_email,
                'content' => $comment->comment_content,
                'date' => $comment->comment_date,
                'status' => $comment->comment_approved,
            );
        }

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($result, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }

    /**
     * Ferramenta: Obter estatísticas
     */
    private static function tool_get_statistics($args) {
        $stats = array(
            'posts' => array(
                'published' => wp_count_posts('post')->publish,
                'draft' => wp_count_posts('post')->draft,
                'total' => wp_count_posts('post')->publish + wp_count_posts('post')->draft,
            ),
            'pages' => array(
                'published' => wp_count_posts('page')->publish,
                'draft' => wp_count_posts('page')->draft,
                'total' => wp_count_posts('page')->publish + wp_count_posts('page')->draft,
            ),
            'comments' => array(
                'approved' => wp_count_comments()->approved,
                'pending' => wp_count_comments()->moderated,
                'spam' => wp_count_comments()->spam,
                'total' => wp_count_comments()->total_comments,
            ),
            'users' => count_users(),
            'categories' => wp_count_terms('category'),
            'tags' => wp_count_terms('post_tag'),
        );

        return array(
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => json_encode($stats, JSON_PRETTY_PRINT),
                ),
            ),
        );
    }
}
