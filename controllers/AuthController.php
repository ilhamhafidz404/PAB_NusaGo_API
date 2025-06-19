<?php

class AuthController {
    public function login() {
        global $pdo;

        // request parameters
        $email      = $_POST['email'] ?? '';
        $password   = $_POST['password'] ?? '';
    
        header('Content-Type: application/json');

        try {
            $stmt = $pdo->prepare("SELECT * FROM accounts WHERE email = :email AND password = :password");
            $stmt->execute([
                'email' => $email,
                'password' => $password,
            ]);
            
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($account) {
                // Making Response Success Get Data
                http_response_code(200); // status code OK
                echo json_encode([
                    'status'    => 'success',
                    'message'   => 'Login berhasil',
                    'data'      => [
                        'id'            => $account['id'],
                        'name'          => $account['name'],
                        'email'         => $account['email'],
                        'role'          => $account['role'],
                        'verified_at'   => $account['verified_at'],
                    ]
                ]);
            } else {
                // Making Response Error Get Data
                http_response_code(401); // status code Unauthorized
                echo json_encode([
                    'status'    => 'error',
                    'message'   => 'Username atau password salah'
                ]);
            }
        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }
    
        exit;
    }    

    public function registration() {
        global $pdo;

        // request parameters
        $name       = $_POST['name'] ?? '';
        $email      = $_POST['email'] ?? '';
        $password   = $_POST['password'] ?? '';
        $role       = $_POST['role'] ?? 'user';

        header('Content-Type: application/json');

        try {
            // Query Insert Data Account
            $stmt = $pdo->prepare("INSERT INTO accounts (name, email, password, role) VALUES (:name, :email, :password, :role)");
            $result = $stmt->execute([
                'name'      => $name,
                'email'     => $email,
                'password'  => $password,
                'role'      => $role,
            ]);

            if ($result) {
                // Making Response Success Insert Account
                http_response_code(200);  // status code OK
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Registrasi berhasil',
                ]);
            } else {
                // Making Response Error Insert Account
                http_response_code(400);  // status code Bad Request
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal melakukan registrasi'
                ]);
            }
        } catch (PDOException $e) {
            resError('Terjadi kesalahan pada server.', $e->getMessage(), 500);
        }

        exit;
    }
}
