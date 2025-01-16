<?php
class LM_Leads
{
    private $wpdb;
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'lm_leads';
    }

    /**
     * Salva um novo lead
     * 
     * @param array $data Dados do lead
     * @return bool|WP_Error Resultado da operação
     */
    public function save_lead($data)
    {
        $required_fields = array('nome_completo', 'email', 'telefone');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', 'Campo obrigatório: ' . $field);
            }
        }

        $lead_data = array(
            'nome_completo' => sanitize_text_field($data['nome_completo']),
            'nome_preferido' => !empty($data['nome_preferido']) ? sanitize_text_field($data['nome_preferido']) : '',
            'email' => sanitize_email($data['email']),
            'telefone' => sanitize_text_field($data['telefone']),
            'whatsapp' => !empty($data['whatsapp']) ? 1 : 0,
            'aceita_whatsapp' => !empty($data['aceita_whatsapp']) ? 1 : 0,
            'departamento' => !empty($data['departamento']) ? sanitize_text_field($data['departamento']) : '',
            'tipo_contato' => !empty($data['tipo_contato']) ? sanitize_text_field($data['tipo_contato']) : '',
            'mensagem' => !empty($data['mensagem']) ? sanitize_textarea_field($data['mensagem']) : '',
            'origem' => !empty($data['origem']) ? sanitize_text_field($data['origem']) : 'form',
            'status' => 'new',
            'data_criacao' => current_time('mysql'),
            'data_atualizacao' => current_time('mysql')
        );

        $result = $this->wpdb->insert($this->table_name, $lead_data);

        if (!$result) {
            return new WP_Error('insert_failed', 'Erro ao salvar lead');
        }

        // Notifica sobre o novo lead
        $this->notify_new_lead($lead_data);

        return true;
    }

    /**
     * Notifica sobre um novo lead
     * 
     * @param array $data Dados do lead
     */
    public function notify_new_lead($data)
    {
        $mailer = new LM_Mailer();
        $subject = 'Novo Lead Recebido';
        $message = "<p>Um novo lead foi recebido:</p>";
        $message .= "<p>Nome Completo: {$data['nome_completo']}<br>";

        if (!empty($data['nome_preferido'])) {
            $message .= "Nome Preferido: {$data['nome_preferido']}<br>";
        }

        $message .= "Email: {$data['email']}<br>";
        $message .= "Telefone: {$data['telefone']}<br>";

        if (!empty($data['whatsapp'])) {
            $message .= "Tem WhatsApp: Sim<br>";
            if (!empty($data['aceita_whatsapp'])) {
                $message .= "Aceita contato por WhatsApp: Sim<br>";
            }
        }

        if (!empty($data['departamento'])) {
            $message .= "Departamento: {$data['departamento']}<br>";
        }

        if (!empty($data['tipo_contato'])) {
            $message .= "Tipo de Contato: {$data['tipo_contato']}<br>";
        }

        if (!empty($data['mensagem'])) {
            $message .= "Mensagem:<br>{$data['mensagem']}<br>";
        }

        $message .= "Origem: {$data['origem']}<br>";
        $message .= "Data: " . current_time('mysql') . "</p>";

        $to = get_bloginfo('admin_email');
        $mailer->send_lead_notification($to, $subject, $message);

        $mailer->send_contact_form_notification($data['email']);
    }

    /**
     * Busca leads com filtros
     * 
     * @param array $args Argumentos da busca
     * @return array Lista de leads
     */
    public function get_leads($args = array())
    {
        $defaults = array(
            'status' => 'all',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'data_criacao',
            'order' => 'DESC',
            'search' => ''
        );

        $args = wp_parse_args($args, $defaults);

        // Debug
        error_log('Buscando leads com args: ' . print_r($args, true));

        $query = "SELECT * FROM {$this->table_name}";
        $where = array();

        // Filtro por status
        if ($args['status'] !== 'all') {
            $where[] = $this->wpdb->prepare("status = %s", $args['status']);
        }

        // Busca
        if (!empty($args['search'])) {
            $search = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $where[] = $this->wpdb->prepare(
                "(nome_completo LIKE %s OR nome_preferido LIKE %s OR email LIKE %s OR telefone LIKE %s)",
                $search,
                $search,
                $search,
                $search
            );
        }

        // Monta WHERE
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }

        // Ordenação e limite
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        $query .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);

        // Debug
        error_log('Query final: ' . $query);

        $results = $this->wpdb->get_results($query);

        // Debug
        error_log('Resultados encontrados: ' . count($results));
        error_log('Primeiro resultado: ' . print_r(!empty($results) ? $results[0] : 'nenhum', true));

        return $results;
    }

    /**
     * Conta total de leads
     * 
     * @param string $status Status dos leads
     * @param string $search Termo de busca
     * @return int Total de leads
     */
    public function count_leads($status = 'all', $search = '')
    {
        $query = "SELECT COUNT(*) FROM {$this->table_name}";
        $where = array();

        if ($status !== 'all') {
            $where[] = $this->wpdb->prepare("status = %s", $status);
        }

        if (!empty($search)) {
            $search = '%' . $this->wpdb->esc_like($search) . '%';
            $where[] = $this->wpdb->prepare(
                "(nome_completo LIKE %s OR nome_preferido LIKE %s OR email LIKE %s OR telefone LIKE %s)",
                $search,
                $search,
                $search,
                $search
            );
        }

        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }

        // Debug
        error_log('Query count: ' . $query);

        $count = (int) $this->wpdb->get_var($query);

        // Debug
        error_log('Total encontrado: ' . $count);

        return $count;
    }

    /**
     * Atualiza o status de um lead
     * 
     * @param int $id ID do lead
     * @param string $status Novo status
     * @return bool|WP_Error Resultado da operação
     */
    public function update_status($id, $status)
    {
        if (!in_array($status, array('new', 'contacted', 'converted', 'archived'))) {
            return new WP_Error('invalid_status', 'Status inválido');
        }

        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'status' => $status,
                'data_atualizacao' => current_time('mysql')
            ),
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Erro ao atualizar status');
        }

        return true;
    }

    /**
     * Busca um lead pelo ID
     * 
     * @param int $id ID do lead
     * @return object|null Dados do lead
     */
    public function get_lead($id)
    {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
    }

    /**
     * Remove um lead
     * 
     * @param int $id ID do lead
     * @return bool|WP_Error Resultado da operação
     */
    public function delete_lead($id)
    {
        $result = $this->wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('delete_failed', 'Erro ao remover lead');
        }

        return true;
    }
}
