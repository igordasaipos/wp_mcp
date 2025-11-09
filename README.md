# WordPress Claude MCP Integration

Plugin WordPress que integra seu site com Claude AI usando o Model Context Protocol (MCP), permitindo que o Claude interaja diretamente com seu site WordPress.

## 📋 Descrição

Este plugin implementa o Model Context Protocol (MCP) para WordPress, permitindo que assistentes de IA como o Claude Desktop:

- 🔍 Busquem e leiam posts e páginas
- ✍️ Criem e editem conteúdo
- 📊 Acessem estatísticas do site
- 👥 Gerenciem usuários e comentários
- 🎨 Busquem e organizem mídia
- ⚙️ Consultem configurações do site

## 🚀 Recursos

### Ferramentas MCP Disponíveis

- **wordpress_search_posts** - Busca posts por título, conteúdo ou autor
- **wordpress_create_post** - Cria novos posts ou páginas
- **wordpress_update_post** - Atualiza posts existentes
- **wordpress_get_post** - Obtém detalhes de um post específico
- **wordpress_list_categories** - Lista todas as categorias
- **wordpress_search_media** - Busca arquivos de mídia
- **wordpress_list_users** - Lista usuários do site
- **wordpress_get_comments** - Obtém comentários
- **wordpress_get_statistics** - Estatísticas gerais do site

### Recursos

- **wordpress://site/info** - Informações gerais do site
- **wordpress://site/settings** - Configurações do site

### Prompts

- **create_post** - Auxiliar na criação de posts
- **analyze_site** - Análise e insights sobre o site

## 📦 Requisitos

- WordPress 6.0 ou superior
- PHP 7.4 ou superior
- Claude Desktop ou outro cliente MCP compatível

## 🔧 Instalação

1. **Baixe o plugin:**
   ```bash
   git clone https://github.com/igordasaipos/wp_mcp.git
   ```

2. **Faça upload para o WordPress:**
   - Copie a pasta para `wp-content/plugins/wp-claude-mcp/`
   - Ou faça upload do arquivo ZIP através do painel do WordPress

3. **Ative o plugin:**
   - Acesse o painel do WordPress
   - Vá em Plugins > Plugins Instalados
   - Ative "WordPress Claude MCP Integration"

## 🔑 Configuração

### 1. Criar um Token de Acesso

1. No painel do WordPress, vá em **Claude MCP**
2. Preencha o formulário "Criar Novo Token de Acesso":
   - **Nome do Token**: Um nome descritivo (ex: "Claude Desktop - Meu Computador")
   - **Validade**: Escolha por quanto tempo o token será válido
3. Clique em **"Gerar Token"**
4. **IMPORTANTE**: Copie o token imediatamente! Por segurança, você não poderá vê-lo novamente.

### 2. Configurar o Claude Desktop

1. Localize o arquivo de configuração do Claude Desktop:
   - **macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
   - **Windows**: `%APPDATA%\Claude\claude_desktop_config.json`
   - **Linux**: `~/.config/Claude/claude_desktop_config.json`

2. Adicione a seguinte configuração:

```json
{
  "mcpServers": {
    "wordpress": {
      "url": "https://seu-site.com/wp-json/wp/v2/claude-mcp/mcp",
      "headers": {
        "Authorization": "Bearer SEU_TOKEN_AQUI"
      }
    }
  }
}
```

3. Substitua:
   - `https://seu-site.com` pelo URL do seu site WordPress
   - `SEU_TOKEN_AQUI` pelo token que você copiou

4. Reinicie o Claude Desktop

### 3. Testar a Conexão

Abra o Claude Desktop e experimente comandos como:

- "Liste meus 5 posts mais recentes"
- "Crie um rascunho de post sobre inteligência artificial"
- "Quais são as estatísticas do meu site?"
- "Mostre os comentários pendentes de aprovação"

## 💡 Exemplos de Uso

### Buscar Posts

```
Claude, busque posts que mencionam "WordPress" no meu site
```

### Criar um Post

```
Claude, crie um novo post sobre os benefícios do WordPress.
Título: "Por que escolher WordPress em 2024"
Faça um rascunho com 3 seções principais.
```

### Obter Estatísticas

