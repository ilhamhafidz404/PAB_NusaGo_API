<?php
/**
 * TransactionController.php
 * Route contoh:
 *   GET  /transactions           → index()
 *   POST /transactions/add       → store()
 *   GET  /transactions/export    → export()
 */

class TransactionController
{
    /* ─────────────────── LIST / INDEX ─────────────────── */
    public function index()
    {
        global $pdo;
        header('Content-Type: application/json');

        try {
            $stmt = $pdo->query("
                SELECT 
                    t.id, 
                    t.invoice, 
                    t.user_id, 
                    a.name, 
                    t.created_at
                FROM 
                    transactions t
                INNER JOIN
                    accounts a
                ON
                    a.id = t.user_id
                ORDER BY 
                    id 
                DESC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendSuccess([
                'message' => 'Daftar transaksi berhasil diambil',
                'data'    => $rows
            ]);

        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
    }

    /* ─────────────────── STORE / ADD ─────────────────── */
    public function store()
    {
        global $pdo;
        header('Content-Type: application/json');

        $invoice = $_POST['invoice']  ?? '';
        $userId  = $_POST['user_id']  ?? 0;

        if (!$invoice || !$userId) {
            resError('Invoice & user_id wajib diisi.', '', 400);
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO transactions (invoice, user_id, created_at)
                VALUES (:invoice, :user_id, NOW())
            ");
            $ok = $stmt->execute([
                ':invoice' => $invoice,
                ':user_id' => $userId
            ]);

            if ($ok) {
                sendSuccess(['message' => 'Transaksi berhasil ditambahkan'], 201);
            } else {
                resError('Gagal menambah transaksi.', '', 400);
            }

        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
    }

    /* ─────────────────── EXPORT (Excel) ─────────────────── */
    public function export()
    {
        global $pdo;

        try {
            /* ─── Query dengan alias & id ─── */
            $stmt = $pdo->query("
                SELECT
                    t.id,
                    t.invoice,
                    u.name   AS user_name,
                    t.created_at
                FROM
                    transactions t
                INNER JOIN
                    accounts u ON u.id = t.user_id
                ORDER BY
                    t.id DESC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /* ─── Header file ─── */
            $filename = "transactions_" . date('Ymd_His') . ".xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            /* ─── Header kolom ─── */
            echo "No\tInvoice\tUser Name\tCreated At\n";

            /* ─── Data ─── */
            $no = 1;
            foreach ($rows as $r) {
                // Format tanggal → 23/06/25 14:30
                $dateFmt = date('d/m/y H:i', strtotime($r['created_at']));

                echo $no . "\t" .
                    $r['invoice'] . "\t" .
                    $r['user_name'] . "\t" .
                    $dateFmt . "\n";

                $no++;
            }
            exit;

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Export gagal.',
                'detail'  => $e->getMessage()
            ]);
            exit;
        }
    }

}
