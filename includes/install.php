<?php

function cbt_install() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    $table_soal = $wpdb->prefix . 'cbt_soal';
    $table_hasil = $wpdb->prefix . 'cbt_hasil';

    // Tabel soal
    $sql_soal = "CREATE TABLE $table_soal (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kategori VARCHAR(100),
        pertanyaan TEXT,
        opsi_a TEXT,
        opsi_b TEXT,
        opsi_c TEXT,
        opsi_d TEXT,
        jawaban CHAR(1)
    ) $charset;";

    // Tabel hasil ujian
    $sql_hasil = "CREATE TABLE $table_hasil (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        skor INT,
        waktu_submit DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_soal);
    dbDelta($sql_hasil);
}

// Membuat halaman otomatis saat plugin diaktifkan
function cbt_create_pages() {
    $pages = [
        'daftar' => '[cbt_register]',
        'login'  => '[cbt_login]',
        'ujian'  => '[cbt_test]',
        'logout' => '[cbt_logout]'
    ];

    foreach ($pages as $slug => $shortcode) {
        $page = get_page_by_path($slug);
        if (!$page) {
            wp_insert_post([
                'post_title'   => ucfirst($slug),
                'post_name'    => $slug,
                'post_content' => $shortcode,
                'post_status'  => 'publish',
                'post_type'    => 'page'
            ]);
        }
    }
}
