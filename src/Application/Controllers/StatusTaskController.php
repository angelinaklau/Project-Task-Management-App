<?php

namespace App\Application\Controllers;

use App\Application\Settings\SettingsInterface;
use Slim\Views\Twig;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Medoo\Medoo;

class StatusTaskController
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

        $lists = $this->db->select('tbl_task_statuses', '*');

        return $this->view->render($response, 'status-task/index.twig', [
            'lists' => $lists,
            'session' => ['flash' => $flash],
            'current_path' => $request->getUri()->getPath()
        ]);
    }


    public function create(Request $request, Response $response): Response
    {

        return $this->view->render($response, 'status-task/create.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $this->db->insert('tbl_task_statuses', [
            'name' => $data['name']
        ]);
        $_SESSION['flash'] = 'Data berhasil ditambakan.';
        return $response
            ->withHeader('Location', '/status-task')
            ->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $this->db->get('tbl_task_statuses', '*', ['id' => $id]);

        if (!$data) {
            throw new HttpNotFoundException($request, "Data tidak ditemukan.");
        }

        return $this->view->render($response, 'status-task/edit.twig', [
            'detail' => $data,
            'current_path' => $request->getUri()->getPath()
        ]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $this->db->update('tbl_task_statuses', [
            'name' => $data['name']
        ], ['id' => $id]);

        $_SESSION['flash'] = 'Data berhasil diperbarui.';

        return $response->withHeader('Location', '/status-task')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int) $args['id'];

        // $bookCount = $this->db->count('tbl_task_statuses', ['category_id' => $id]);

        // if ($bookCount > 0) {
        //     $_SESSION['flash'] = 'Status tidak dapat dihapus karena masih digunakan oleh buku.';
        //     return $response->withHeader('Location', '/status-task')->withStatus(302);
        // }

        $this->db->delete('tbl_task_statuses', ['id' => $id]);

        $_SESSION['flash'] = 'Data berhasil dihapus.';
        return $response->withHeader('Location', '/status-task')->withStatus(302);
    }



}
