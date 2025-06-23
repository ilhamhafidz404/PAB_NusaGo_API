<?php
class UserController
{
    public function index()
    {
        global $pdo;
        header('Content-Type: application/json');

        try {
            /* ─────────────────────
            Ambil data user
            ───────────────────── */
            $stmt = $pdo->prepare("
                SELECT 
                    *
                FROM 
                    accounts
                WHERE
                    role != 'admin'
                ORDER BY 
                    id 
                DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC); // gunakan fetchAll!

            /* ─────────────────────
            Response
            ───────────────────── */
            if (empty($users)) {
                resError('Data user kosong', '', 404);
                return;
            }

            sendSuccess([
                'message' => 'Daftar user berhasil diambil',
                'data'    => $users
            ]);

        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
    }

    
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

    public function delete()
    {
        global $pdo;
        header('Content-Type: application/json');

        /* ─── Ambil id dari query string ─── */
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            resError('Parameter id tidak valid.', '', 400);
        }

        try {
            /* ─── Cek akun ─── */
            $stmt = $pdo->prepare("SELECT deleted_at FROM accounts WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                resError('User tidak ditemukan.', '', 404);
            }

            /* ─── Tentukan aksi: nonaktifkan / aktifkan ─── */
            if ($row['deleted_at'] === null) {
                // Akun aktif → lakukan soft-delete
                $stmt = $pdo->prepare("UPDATE accounts SET deleted_at = NOW() WHERE id = :id");
                $stmt->execute([':id' => $id]);

                sendSuccess(['message' => 'User berhasil dinonaktifkan (soft delete)']);
            } else {
                // Akun sudah nonaktif → aktifkan kembali
                $stmt = $pdo->prepare("UPDATE accounts SET deleted_at = NULL WHERE id = :id");
                $stmt->execute([':id' => $id]);

                sendSuccess(['message' => 'User berhasil diaktifkan kembali']);
            }

        } catch (PDOException $e) {
            resError('Terjadi kesalahan saat memproses permintaan.', $e->getMessage(), 500);
        }
    }
}
