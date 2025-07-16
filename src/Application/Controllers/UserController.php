<?php

namespace App\Application\Controllers;

use App\Application\Settings\SettingsInterface;
use Slim\Views\Twig;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Medoo\Medoo;
use App\Application\Helpers\IndoDate;

class UserController
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $lists = $this->db->select('tbl_users', '*');

        return $this->view->render($response, 'users/index.twig', [
            'lists' => $lists,
            'session' => ['flash' => $flash],
            'current_path' => $request->getUri()->getPath()
        ]);
    }


    public function create(Request $request, Response $response): Response
    {

        return $this->view->render($response, 'users/create.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $existing = $this->db->get('tbl_users', '*', ['email' => $data['email']]);
        if ($existing) {
            $_SESSION['flash'] = 'Email sudah ada.';
            return $response
                ->withHeader('Location', '/users/create')
                ->withStatus(302);
        }

        $this->db->insert('tbl_users', [
            'email'         => $data['email'],
            'password'      => $data['password'],
            'name'          => $data['name'],
            'role'          => $data['role'],
            'status'        => 1,
            'created_at'    => IndoDate::now(),
        ]);
        $_SESSION['flash'] = 'Data berhasil ditambakan.';
        return $response
            ->withHeader('Location', '/users')
            ->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $this->db->get('tbl_users', '*', ['id' => $id]);

        if (!$data) {
            throw new HttpNotFoundException($request, "Data tidak ditemukan.");
        }

        return $this->view->render($response, 'users/edit.twig', [
            'detail' => $data,
            'current_path' => $request->getUri()->getPath()
        ]);
    }

   public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $existing = $this->db->get('tbl_users', '*', [
            'email' => $data['email'],
            'id[!]' => $id,
        ]);

        if ($existing) {
            $_SESSION['error'] = 'Email sudah digunakan oleh user lain.';
            return $response->withHeader('Location', '/users/' . $id . '/edit')->withStatus(302);
        }

        $updateData = [
            'email'      => $data['email'],
            'name'       => $data['name'],
            'role'       => $data['role'],
            'updated_at' => IndoDate::now(),
        ];

        $this->db->update('tbl_users', $updateData, ['id' => $id]);

        $_SESSION['flash'] = 'Data berhasil diperbarui.';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int) $args['id'];

        $this->db->delete('tbl_users', ['id' => $id]);

        $_SESSION['flash'] = 'Data berhasil dihapus.';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }



}