```
Claude, me mostre um resumo das estatísticas do meu site WordPress
```

### Gerenciar Comentários

```
Claude, me mostre os comentários mais recentes aguardando moderação
```

## 🔒 Segurança

### Autenticação JWT

- Todos os tokens são armazenados com hash SHA-256
- Tokens podem ser revogados a qualquer momento
- Tokens têm data de expiração configurável
- Cada requisição é autenticada via Bearer token

### Permissões WordPress

- O plugin respeita as permissões nativas do WordPress
- Ações são executadas com as capacidades do usuário que criou o token
- Tokens de administradores têm acesso total
- Tokens de outros papéis têm acesso limitado

### Boas Práticas

1. **Crie tokens específicos** para cada dispositivo/aplicação
2. **Use nomes descritivos** para identificar facilmente os tokens
3. **Revogue tokens** não utilizados
4. **Configure validades curtas** para tokens de teste
5. **Nunca compartilhe** seus tokens

## 🛠️ Desenvolvimento

### Estrutura do Plugin

```
wp-claude-mcp/
├── wp-claude-mcp.php          # Arquivo principal do plugin
├── includes/
│   ├── class-jwt-auth.php     # Autenticação JWT
│   ├── class-mcp-server.php   # Servidor MCP
│   ├── class-mcp-tools.php    # Ferramentas MCP
│   ├── class-rest-api.php     # Endpoints REST API
│   └── class-admin-ui.php     # Interface de administração
├── assets/
│   ├── css/
│   │   └── admin.css          # Estilos do admin
│   └── js/
│       └── admin.js           # JavaScript do admin
└── README.md
```

### Extendendo o Plugin

#### Adicionar uma Nova Ferramenta

Edite `includes/class-mcp-tools.php`:

```php
// 1. Adicione a definição da ferramenta em get_available_tools()
array(
    'name' => 'wordpress_minha_ferramenta',
    'description' => 'Descrição da ferramenta',
    'inputSchema' => array(
        'type' => 'object',
        'properties' => array(
            'parametro' => array(
                'type' => 'string',
                'description' => 'Descrição do parâmetro',
            ),
        ),
        'required' => array('parametro'),
    ),
),

// 2. Implemente o método da ferramenta
private static function tool_minha_ferramenta($args) {
    // Sua lógica aqui
    return array(
        'content' => array(
            array(
                'type' => 'text',
                'text' => json_encode($resultado, JSON_PRETTY_PRINT),
            ),
        ),
    );
}
```

## 🐛 Troubleshooting

### Token não funciona

- Verifique se o token foi copiado corretamente
- Confirme que o token não expirou
- Verifique se o token está ativo na lista de tokens

### Erro 401 (Não Autorizado)

- Confirme que o header Authorization está correto
- Verifique se o formato é `Bearer {token}`
- Revogue e crie um novo token

### Erro 404 (Não Encontrado)

- Verifique se a URL do endpoint está correta
- Confirme que o plugin está ativado
- Tente salvar novamente os permalinks (Configurações > Links Permanentes)

### Claude não reconhece as ferramentas

- Reinicie o Claude Desktop
- Verifique o arquivo de configuração JSON
- Confirme que não há erros de sintaxe no JSON

## 📝 Changelog

### Versão 1.0.0 (2024-11-09)

- Lançamento inicial
- Implementação do protocolo MCP 2024-11-05
- 10 ferramentas principais
- Sistema de autenticação JWT
- Interface de gerenciamento de tokens
- Suporte completo para posts, páginas, mídia, usuários e comentários

## 📄 Licença

Este plugin é licenciado sob a GPL v2 ou posterior.

## 👤 Autor

**Igor da Saipos**
- GitHub: [@igordasaipos](https://github.com/igordasaipos)

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## 📚 Recursos Adicionais

- [Documentação do MCP](https://modelcontextprotocol.io/)
- [WordPress MCP da Automattic](https://github.com/Automattic/wordpress-mcp)
- [Claude Desktop](https://claude.ai/desktop)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)

## 💬 Suporte

Para reportar bugs ou solicitar recursos:
- Abra uma [Issue no GitHub](https://github.com/igordasaipos/wp_mcp/issues)

---

**Desenvolvido com ❤️ para a comunidade WordPress**
