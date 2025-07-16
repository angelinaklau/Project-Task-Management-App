<?php

namespace App\Application\Controllers;

use App\Application\Settings\SettingsInterface;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Medoo\Medoo;
use App\Application\Helpers\IndoDate;

class AuthController
{
    private Twig $view;
    private Medoo $db;

    public function __construct(Twig $view, SettingsInterface $settings)
    {
        $this->view = $view;
        $this->db = $settings->get('database');
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        session_start();
        $data = $request->getParsedBody();

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $user = $this->db->get('tbl_users', '*', ['email' => $email]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ];

            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        return $this->view->render($response, 'auth/login.twig', [
            'error' => 'Email atau password salah.',
            'old' => $data,
        ]);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $data = $request->getParsedBody();

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';

        // Validasi kosong
        if (empty($name) || empty($email) || empty($password) || empty($passwordConfirm)) {
            return $this->view->render($response, 'auth/register.twig', [
                'error' => 'Semua field wajib diisi.',
                'old' => $data
            ]);
        }

        // Validasi konfirmasi password
        if ($password !== $passwordConfirm) {
            return $this->view->render($response, 'auth/register.twig', [
                'error' => 'Password dan konfirmasi tidak cocok.',
                'old' => $data
            ]);
        }

        // Cek email
        $existing = $this->db->get('tbl_users', '*', ['email' => $email]);
        if ($existing) {
            return $this->view->render($response, 'auth/register.twig', [
                'error' => 'Email sudah terdaftar.',
                'old' => $data
            ]);
        }

        // Simpan user
        $id = $this->db->insert('tbl_users', [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'role' => 'admin',
            'status' => 1,
            'created_at' => IndoDate::now()
        ]);

        if ($id) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        } else {
            return $this->view->render($response, 'auth/register.twig', [
                'error' => 'Gagal daftar.',
                'old' => $data
            ]);
        }
    }

    public function logout($request, $response, $args)
    {
        session_start();

        if (isset($_SESSION['user'])) {
            $userId = $_SESSION['user']['id'];
            $now = IndoDate::now();

            $this->db->update('tbl_users', [
                'last_login_time' => $now,
                'updated_at' => $now
            ], [
                'id' => $userId
            ]);
        }

        session_destroy();

        return $response->withHeader('Location', '/login')->withStatus(302);
    }


}
