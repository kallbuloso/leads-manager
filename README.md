# Leads Manager

Sistema de gerenciamento de leads e newsletter para WordPress. Permite capturar, gerenciar e acompanhar leads, além de enviar newsletters para os inscritos.

## Funcionalidades

- Captura de leads através de formulários
- Gestão de leads com diferentes status
- Sistema de newsletter integrado
- Painel administrativo com estatísticas
- Notificações por email
- Suporte a SMTP

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- MySQL 5.6 ou superior

## Instalação

1. Faça o download do plugin
2. Descompacte o arquivo na pasta `/wp-content/plugins/`
3. Ative o plugin através do menu 'Plugins' no WordPress
4. Configure as opções de SMTP em 'Leads Manager > Configurações'

## Estrutura de Arquivos

```
leads-manager/
├── admin/
│   ├── class-admin.php
│   ├── dashboard-page.php
│   ├── email-settings-page.php
│   ├── leads-page.php
│   └── newsletter-page.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── leads.css
│   └── js/
│       ├── admin.js
│       ├── forms.js
│       ├── leads.js
│       └── toast.js
├── includes/
│   ├── class-ajax.php
│   ├── class-database.php
│   ├── class-email-settings.php
│   ├── class-forms.php
│   ├── class-leads.php
│   └── class-newsletter.php
├── languages/
│   └── leads-manager.pot
├── templates/
│   ├── contact-form.php
│   └── newsletter-form.php
├── LICENSE
├── README.md
├── leads-manager.php
└── uninstall.php
```

## Uso

### Shortcodes

- `[lm_contact_form]` - Exibe o formulário de contato
- `[lm_newsletter_form]` - Exibe o formulário de newsletter

### Hooks

#### Filtros

- `lm_form_fields` - Modifica os campos do formulário
- `lm_email_content` - Modifica o conteúdo do email
- `lm_newsletter_template` - Modifica o template da newsletter

#### Ações

- `lm_after_lead_save` - Executado após salvar um lead
- `lm_after_newsletter_send` - Executado após enviar uma newsletter
- `lm_after_status_change` - Executado após mudar o status de um lead

## Desenvolvimento

### Configuração do Ambiente

1. Clone o repositório
2. Execute `composer install` para instalar as dependências
3. Configure o ambiente WordPress local

### Comandos

- `composer test` - Executa os testes
- `composer phpcs` - Verifica o código com PHPCS
- `composer build` - Gera uma versão de distribuição

## Contribuição

1. Faça um Fork do projeto
2. Crie uma Branch para sua Feature (`git checkout -b feature/AmazingFeature`)
3. Faça commit das suas alterações (`git commit -m 'Add some AmazingFeature'`)
4. Faça Push para a Branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## Autor

- **Amaral Karl** - [GitHub](https://github.com/kallbuloso)

## Changelog

### 1.0.0 (2025-01-16)

- Lançamento inicial
- Sistema de gerenciamento de leads
- Sistema de newsletter
- Painel administrativo
- Suporte a SMTP
