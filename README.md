# Leads Manager

Plugin WordPress para gerenciamento de leads e newsletter.

## Funcionalidades

### Gerenciamento de Leads

- Captura de leads através de formulário de contato
- Painel administrativo para visualização e gerenciamento dos leads
- Status de leads (novo, contatado, convertido, arquivado)
- Notificações por email para novos leads
- Campos personalizados para informações do lead

### Newsletter

- Formulário de inscrição na newsletter
- Gerenciamento de inscritos no painel administrativo
- Status de inscritos (ativo, inativo, bloqueado)
- Emails automáticos de:
  - Boas-vindas para novos inscritos
  - Confirmação de reativação
- Integração com sistema de leads

## Shortcodes

### Formulário de Newsletter
```
[lm_newsletter_form]
```

### Formulário de Contato
```
[lm_contact_form]
```

## Configurações

### Email

Configure as opções de email em Leads Manager > Configurações:
- Servidor SMTP
- Porta
- Usuário
- Senha
- Segurança (SSL/TLS)
- Email do remetente
- Nome do remetente

## Banco de Dados

### Tabela de Leads (lm_leads)
- id
- nome_completo
- nome_preferido
- email
- telefone
- whatsapp
- aceita_whatsapp
- departamento
- tipo_contato
- mensagem
- origem
- status
- created_at
- updated_at

### Tabela de Newsletter (lm_newsletter)
- id
- email
- status (ativo, inativo, bloqueado)
- created_at
- updated_at

## Desenvolvimento

### Estrutura de Arquivos
```
leads-manager/
├── admin/
│   ├── leads-page.php
│   ├── newsletter-page.php
│   └── settings-page.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   ├── forms.css
│   │   └── toast.css
│   └── js/
│       ├── admin.js
│       ├── forms.js
│       └── toast.js
├── includes/
│   ├── class-ajax.php
│   ├── class-database.php
│   ├── class-email-settings.php
│   ├── class-forms.php
│   ├── class-leads.php
│   ├── class-mailer.php
│   └── class-newsletter.php
├── leads-manager.php
└── README.md
```

### Dependências
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

### Instalação para Desenvolvimento

1. Clone o repositório na pasta `wp-content/plugins/`
2. Ative o plugin no painel WordPress
3. Configure as opções de email em Leads Manager > Configurações

## Notas de Versão

### 1.0.0
- Lançamento inicial
- Sistema de gerenciamento de leads
- Sistema de newsletter
- Notificações por email
- Painel administrativo
