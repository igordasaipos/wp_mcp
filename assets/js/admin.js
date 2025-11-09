/**
 * Interface de administração React para WordPress Claude MCP
 */

(function() {
    const { useState, useEffect } = wp.element;
    const { __ } = wp.i18n;

    const ClaudeMCPAdmin = () => {
        const [tokens, setTokens] = useState([]);
        const [loading, setLoading] = useState(true);
        const [newToken, setNewToken] = useState(null);
        const [formData, setFormData] = useState({
            token_name: '',
            expires_in: '+1 year',
        });
        const [error, setError] = useState(null);
        const [success, setSuccess] = useState(null);

        // Carrega os tokens ao montar o componente
        useEffect(() => {
            fetchTokens();
        }, []);

        const fetchTokens = async () => {
            setLoading(true);
            setError(null);

            try {
                const response = await fetch(wpClaudeMCP.apiUrl + '/tokens', {
                    headers: {
                        'X-WP-Nonce': wpClaudeMCP.nonce,
                    },
                });

                if (!response.ok) {
                    throw new Error('Erro ao carregar tokens');
                }

                const data = await response.json();
                setTokens(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        const createToken = async (e) => {
            e.preventDefault();
            setError(null);
            setSuccess(null);
            setNewToken(null);

            if (!formData.token_name) {
                setError('Por favor, forneça um nome para o token');
                return;
            }

            try {
                const response = await fetch(wpClaudeMCP.apiUrl + '/tokens', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': wpClaudeMCP.nonce,
                    },
                    body: JSON.stringify(formData),
                });

                if (!response.ok) {
                    throw new Error('Erro ao criar token');
                }

                const data = await response.json();
                setNewToken(data);
                setSuccess('Token criado com sucesso!');
                setFormData({ token_name: '', expires_in: '+1 year' });
                fetchTokens();
            } catch (err) {
                setError(err.message);
            }
        };

        const deleteToken = async (tokenId) => {
            if (!confirm('Tem certeza que deseja revogar este token?')) {
                return;
            }

            setError(null);

            try {
                const response = await fetch(wpClaudeMCP.apiUrl + '/tokens/' + tokenId, {
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': wpClaudeMCP.nonce,
                    },
                });

                if (!response.ok) {
                    throw new Error('Erro ao deletar token');
                }

                setSuccess('Token revogado com sucesso!');
                fetchTokens();
            } catch (err) {
                setError(err.message);
            }
        };

        const copyToClipboard = (text) => {
            navigator.clipboard.writeText(text).then(() => {
                setSuccess('Token copiado para a área de transferência!');
                setTimeout(() => setSuccess(null), 3000);
            });
        };

        const formatDate = (dateString) => {
            if (!dateString) return 'Nunca';
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
        };

        return wp.element.createElement(
            'div',
            { className: 'wp-claude-mcp-admin' },

            // Notificações
            error && wp.element.createElement(
                'div',
                { className: 'wp-claude-mcp-notice error' },
                wp.element.createElement('p', null, error)
            ),

            success && wp.element.createElement(
                'div',
                { className: 'wp-claude-mcp-notice success' },
                wp.element.createElement('p', null, success)
            ),

            // Formulário de criação de token
            wp.element.createElement(
                'div',
                { className: 'wp-claude-mcp-tokens-container' },

                wp.element.createElement(
                    'div',
                    { className: 'wp-claude-mcp-token-form' },
                    wp.element.createElement('h3', null, 'Criar Novo Token de Acesso'),

                    wp.element.createElement(
                        'form',
                        { onSubmit: createToken },

                        wp.element.createElement(
                            'div',
                            { className: 'wp-claude-mcp-form-field' },
                            wp.element.createElement('label', { htmlFor: 'token_name' }, 'Nome do Token:'),
                            wp.element.createElement('input', {
                                type: 'text',
                                id: 'token_name',
                                value: formData.token_name,
                                onChange: (e) => setFormData({ ...formData, token_name: e.target.value }),
                                placeholder: 'Ex: Claude Desktop - Meu Computador',
                            })
                        ),

                        wp.element.createElement(
                            'div',
                            { className: 'wp-claude-mcp-form-field' },
                            wp.element.createElement('label', { htmlFor: 'expires_in' }, 'Validade:'),
                            wp.element.createElement(
                                'select',
                                {
                                    id: 'expires_in',
                                    value: formData.expires_in,
                                    onChange: (e) => setFormData({ ...formData, expires_in: e.target.value }),
                                },
                                wp.element.createElement('option', { value: '+1 month' }, '1 mês'),
                                wp.element.createElement('option', { value: '+3 months' }, '3 meses'),
                                wp.element.createElement('option', { value: '+6 months' }, '6 meses'),
                                wp.element.createElement('option', { value: '+1 year' }, '1 ano'),
                                wp.element.createElement('option', { value: '+2 years' }, '2 anos'),
                            )
                        ),

                        wp.element.createElement(
                            'div',
                            { className: 'wp-claude-mcp-form-actions' },
                            wp.element.createElement(
                                'button',
                                { type: 'submit', className: 'wp-claude-mcp-button' },
                                'Gerar Token'
                            )
                        )
                    )
                ),

                // Exibir novo token criado
                newToken && wp.element.createElement(
                    'div',
                    { className: 'wp-claude-mcp-token-display' },
                    wp.element.createElement('h4', null, '⚠️ Atenção: Copie este token agora!'),
                    wp.element.createElement('p', null, 'Por questões de segurança, você não poderá ver este token novamente.'),

                    // Token puro
                    wp.element.createElement('h5', { style: { marginTop: '20px', marginBottom: '10px' } }, '🔑 Token:'),
                    wp.element.createElement(
                        'div',
                        { className: 'wp-claude-mcp-token-value' },
                        newToken.token
                    ),
                    wp.element.createElement(
                        'button',
                        {
                            type: 'button',
                            className: 'wp-claude-mcp-copy-button',
                            onClick: () => copyToClipboard(newToken.token),
                        },
                        '📋 Copiar Token'
                    ),

                    // URL SSE pronta
                    wp.element.createElement('h5', { style: { marginTop: '20px', marginBottom: '10px', color: '#00a32a' } }, '⚡ URL SSE (Streaming - Recomendada):'),
                    wp.element.createElement('p', { style: { fontSize: '12px', margin: '5px 0' } }, 'Cole esta URL direto no Claude Web - token já incluído!'),
                    wp.element.createElement(
                        'div',
                        {
                            className: 'wp-claude-mcp-token-value',
                            style: { background: '#d5e8d4', borderColor: '#00a32a' }
                        },
                        wpClaudeMCP.siteUrl + '/wp-json/wp/v2/claude-mcp/sse?token=' + newToken.token
                    ),
                    wp.element.createElement(
                        'button',
                        {
                            type: 'button',
                            className: 'wp-claude-mcp-copy-button',
                            style: { background: '#00a32a' },
                            onClick: () => copyToClipboard(wpClaudeMCP.siteUrl + '/wp-json/wp/v2/claude-mcp/sse?token=' + newToken.token),
                        },
                        '📋 Copiar URL SSE Completa'
                    ),

                    // URL Remote MCP
                    wp.element.createElement('h5', { style: { marginTop: '20px', marginBottom: '10px' } }, '🔗 URL Remote MCP (Básica):'),
                    wp.element.createElement('p', { style: { fontSize: '12px', margin: '5px 0' } }, 'Use com Header: Authorization: Bearer TOKEN'),
                    wp.element.createElement(
                        'div',
                        { className: 'wp-claude-mcp-token-value' },
                        wpClaudeMCP.siteUrl + '/wp-json/wp/v2/claude-mcp/remote'
                    ),
                    wp.element.createElement(
                        'button',
                        {
                            type: 'button',
                            className: 'wp-claude-mcp-copy-button',
                            onClick: () => copyToClipboard(wpClaudeMCP.siteUrl + '/wp-json/wp/v2/claude-mcp/remote'),
                        },
                        '📋 Copiar URL Remote MCP'
                    )
                ),

                // Lista de tokens
                wp.element.createElement(
                    'div',
                    { className: 'wp-claude-mcp-tokens-list' },
                    wp.element.createElement('h3', null, 'Tokens de Acesso'),

                    loading ? wp.element.createElement(
                        'div',
                        { style: { textAlign: 'center', padding: '40px' } },
                        wp.element.createElement('div', { className: 'wp-claude-mcp-loading' })
                    ) : tokens.length === 0 ? wp.element.createElement(
                        'div',
                        { className: 'wp-claude-mcp-empty-state' },
                        wp.element.createElement('p', null, 'Nenhum token criado ainda.')
                    ) : tokens.map((token) =>
                        wp.element.createElement(
                            'div',
                            { key: token.id, className: 'wp-claude-mcp-token-item' },

                            wp.element.createElement(
                                'div',
                                { className: 'wp-claude-mcp-token-info' },
                                wp.element.createElement('h4', null,
                                    token.token_name,
                                    ' ',
                                    wp.element.createElement(
                                        'span',
                                        { className: `wp-claude-mcp-badge ${token.is_active === '1' ? 'active' : 'inactive'}` },
                                        token.is_active === '1' ? 'Ativo' : 'Inativo'
                                    )
                                ),
                                wp.element.createElement(
                                    'div',
                                    { className: 'wp-claude-mcp-token-meta' },
                                    wp.element.createElement('span', null, 'Criado: ', formatDate(token.created_at)),
                                    wp.element.createElement('span', null, 'Último uso: ', formatDate(token.last_used)),
                                    wp.element.createElement('span', null, 'Expira: ', formatDate(token.expires_at))
                                )
                            ),

                            wp.element.createElement(
                                'div',
                                { className: 'wp-claude-mcp-token-actions' },
                                token.is_active === '1' && wp.element.createElement(
                                    'button',
                                    {
                                        className: 'wp-claude-mcp-button wp-claude-mcp-button-danger',
                                        onClick: () => deleteToken(token.id),
                                    },
                                    'Revogar'
                                )
                            )
                        )
                    )
                )
            )
        );
    };

    // Renderiza o componente quando o DOM estiver pronto
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('wp-claude-mcp-admin-root');
        if (root) {
            wp.element.render(
                wp.element.createElement(ClaudeMCPAdmin),
                root
            );
        }
    });
})();
