<?php
/**
 * Plugin Name: WC Category Hierarchy Fixer
 * Description: Força automaticamente a associação de todas as categorias pai (ancestrais) quando um produto está associado apenas à categoria filha. Inclui ação em massa.
 * Author: Dsantos Info
 * Author URI: https://dsantosinfo.com.br/
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.0
 * Text Domain: wc-hierarchy-fixer
 */

namespace WcHierarchyFixer;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class CategoryFixer {

    const TAXONOMY = 'product_cat';

    public function __construct() {
        // 1. Hook para automação ao salvar/atualizar produto individual
        add_action( 'save_post_product', [ $this, 'auto_fix_on_save' ], 20, 3 );

        // 2. Hooks para Ação em Massa (Bulk Action) na lista de produtos
        add_filter( 'bulk_actions-edit-product', [ $this, 'register_bulk_action' ] );
        add_filter( 'handle_bulk_actions-edit-product', [ $this, 'handle_bulk_action' ], 10, 3 );
        add_action( 'admin_notices', [ $this, 'bulk_action_admin_notice' ] );
    }

    /**
     * Lógica principal: Processa um único produto ID para corrigir hierarquia
     * * @param int $product_id
     * @return bool True se houve alteração, False caso contrário
     */
    private function process_product( $product_id ) {
        // Obtém os IDs das categorias atuais do produto
        $current_term_ids = wp_get_post_terms( $product_id, self::TAXONOMY, [ 'fields' => 'ids' ] );

        if ( empty( $current_term_ids ) || is_wp_error( $current_term_ids ) ) {
            return false;
        }

        $all_term_ids = $current_term_ids;

        // Para cada categoria associada, busca seus ancestrais
        foreach ( $current_term_ids as $term_id ) {
            $ancestors = get_ancestors( $term_id, self::TAXONOMY );
            
            if ( ! empty( $ancestors ) ) {
                // Mescla os ancestrais encontrados
                $all_term_ids = array_merge( $all_term_ids, $ancestors );
            }
        }

        // Remove duplicatas e garante inteiros
        $all_term_ids = array_map( 'intval', array_unique( $all_term_ids ) );

        // Verifica se houve mudança para evitar update desnecessário
        // Ordena arrays para comparação precisa
        sort( $current_term_ids );
        sort( $all_term_ids );

        if ( $current_term_ids !== $all_term_ids ) {
            // Atualiza os termos do produto mantendo os existentes + novos pais
            wp_set_object_terms( $product_id, $all_term_ids, self::TAXONOMY );
            return true;
        }

        return false;
    }

    /**
     * Hook: Executa ao salvar um produto individualmente
     */
    public function auto_fix_on_save( $post_id, $post, $update ) {
        // Evita autosave e revisões
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Verifica permissões
        if ( ! current_user_can( 'edit_product', $post_id ) ) {
            return;
        }

        // Remove o hook temporariamente para evitar loop infinito (embora set_object_terms não deva disparar save_post para o post)
        remove_action( 'save_post_product', [ $this, 'auto_fix_on_save' ], 20 );

        $this->process_product( $post_id );

        // Re-adiciona o hook
        add_action( 'save_post_product', [ $this, 'auto_fix_on_save' ], 20, 3 );
    }

    /**
     * Adiciona opção no dropdown de ações em massa
     */
    public function register_bulk_action( $bulk_actions ) {
        $bulk_actions['fix_cat_hierarchy'] = __( 'Corrigir Hierarquia de Categorias', 'wc-hierarchy-fixer' );
        return $bulk_actions;
    }

    /**
     * Processa a ação em massa
     */
    public function handle_bulk_action( $redirect_to, $doaction, $post_ids ) {
        if ( $doaction !== 'fix_cat_hierarchy' ) {
            return $redirect_to;
        }

        $changed_count = 0;

        foreach ( $post_ids as $post_id ) {
            if ( $this->process_product( $post_id ) ) {
                $changed_count++;
            }
        }

        // Adiciona query args para exibir a mensagem de sucesso
        return add_query_arg( [
            'hierarchy_fixed_count' => $changed_count,
            'hierarchy_fixed_done' => 1
        ], $redirect_to );
    }

    /**
     * Exibe notificação no admin após ação em massa
     */
    public function bulk_action_admin_notice() {
        if ( empty( $_GET['hierarchy_fixed_done'] ) ) {
            return;
        }

        $count = isset( $_GET['hierarchy_fixed_count'] ) ? intval( $_GET['hierarchy_fixed_count'] ) : 0;
        
        $message = sprintf( 
            __( 'Hierarquia corrigida com sucesso em %d produtos.', 'wc-hierarchy-fixer' ), 
            $count 
        );
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
    }
}

// Inicializa o plugin
new CategoryFixer();