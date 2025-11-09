<?php
/**
 * Interface de administração
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Claude_MCP_Admin_UI {

    /**
     * Renderiza a página de configurações
     */
    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Claude MCP Settings', 'wp-claude-mcp'); ?></h1>

            <div id="wp-claude-mcp-admin-root"></div>

            <div class="wp-claude-mcp-info-box" style="max-width: 800px; margin-top: 30px; padding: 20px; background: #f0f0f1; border-left: 4px solid #2271b1;">
                <h2><?php echo esc_html__('Como Conectar ao Claude Desktop', 'wp-claude-mcp'); ?></h2>

                <ol>
                    <li><strong><?php echo esc_html__('Crie um Token', 'wp-claude-mcp'); ?>:</strong>
                        <?php echo esc_html__('Use o formulário acima para gerar um novo token de acesso', 'wp-claude-mcp'); ?>
                    </li>

                    <li><strong><?php echo esc_html__('Configure o Claude Desktop', 'wp-claude-mcp'); ?>:</strong>
                        <?php echo esc_html__('Adicione a configuração abaixo ao arquivo', 'wp-claude-mcp'); ?>
                        <code>claude_desktop_config.json</code>:

                        <pre style="background: #fff; padding: 15px; margin: 10px 0; overflow-x: auto;">{
  "mcpServers": {
    "wordpress": {
      "url": "<?php echo esc_url(rest_url('wp/v2/claude-mcp/mcp')); ?>",
      "headers": {
        "Authorization": "Bearer SEU_TOKEN_AQUI"
      }
    }
  }
}</pre>
                    </li>

                    <li><strong><?php echo esc_html__('Reinicie o Claude Desktop', 'wp-claude-mcp'); ?></strong></li>

                    <li><strong><?php echo esc_html__('Teste a Conexão', 'wp-claude-mcp'); ?>:</strong>
                        <?php echo esc_html__('No Claude, experimente comandos como:', 'wp-claude-mcp'); ?>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li>"Liste meus posts mais recentes"</li>
                            <li>"Crie um novo post sobre [tópico]"</li>
                            <li>"Mostre as estatísticas do meu site"</li>
                        </ul>
                    </li>
                </ol>

                <h3><?php echo esc_html__('Localização do arquivo de configuração', 'wp-claude-mcp'); ?>:</h3>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <li><strong>macOS:</strong> <code>~/Library/Application Support/Claude/claude_desktop_config.json</code></li>
                    <li><strong>Windows:</strong> <code>%APPDATA%\Claude\claude_desktop_config.json</code></li>
                    <li><strong>Linux:</strong> <code>~/.config/Claude/claude_desktop_config.json</code></li>
                </ul>
            </div>

            <div class="wp-claude-mcp-api-info" style="max-width: 800px; margin-top: 20px; padding: 20px; background: #f0f0f1;">
                <h2><?php echo esc_html__('Informações da API', 'wp-claude-mcp'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><?php echo esc_html__('Endpoint MCP', 'wp-claude-mcp'); ?>:</th>
                        <td>
                            <code><?php echo esc_url(rest_url('wp/v2/claude-mcp/mcp')); ?></code>
                            <button class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js(rest_url('wp/v2/claude-mcp/mcp')); ?>')">
                                <?php echo esc_html__('Copiar', 'wp-claude-mcp'); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Versão do Plugin', 'wp-claude-mcp'); ?>:</th>
                        <td><?php echo esc_html(WP_CLAUDE_MCP_VERSION); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Versão do Protocolo MCP', 'wp-claude-mcp'); ?>:</th>
                        <td><?php echo esc_html(WP_Claude_MCP_Server::MCP_VERSION); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
}
