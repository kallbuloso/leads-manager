<?php
class LM_Database {
    private $leads_table;
    private $newsletter_table;
    private $charset_collate;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->leads_table = $wpdb->prefix . 'lm_leads';
        $this->newsletter_table = $wpdb->prefix . 'lm_newsletter';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    public function create_tables() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Tabela de leads
        $sql = "CREATE TABLE IF NOT EXISTS {$this->leads_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nome_completo varchar(100) NOT NULL,
            nome_preferido varchar(50),
            email varchar(100) NOT NULL,
            telefone varchar(20) NOT NULL,
            whatsapp tinyint(1) DEFAULT 0,
            aceita_whatsapp tinyint(1) DEFAULT 0,
            departamento varchar(50),
            tipo_contato varchar(50),
            mensagem text,
            origem varchar(50) DEFAULT 'site',
            status varchar(20) DEFAULT 'new',
            data_criacao datetime DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$this->charset_collate};";

        dbDelta($sql);

        // Tabela de Newsletter
        $sql = "CREATE TABLE IF NOT EXISTS {$this->newsletter_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) {$this->charset_collate};";

        dbDelta($sql);
    }

    public function drop_tables() {
        $sql_newsletter = "DROP TABLE IF EXISTS {$this->newsletter_table};";
        $sql_leads = "DROP TABLE IF EXISTS {$this->leads_table};";
        $this->wpdb->query($sql_newsletter);
        $this->wpdb->query($sql_leads);
    }

    public function count_subscribers() {
        return $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->newsletter_table}");
    }
}
