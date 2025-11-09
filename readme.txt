=== WordPress Claude MCP Integration ===
Contributors: igordasaipos
Tags: claude, ai, mcp, model-context-protocol, automation
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integre seu site WordPress com Claude AI usando o Model Context Protocol (MCP).

== Description ==

WordPress Claude MCP Integration permite que o Claude AI interaja diretamente com seu site WordPress através do Model Context Protocol (MCP).

= Principais Recursos =

* 🔍 Busca inteligente de posts e páginas
* ✍️ Criação e edição de conteúdo
* 📊 Acesso a estatísticas do site
* 👥 Gerenciamento de usuários e comentários
* 🎨 Busca e organização de mídia
* ⚙️ Consulta de configurações do site
* 🔒 Autenticação segura via JWT
* 🎛️ Interface de gerenciamento de tokens

= Ferramentas Disponíveis =

* wordpress_search_posts - Busca posts por título, conteúdo ou autor
* wordpress_create_post - Cria novos posts ou páginas
* wordpress_update_post - Atualiza posts existentes
* wordpress_get_post - Obtém detalhes de um post
* wordpress_list_categories - Lista categorias
* wordpress_search_media - Busca arquivos de mídia
* wordpress_list_users - Lista usuários
* wordpress_get_comments - Obtém comentários
* wordpress_get_statistics - Estatísticas do site

= Como Funciona =

1. Instale e ative o plugin
2. Crie um token de acesso no painel do WordPress
3. Configure o Claude Desktop com o token
4. Comece a interagir com seu site através do Claude

= Segurança =

* Autenticação JWT com tokens hash SHA-256
* Tokens podem ser revogados a qualquer momento
* Respeita permissões nativas do WordPress
* Tokens com data de expiração configurável

== Installation ==

= Instalação Automática =

1. Acesse o painel do WordPress
2. Vá em Plugins > Adicionar Novo
3. Busque por "WordPress Claude MCP Integration"
4. Clique em "Instalar Agora"
5. Ative o plugin

= Instalação Manual =

1. Baixe o arquivo ZIP do plugin
2. Acesse Plugins > Adicionar Novo > Enviar Plugin
3. Escolha o arquivo ZIP e clique em "Instalar Agora"
4. Ative o plugin

= Configuração =

1. Vá em "Claude MCP" no menu do WordPress
2. Crie um novo token de acesso
3. Copie o token gerado
4. Configure o Claude Desktop com o token

Consulte o arquivo README.md para instruções detalhadas.

== Frequently Asked Questions ==

= O que é MCP? =

MCP (Model Context Protocol) é um protocolo que permite que assistentes de IA interajam com diferentes sistemas de forma padronizada.

= Preciso do Claude Desktop? =

Você pode usar qualquer cliente compatível com MCP, mas o Claude Desktop é a opção mais popular e testada.

= É seguro? =

Sim! O plugin usa autenticação JWT com tokens hash, respeita as permissões do WordPress e permite revogar tokens a qualquer momento.

= Posso criar múltiplos tokens? =

Sim! Você pode criar quantos tokens precisar, um para cada dispositivo ou aplicação.

= Como revogo um token? =

Acesse "Claude MCP" no painel do WordPress e clique em "Revogar" ao lado do token desejado.

= O plugin funciona com outros assistentes de IA? =

Sim! Qualquer cliente compatível com MCP pode usar este plugin.

= Posso adicionar minhas próprias ferramentas? =

Sim! O plugin é extensível. Consulte a documentação de desenvolvimento no README.md.

== Screenshots ==

1. Interface de gerenciamento de tokens
2. Criação de novo token de acesso
3. Lista de tokens ativos
4. Exemplo de interação com Claude Desktop

== Changelog ==

= 1.0.0 (2024-11-09) =
* Lançamento inicial
* Implementação do protocolo MCP 2024-11-05
* 10 ferramentas MCP principais
* Sistema de autenticação JWT
* Interface React para gerenciamento de tokens
* Suporte para posts, páginas, mídia, usuários e comentários

== Upgrade Notice ==

= 1.0.0 =
Primeira versão do plugin. Instale e configure seus tokens de acesso.

== Technical Details ==

= REST API Endpoints =

* POST /wp/v2/claude-mcp/mcp - Endpoint principal do MCP
* GET /wp/v2/claude-mcp/tokens - Lista tokens
* POST /wp/v2/claude-mcp/tokens - Cria token
* DELETE /wp/v2/claude-mcp/tokens/{id} - Revoga token

= MCP Protocol Version =

Este plugin implementa a versão 2024-11-05 do Model Context Protocol.

= Database Tables =

O plugin cria a tabela `{prefix}_claude_mcp_tokens` para armazenar tokens de forma segura.

== Privacy Policy ==

Este plugin:
* NÃO coleta dados pessoais dos visitantes
* NÃO envia dados para servidores externos
* Armazena apenas tokens de autenticação no banco de dados
* Tokens são hash SHA-256 e não podem ser descriptografados

Ao usar o Claude AI, consulte a política de privacidade da Anthropic.

== Support ==

Para suporte, documentação e contribuições:
* GitHub: https://github.com/igordasaipos/wp_mcp
* Issues: https://github.com/igordasaipos/wp_mcp/issues

== Credits ==

Desenvolvido por Igor da Saipos
Baseado no trabalho da Automattic com wordpress-mcp
