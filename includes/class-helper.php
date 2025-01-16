<?php
class LM_Helper {
    public static $status_translations = array(
        // Inglês => Português
        'active' => 'Ativo',
        'inactive' => 'Inativo',
        'blocked' => 'Bloqueado',
        'all' => 'Todos'
    );

    public static function get_status_pt($status) {
        return isset(self::$status_translations[$status]) 
            ? self::$status_translations[$status] 
            : $status;
    }

    public static function get_status_en($status_pt) {
        $status_en = array_search($status_pt, self::$status_translations);
        return $status_en !== false ? $status_en : 'active';
    }

    public static function get_status_options() {
        return array(
            'all' => 'Todos',
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'blocked' => 'Bloqueado'
        );
    }
}
