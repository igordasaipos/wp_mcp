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

            <div class="wp-claude-mcp-info-box" style="max-width: 800px; margin-top: 30px; padding: 20px; background: #d5e8d4; border-left: 4px solid #00a32a;">
                <h2>🌐 <?php echo esc_html__('Como Conectar ao Claude Web', 'wp-claude-mcp'); ?></h2>

                <p><strong><?php echo esc_html__('Copie estas URLs para conectar via Claude.ai:', 'wp-claude-mcp'); ?></strong></p>

                <div style="background: #fff; padding: 15px; margin: 10px 0; border-radius: 4px; border: 2px solid #00a32a;">
                    <strong><?php echo esc_html__('⚡ SSE URL (RECOMENDADA - Padrão AI Engine):', 'wp-claude-mcp'); ?></strong><br>
                    <code style="font-size: 13px; word-break: break-all; display: block; margin: 5px 0;" id="sse-url-template"><?php echo esc_url(rest_url('mcp/v1/sse')); ?>?token=SEU_TOKEN_AQUI</code>
                    <button class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('sse-url-template').textContent)">
                        <?php echo esc_html__('📋 Copiar Template', 'wp-claude-mcp'); ?>
                    </button>
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #00a32a; font-weight: bold;">
                        <?php echo esc_html__('✅ Compatível com AI Engine! Token direto na URL.', 'wp-claude-mcp'); ?><br>
                        <?php echo esc_html__('💡 Após gerar um token, a URL completa aparecerá automaticamente!', 'wp-claude-mcp'); ?>
                    </p>
                </div>

                <h3><?php echo esc_html__('Passos para Conectar (MÉTODO RECOMENDADO - SSE):', 'wp-claude-mcp'); ?></h3>
                <ol>
                    <li><strong><?php echo esc_html__('Crie um Token', 'wp-claude-mcp'); ?>:</strong>
                        <?php echo esc_html__('Use o formulário acima para gerar um novo token de acesso', 'wp-claude-mcp'); ?>
                    </li>

                    <li><strong><?php echo esc_html__('Copie a URL SSE completa', 'wp-claude-mcp'); ?>:</strong>
                        <?php echo esc_html__('Após gerar o token, copie a URL SSE que aparece automaticamente (já inclui o token)', 'wp-claude-mcp'); ?>
                    </li>

                    <li><strong><?php echo esc_html__('Acesse Claude.ai', 'wp-claude-mcp'); ?>:</strong>
                        <?php echo esc_html__('Vá em Configurações > Integrações > Model Context Protocol', 'wp-claude-mcp'); ?>
                    </li>

                    <li><strong><?php echo esc_html__('Adicione o Servidor SSE', 'wp-claude-mcp'); ?>:</strong>
                        <ul style="list-style-type: disc; margin-left: 20px; margin-top: 5px;">
                            <li><strong>Nome:</strong> <?php echo esc_html(get_bloginfo('name')); ?> (ou qualquer nome)</li>
                            <li><strong>SSE Server URL:</strong> Cole a URL SSE com token (ex: ...sse?token=xyz...)</li>
                            <li><strong>Autenticação:</strong> ✅ Token já está na URL! Não precisa configurar header.</li>
                        </ul>
                    </li>

                    <li><strong><?php echo esc_html__('Teste a Conexão', 'wp-claude-mcp'); ?>:</strong>
                        <?php echo esc_html__('No Claude, experimente:', 'wp-claude-mcp'); ?>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li>"Liste meus posts mais recentes"</li>
                            <li>"Mostre as estatísticas do meu site"</li>
                            <li>"Crie um novo post sobre [tópico]"</li>
                        </ul>
                    </li>
                </ol>
            </div>

            <div class="wp-claude-mcp-info-box" style="max-width: 800px; margin-top: 20px; padding: 20px; background: #f0f0f1; border-left: 4px solid #2271b1;">
                <h2>💻 <?php echo esc_html__('Como Conectar ao Claude Desktop', 'wp-claude-mcp'); ?></h2>

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
      "url": "<?php echo esc_url(rest_url('mcp/v1/mcp')); ?>",
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
                <h2><?php echo esc_html__('📡 Endpoints da API', 'wp-claude-mcp'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><?php echo esc_html__('SSE Endpoint (Streaming)', 'wp-claude-mcp'); ?>:</th>
                        <td>
                            <code style="font-size: 13px;"><?php echo esc_url(rest_url('mcp/v1/sse')); ?></code>
                            <button class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js(rest_url('mcp/v1/sse')); ?>')">
                                <?php echo esc_html__('Copiar', 'wp-claude-mcp'); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Messages Endpoint (JSON-RPC)', 'wp-claude-mcp'); ?>:</th>
                        <td>
                            <code style="font-size: 13px;"><?php echo esc_url(rest_url('mcp/v1/messages')); ?></code>
                            <button class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js(rest_url('mcp/v1/messages')); ?>')">
                                <?php echo esc_html__('Copiar', 'wp-claude-mcp'); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('MCP Endpoint (Claude Desktop)', 'wp-claude-mcp'); ?>:</th>
                        <td>
                            <code style="font-size: 13px;"><?php echo esc_url(rest_url('mcp/v1/mcp')); ?></code>
                            <button class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js(rest_url('mcp/v1/mcp')); ?>')">
                                <?php echo esc_html__('Copiar', 'wp-claude-mcp'); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Config Pública', 'wp-claude-mcp'); ?>:</th>
                        <td>
                            <code style="font-size: 13px;"><?php echo esc_url(rest_url('mcp/v1/config')); ?></code>
                            <button class="button button-small" onclick="window.open('<?php echo esc_js(rest_url('mcp/v1/config')); ?>', '_blank')">
                                <?php echo esc_html__('Abrir', 'wp-claude-mcp'); ?>
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
