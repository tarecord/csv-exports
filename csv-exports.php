<?php declare(strict_types=1);
/*
 * Plugin Name:       CSV Exports
 * Description:       Export data from the database as a CSV.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Tanner Record
 * Author URI:        https://www.tannerrecord.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       csv-exports
 * Domain Path:       /languages
 */

add_action( 'admin_menu', 'csv_export_init_menu');

function csv_export_init_menu() {
    add_menu_page(
        'CSV Export',
        'CSV Export',
        'manage_options',
        'csv_export',
        'csv_export_render'
    );
}

function csv_export_render() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="<?php echo admin_url( 'admin.php?page=csv_export' ); ?>" method="post">
            <?php wp_nonce_field( 'generate-csv' ); ?>
            <?php submit_button( 'Generate CSV' ); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init', 'handle_csv_export' );

function handle_csv_export() {
    if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'generate-csv') ) {
        return;
    }

    $query = new WP_Query( [
        'post_type' => 'post',
    ] );

    $query->get_posts();

    $file = fopen( wp_get_upload_dir()['path'] . '/' . gmdate( 'now' ) . '.csv', 'w' );

    fputcsv( $file, [
        'id',
        'post_author',
        'post_date',
        'post_content',
        'post_title',
        'post_status',
    ] );

    /**
     * @var \WP_Post $post
     */
    foreach ( $query->posts as $post) {
        fputcsv( $file, [
            $post->ID,
            $post->post_author,
            $post->post_date,
            $post->post_content,
            $post->post_title,
            $post->post_status,
        ] );
    }

    fclose( $file );
}
