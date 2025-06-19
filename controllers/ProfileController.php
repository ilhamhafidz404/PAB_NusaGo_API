<?php
class ProfileController
{
    public function update()
    {
        global $pdo;
        header('Content-Type: application/json');

        /* ─────────────────────
           Ambil payload POST
        ──────────────────────*/
        $id       = $_POST['id']       ?? null;   // wajib
        $name     = $_POST['name']     ?? null;   // opsional
        $email    = $_POST['email']    ?? null;   // opsional
        $password = $_POST['password'] ?? null;   // opsional

        /* ─────────────────────
           Validasi dasar
        ──────────────────────*/
        if (!$id) {
            resError('ID user tidak valid');
        }

        /* ─────────────────────
           Ambil data user lama
        ──────────────────────*/
        $stmtOld = $pdo->prepare("SELECT * FROM accounts WHERE id = :id LIMIT 1");
        $stmtOld->execute(['id' => $id]);
        $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$oldData) {
            resError('User tidak ditemukan', '', 404);
        }

        /* ─────────────────────
           Bangun query dinamis
        ──────────────────────*/
        $fields = [];
        $params = ['id' => $id];

        if ($name !== null) {
            $fields[] = 'name = :name';
            $params['name'] = $name;
        }

        if ($email !== null) {
            $fields[] = 'email = :email';
            $params['email'] = $email;
        }

        // Hanya update password jika tidak kosong
        if ($password !== null && trim($password) !== "") {
            $fields[] = 'password = :password';
            $params['password'] = $password;
        }

        if (empty($fields)) {
            resError('Tidak ada data yang diubah');
        }

        $sql = "UPDATE accounts SET " . implode(', ', $fields) . " WHERE id = :id";

        /* ─────────────────────
           Eksekusi & respon
        ──────────────────────*/
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            sendSuccess(['message' => 'Profil berhasil diperbarui']);
        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
    }
}
