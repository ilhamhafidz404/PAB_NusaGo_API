<?php
class NewsController
{
    /* ───────────────────────── READ ALL ───────────────────────── */
    public function index()
    {
        global $pdo;
        header('Content-Type: application/json');

        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 3;  // pastikan integer & sanitasi
        $limit = max(1, $limit); 

        try {
            $stmt = $pdo->prepare("SELECT * FROM news
                       ORDER BY created_at DESC
                       LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT); 
            $stmt->execute();
            $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode([
                'status'  => 'success',
                'message' => 'Get Data News Success',
                'data'    => $news
            ]);

        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
        exit;
    }

    /* ───────────────────────── CREATE ───────────────────────── */
    public function store()
    {
        global $pdo;
        header('Content-Type: application/json');

        $title       = $_POST['title']        ?? '';
        $description = $_POST['description']  ?? '';
        $body        = $_POST['body']         ?? '';
        $image       = $_POST['image']        ?? '';
        $user_id     = $_POST['user_id']      ?? 1; 

        /* ── Validasi field dasar ── */
        if (!$title || !$description || !$body || !$user_id) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Field wajib tidak lengkap.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO news (title, description, body, image, user_id, created_at)
                 VALUES (:title, :description, :body, :image, :user_id, NOW())"
            );
            $result = $stmt->execute([
                'title'       => $title,
                'description' => $description,
                'body'        => $body,
                'image'       => $image,
                'user_id'     => $user_id
            ]);

            if ($result) {
                http_response_code(201);    // Created
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Berita berhasil ditambahkan'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Gagal menambah berita'
                ]);
            }
        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
        exit;
    }

    /* ───────────────────────── READ SINGLE ───────────────────────── */
    public function show()
    {
        global $pdo;

        $id = $_GET["id"] ?? "1";

        header('Content-Type: application/json');

        try {
            $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($news) {
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'data'   => $news
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Berita tidak ditemukan'
                ]);
            }

        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
        exit;
    }

    /* ───────────────────────── UPDATE ───────────────────────── */
    public function update($id)
    {
        global $pdo;
        header('Content-Type: application/json');

        // ambil field yang di‐submit (boleh sebagian)
        $title       = $_POST['title']       ?? null;
        $description = $_POST['description'] ?? null;
        $body        = $_POST['body']        ?? null;

        // cek record lama (untuk mengambil path gambar lama jika akan diganti)
        $stmtOld = $pdo->prepare("SELECT * FROM news WHERE id = :id");
        $stmtOld->execute(['id' => $id]);
        $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$oldData) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Berita tidak ditemukan']);
            exit;
        }

        /* ── Upload gambar baru (optional) ── */
        $imagePath = $oldData['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->handleImageUpload($_FILES['image']);
            if ($imagePath === false) return;

            // Hapus file lama jika ada
            if ($oldData['image'] && file_exists($oldData['image'])) {
                @unlink($oldData['image']);
            }
        }

        // Bangun query dinamis
        $fields = [];
        $params = ['id' => $id];
        if ($title       !== null) { $fields[] = 'title = :title';       $params['title']       = $title; }
        if ($description !== null) { $fields[] = 'description = :description'; $params['description'] = $description; }
        if ($body        !== null) { $fields[] = 'body = :body';         $params['body']        = $body; }
        if ($imagePath   !== null) { $fields[] = 'image = :image';       $params['image']       = $imagePath; }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diubah']);
            exit;
        }

        $sql = "UPDATE news SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";

        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Berita berhasil diupdate']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Gagal update berita']);
            }
        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
        exit;
    }

    /* ───────────────────────── DELETE ───────────────────────── */
    public function destroy()
    {
        global $pdo;
        header('Content-Type: application/json');

        $id = $_POST["id"] ?? "1";

        // cek record lama
        $stmtOld = $pdo->prepare("SELECT image FROM news WHERE id = :id");
        $stmtOld->execute(['id' => $id]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

        try {
            $stmt = $pdo->prepare("DELETE FROM news WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);

            if ($result) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Berita berhasil dihapus']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus berita']);
            }
        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
        exit;
    }

    /* ───────────────────────── PRIVATE HELPER ───────────────────────── */
    /**
     * Menangani upload gambar, me‑return path file atau false (kalau terjadi error & sudah kirim response)
     */
    private function handleImageUpload(array $file)
    {
        header('Content-Type: application/json');

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            http_response_code(415); // Unsupported Media Type
            echo json_encode(['status' => 'error', 'message' => 'Format gambar tidak didukung']);
            return false;
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2 MB
            http_response_code(413); // Payload Too Large
            echo json_encode(['status' => 'error', 'message' => 'Ukuran gambar maksimal 2 MB']);
            return false;
        }

        // pastikan folder upload ada
        $uploadDir = __DIR__ . '/../../public/uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('news_', true) . '.' . $ext;
        $path = $uploadDir . $name;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan gambar di server']);
            return false;
        }

        /* Jika kamu ingin path relatif/url, sesuaikan berikut */
        return 'uploads/news/' . $name; // simpan ke DB
    }
}
