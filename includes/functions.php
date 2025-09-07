<?php

// ========== CBT TEST PAGE ==========
function cbt_display_test() {
    if (!is_user_logged_in()) {
        return "<div class='alert alert-warning mt-3'>⚠️ Silakan login terlebih dahulu untuk mengikuti ujian.</div>";
    }

    $user = wp_get_current_user();
    if (!in_array('subscriber', $user->roles)) {
        return "<div class='alert alert-danger mt-3'>❌ Akses ditolak. Hanya siswa yang bisa mengikuti ujian.</div>";
    }

    global $wpdb;
    $soal = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cbt_soal ORDER BY RAND() LIMIT 5");

    if (isset($_POST['submit_cbt'])) {
        $skor = 0;
        foreach ($soal as $s) {
            $jawaban = $_POST['jawaban'][$s->id] ?? '';
            if (strtoupper($jawaban) === strtoupper($s->jawaban)) {
                $skor++;
            }
        }
        $wpdb->insert("{$wpdb->prefix}cbt_hasil", [
            'user_id' => get_current_user_id(),
            'skor' => $skor
        ]);
        return "<div class='alert alert-success mt-4'>🎉 Skor Anda: <strong>$skor</strong> dari " . count($soal) . " soal.</div>";
    }

    ob_start();
    ?>
    <form method="post" id="cbt-form" class="container mt-4">
        <div class="alert alert-info">⏳ Sisa waktu: <span id="timer">10:00</span></div>
        <p><a href="<?php echo esc_url(home_url('/logout')); ?>" class="btn btn-outline-secondary btn-sm float-end">🚪 Logout</a></p>

        <?php foreach ($soal as $s) : ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo esc_html($s->pertanyaan); ?></h5>
                    <?php foreach (['A', 'B', 'C', 'D'] as $opsi) : 
                        $opsi_text = $s->{'opsi_' . strtolower($opsi)}; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jawaban[<?php echo $s->id; ?>]" value="<?php echo $opsi; ?>" id="soal<?php echo $s->id . '_' . $opsi; ?>">
                            <label class="form-check-label" for="soal<?php echo $s->id . '_' . $opsi; ?>">
                                <?php echo esc_html($opsi_text); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-grid col-6 mx-auto mt-4">
            <button type="submit" name="submit_cbt" class="btn btn-success">✅ Kirim Jawaban</button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

// ========== REGISTRASI SISWA ==========
function cbt_register_form() {
    if (is_user_logged_in()) {
        return "<div class='alert alert-success'>✅ Anda sudah login sebagai " . esc_html(wp_get_current_user()->user_login) . "</div>";
    }

    $error = '';
    if (isset($_POST['cbt_register_submit'])) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        if (username_exists($username) || email_exists($email)) {
            $error = "⚠️ Username atau email sudah terdaftar.";
        } else {
            $user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($user_id)) {
                wp_update_user(['ID' => $user_id, 'role' => 'subscriber']);
                $creds = [
                    'user_login'    => $username,
                    'user_password' => $password,
                    'remember'      => true,
                ];
                $user = wp_signon($creds, false);

                if (!is_wp_error($user)) {
                    wp_redirect(home_url('/ujian'));
                    exit;
                } else {
                    $error = "❌ Gagal login setelah registrasi: " . esc_html($user->get_error_message());
                }
            } else {
                $error = "❌ Gagal mendaftar: " . esc_html($user_id->get_error_message());
            }
        }
    }

    ob_start();
    ?>
    <div class="container mt-4" style="max-width:500px">
        <h3>📝 Registrasi Siswa</h3>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="cbt_register_submit" class="btn btn-primary">Daftar & Login</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
// ========== LOGIN SISWA ==========

function cbt_login_form() {
    if (is_user_logged_in()) {
        return "<div class='alert alert-success'>✅ Anda sudah login sebagai " . esc_html(wp_get_current_user()->user_login) . "</div>";
    }

    $error = '';
    if (isset($_POST['cbt_login_submit'])) {
        $creds = [
            'user_login'    => sanitize_user($_POST['username']),
            'user_password' => $_POST['password'],
            'remember'      => true,
        ];
        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            $error = "❌ Login gagal. Periksa username/password.";
        } else {
            wp_redirect(home_url('/ujian'));
            exit;
        }
    }

    // Mulai output buffering
    ob_start();

    ?>
    <div class="container mt-4" style="max-width:500px">
        <h3>🔐 Login Siswa</h3>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="cbt_login_submit" class="btn btn-success">Login</button>
        </form>
    </div>
    <?php

    // Hentikan buffering dan kembalikan output
    return ob_get_clean();
}

// ========== LOGOUT SISWA ==========
function cbt_logout_page() {
    if (is_user_logged_in()) {
        wp_logout();
        wp_redirect(home_url('/login'));
        exit;
    } else {
        return "<div class='alert alert-info mt-3'>Anda sudah logout.</div>";
    }
}
