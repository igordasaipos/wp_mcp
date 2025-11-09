# Guia Rápido: Conectar WordPress ao Claude Web

## ✅ Passo 1: Instalar o Plugin

1. Baixe este repositório como ZIP
2. No WordPress, vá em **Plugins > Adicionar Novo > Enviar Plugin**
3. Escolha o arquivo ZIP e clique em "Instalar Agora"
4. Ative o plugin "WordPress Claude MCP Integration"

## 🔑 Passo 2: Criar Token de Acesso

1. No WordPress, vá em **Claude MCP** (menu lateral)
2. Preencha o formulário:
   - **Nome do Token**: "Claude Web"
   - **Validade**: 1 ano (ou o período desejado)
3. Clique em **"Gerar Token"**
4. **⚠️ COPIE O TOKEN AGORA!** Você não poderá vê-lo novamente.

## 🌐 Passo 3: Conectar no Claude.ai

1. Acesse [Claude.ai](https://claude.ai)
2. Vá em **Settings** (Configurações)
3. Clique em **Integrations** (Integrações)
4. Selecione **Model Context Protocol (MCP)**
5. Clique em **Add Server** (Adicionar Servidor)

### Configure assim:

- **Nome**: Meu WordPress (ou qualquer nome)
- **Remote MCP Server URL**:
  ```
  https://seu-site.com/wp-json/wp/v2/claude-mcp/remote
  ```
  *(Copie a URL exata da página do plugin no WordPress)*

- **Authentication** (Autenticação):
  - Adicione um header HTTP:
    ```
    Authorization: Bearer SEU_TOKEN_AQUI
    ```
  - Substitua `SEU_TOKEN_AQUI` pelo token que você copiou

### Exemplo Completo:

```
Nome: Meu Blog WordPress
URL: https://meublog.com/wp-json/wp/v2/claude-mcp/remote

Headers:
Authorization: Bearer abc123def456ghi789...
```

## 🎯 Passo 4: Testar

No chat do Claude, experimente:

- "Liste meus 5 posts mais recentes"
- "Crie um rascunho de post sobre WordPress"
- "Mostre as estatísticas do meu site"
- "Quantos comentários pendentes tenho?"

## ❓ Problemas?

### Token não funciona
- Verifique se copiou o token completo
- Confirme que o formato é: `Authorization: Bearer TOKEN`
- Veja se o token não expirou

### URL não conecta
- Certifique-se de que seu site usa HTTPS
- Verifique se a URL está correta (copie do painel do plugin)
- Teste abrindo a URL no navegador (deve mostrar informações JSON)

### Claude não vê as ferramentas
- Atualize a página do Claude
- Verifique se o servidor foi salvo corretamente
- Tente desconectar e reconectar

## 📚 Mais Informações

Consulte o [README.md](README.md) completo para:
- Integração com Claude Desktop
- Lista completa de ferramentas disponíveis
- Configuração OAuth avançada
- Desenvolvimento e extensões
