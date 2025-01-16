<?php
class LM_Newsletter {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'lm_newsletter';
    }

    /**
     * Inscreve um email na newsletter
     * 
     * @param string $email Email para inscrever
     * @return array|WP_Error Sucesso/erro da operação
     */
    public function subscribe($email) {
        // Valida o email
        if (!is_email($email)) {
            return new WP_Error('invalid_email', 'Email inválido.');
        }

        // Verifica se já existe
        $subscriber = $this->get_subscriber_by_email($email);
        
        if ($subscriber) {
            // Se já existe e está inativo, reativa
            if ($subscriber->status === 'inactive') {
                $this->update_status($subscriber->id, 'active');
                LM_Mailer::send_reactivation_email($email);
                return array(
                    'success' => true,
                    'message' => 'Inscrição reativada com sucesso!'
                );
            }
            
            // Se já existe e está ativo, retorna erro
            return new WP_Error('already_subscribed', 'Este email já está inscrito.');
        }

        // Insere novo inscrito
        $result = $this->wpdb->insert(
            $this->table_name,
            array(
                'email' => $email,
                'status' => 'active'
            )
        );

        if (!$result) {
            return new WP_Error('insert_failed', 'Erro ao salvar inscrição.');
        }

        // Envia email de boas-vindas
        LM_Mailer::send_welcome_email($email);

        return array(
            'success' => true,
            'message' => 'Inscrição realizada com sucesso!'
        );
    }

    /**
     * Atualiza o status de um inscrito
     * 
     * @param int $id ID do inscrito
     * @param string $status Novo status
     * @return bool|WP_Error Resultado da operação
     */
    public function update_status($id, $status) {
        $valid_statuses = array('active', 'inactive', 'blocked');
        
        if (!in_array($status, $valid_statuses)) {
            return new WP_Error('invalid_status', 'Status inválido.');
        }

        $result = $this->wpdb->update(
            $this->table_name,
            array('status' => $status),
            array('id' => $id)
        );

        return $result !== false;
    }

    /**
     * Busca inscritos por status
     * 
     * @param string $status Status dos inscritos (all, active, inactive, blocked)
     * @return array Lista de inscritos
     */
    public function get_subscribers($status = 'all') {
        $sql = "SELECT * FROM {$this->table_name}";

        if ($status !== 'all') {
            $sql .= $this->wpdb->prepare(" WHERE status = %s", $status);
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->wpdb->get_results($sql);
    }

    /**
     * Busca um inscrito pelo email
     * 
     * @param string $email Email do inscrito
     * @return object|null Dados do inscrito
     */
    public function get_subscriber_by_email($email) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE email = %s",
                $email
            )
        );
    }

    /**
     * Busca um inscrito pelo ID
     * 
     * @param int $id ID do inscrito
     * @return object|null Dados do inscrito
     */
    public function get_subscriber($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
    }
}
