<?php
add_action('admin_menu', 'cbt_admin_menu');
function cbt_admin_menu() {
    add_menu_page('CBT Admin','CBT Admin','manage_options','cbt_admin','cbt_admin_dashboard','dashicons-welcome-learn-more',6);
}

function cbt_admin_dashboard() {
    global $wpdb;
    $table_soal = $wpdb->prefix . 'cbt_soal';
    $table_hasil = $wpdb->prefix . 'cbt_hasil';

    // Proses simpan / update soal
    if (isset($_POST['submit_soal'])) {
        $data = [
            'kategori'   => sanitize_text_field($_POST['kategori']),
            'pertanyaan' => sanitize_text_field($_POST['pertanyaan']),
            'opsi_a'     => sanitize_text_field($_POST['opsi_a']),
            'opsi_b'     => sanitize_text_field($_POST['opsi_b']),
            'opsi_c'     => sanitize_text_field($_POST['opsi_c']),
            'opsi_d'     => sanitize_text_field($_POST['opsi_d']),
            'jawaban'    => strtoupper(sanitize_text_field($_POST['jawaban'])),
        ];
        if (!empty($_POST['soal_id'])) {
            $wpdb->update($table_soal, $data, ['id' => intval($_POST['soal_id'])]);
            echo '<div class="notice notice-success">✏️ Soal berhasil diperbarui.</div>';
        } else {
            $wpdb->insert($table_soal, $data);
            echo '<div class="notice notice-success">✅ Soal berhasil ditambahkan.</div>';
        }
    }

    // Hapus soal
    if (isset($_GET['hapus_soal'])) {
        $wpdb->delete($table_soal, ['id' => intval($_GET['hapus_soal'])]);
        echo '<div class="notice notice-success">🗑️ Soal berhasil dihapus.</div>';
    }

    // Filter kategori
    $kategori_filter = $_GET['filter_kategori'] ?? '';
    $where = $kategori_filter ? $wpdb->prepare("WHERE kategori = %s", $kategori_filter) : '';

    // Edit soal
    $edit_soal = null;
    if (isset($_GET['edit_soal'])) {
        $edit_soal = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_soal WHERE id = %d", intval($_GET['edit_soal'])));
    }

    // Ambil soal dan kategori unik
    $soal_list = $wpdb->get_results("SELECT * FROM $table_soal $where ORDER BY id DESC");
    $kategori_list = $wpdb->get_col("SELECT DISTINCT kategori FROM $table_soal");

    // Proses export CSV
    if (isset($_GET['export_csv'])) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=cbt_hasil.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID','Username','Skor','Waktu']);
        $rows = $wpdb->get_results("SELECT h.*, u.user_login FROM $table_hasil h LEFT JOIN {$wpdb->prefix}users u ON h.user_id = u.ID");
        foreach ($rows as $r) {
            fputcsv($output, [$r->id, $r->user_login, $r->skor, $r->waktu_submit]);
        }
        exit;
    }

    // Statistik skor
    $stats = $wpdb->get_results("SELECT user_id, COUNT(*) AS jml, AVG(skor) AS avg_skor FROM $table_hasil GROUP BY user_id");
    echo '<div class="wrap"><h1>📊 CBT Admin Panel</h1>';

    // Form tambah/edit soal
    echo '<h2>' . ($edit_soal ? '✏️ Edit Soal' : '➕ Tambah Soal') . '</h2>';
    echo '<form method="post">';
    if ($edit_soal) {
        echo '<input type="hidden" name="soal_id" value="' . intval($edit_soal->id) . '">';
    }
    echo '<table class="form-table"><tbody>
    <tr><th>Kategori</th><td><input type="text" name="kategori" class="regular-text" required value="'.esc_attr($edit_soal->kategori ?? '').'"></td></tr>
    <tr><th>Pertanyaan</th><td><textarea name="pertanyaan" class="large-text" rows="3" required>'.esc_textarea($edit_soal->pertanyaan ?? '').'</textarea></td></tr>
    <tr><th>Opsi A</th><td><input type="text" name="opsi_a" class="regular-text" required value="'.esc_attr($edit_soal->opsi_a ?? '').'"></td></tr>
    <tr><th>Opsi B</th><td><input type="text" name="opsi_b" class="regular-text" required value="'.esc_attr($edit_soal->opsi_b ?? '').'"></td></tr>
    <tr><th>Opsi C</th><td><input type="text" name="opsi_c" class="regular-text" required value="'.esc_attr($edit_soal->opsi_c ?? '').'"></td></tr>
    <tr><th>Opsi D</th><td><input type="text" name="opsi_d" class="regular-text" required value="'.esc_attr($edit_soal->opsi_d ?? '').'"></textarea></td></tr>
    <tr><th>Jawaban Benar</th><td><input type="text" name="jawaban" maxlength="1" required class="small-text" value="'.esc_attr($edit_soal->jawaban ?? '').'"></td></tr>
    </tbody></table>
    <p><button type="submit" name="submit_soal" class="button button-primary">'.($edit_soal ? 'Update Soal' : 'Simpan Soal').'</button></p>
    </form><hr>';

    // Filter kategori
    echo '<h2>🔍 Daftar Soal</h2>';
    echo '<form method="get"><input type="hidden" name="page" value="cbt_admin"><select name="filter_kategori"><option value="">All Kategori</option>';
    foreach ($kategori_list as $kat) {
        echo '<option value="'.esc_attr($kat).'"'.selected($kat,$kategori_filter,false).'>'.esc_html($kat).'</option>';
    }
    echo '</select> <button class="button">Filter</button></form>';

    // Tabel soal
    if ($soal_list) {
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Kategori</th><th>Pertanyaan</th><th>Jawaban</th><th>Aksi</th></tr></thead><tbody>';
        foreach ($soal_list as $s) {
            echo '<tr>
                <td>'.$s->id.'</td>
                <td>'.esc_html($s->kategori).'</td>
                <td>'.esc_html($s->pertanyaan).'</td>
                <td><strong>'.esc_html($s->jawaban).'</strong></td>
                <td>
                    <a href="'.admin_url('admin.php?page=cbt_admin&edit_soal='.$s->id).'" class="button button-small">Edit</a>
                    <a href="'.admin_url('admin.php?page=cbt_admin&hapus_soal='.$s->id).'" class="button button-small" onclick="return confirm(\'Yakin ingin hapus soal?\')">Hapus</a>
                </td>
            </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Belum ada soal untuk kategori ini.</p>';
    }

    // Statistik skor per siswa
    echo '<hr><h2>📈 Statistik Skor Siswa</h2>';
    echo '<table class="widefat fixed striped"><thead><tr><th>User</th><th>Jumlah Tes</th><th>Rata‑rata Skor</th></tr></thead><tbody>';
    foreach ($stats as $st) {
        $user = get_userdata($st->user_id);
        echo '<tr><td>'.esc_html($user->user_login).'</td><td>'.$st->jml.'</td><td>'.round($st->avg_skor,2).'</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p><a href="'.admin_url('admin.php?page=cbt_admin&export_csv=1').'" class="button button-secondary">⬇️ Export Skor ke CSV</a></p>';
    echo '</div>';
}
