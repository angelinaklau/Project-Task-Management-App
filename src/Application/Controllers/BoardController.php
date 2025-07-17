<?php

namespace App\Application\Controllers;

use App\Application\Settings\SettingsInterface;
use Slim\Views\Twig;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Medoo\Medoo;
use App\Application\Helpers\IndoDate;

class BoardController
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

        $lists = $this->db->select('tbl_boards', '*');

        return $this->view->render($response, 'board/index.twig', [
            'lists' => $lists,
            'session' => ['flash' => $flash],
            'current_path' => $request->getUri()->getPath()
        ]);
    }


    public function create(Request $request, Response $response): Response
    {

        return $this->view->render($response, 'board/create.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $this->db->insert('tbl_boards', [
            'name'          => $data['name'],
            'user_id'       => $_SESSION['user']['id'],
            'created_at'    => IndoDate::now(),
            
        ]);
        $_SESSION['flash'] = 'Data berhasil ditambakan.';
        return $response
            ->withHeader('Location', '/boards')
            ->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $this->db->get('tbl_boards', '*', ['id' => $id]);

        if (!$data) {
            throw new HttpNotFoundException($request, "Data tidak ditemukan.");
        }

        return $this->view->render($response, 'board/edit.twig', [
            'detail' => $data,
            'current_path' => $request->getUri()->getPath()
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $this->db->get('tbl_boards', '*', ['id' => $id]);
        $status = $this->db->select('tbl_task_statuses', '*');

        if (!$data) {
            throw new HttpNotFoundException($request, "Data tidak ditemukan.");
        }

        $tasks = $this->db->select('tbl_tasks', '*', ['board_id' => $id]);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $groupedTasks = [];
        foreach ($tasks as $task) {
            $groupedTasks[$task['status_id']][] = $task;
        }

        return $this->view->render($response, 'board/show.twig', [
            'detail' => $data,
            'status' => $status,
            'tasks'  => $groupedTasks,
            'session' => ['flash' => $flash],
            'current_path' => $request->getUri()->getPath()
        ]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $this->db->update('tbl_boards', [
            'name'      => $data['name'],
            'updated_at'=> IndoDate::now(),
        ], ['id' => $id]);

        $_SESSION['flash'] = 'Data berhasil diperbarui.';

        return $response->withHeader('Location', '/boards')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int) $args['id'];

        $this->db->delete('tbl_boards', ['id' => $id]);

        $_SESSION['flash'] = 'Data berhasil dihapus.';
        return $response->withHeader('Location', '/boards')->withStatus(302);
    }

    public function task(Request $request, Response $response, array $args): Response
    {
        $boardId = (int) $args['id'];
        $statusId = (int) $args['status_id'];

        $board = $this->db->get('tbl_boards', '*', ['id' => $boardId]);
        if (!$board) {
            throw new HttpNotFoundException($request, "Board tidak ditemukan.");
        }

        $status = $this->db->get('tbl_task_statuses', '*', ['id' => $statusId]);
        if (!$status) {
            throw new HttpNotFoundException($request, "Status tidak ditemukan.");
        }

        return $this->view->render($response, 'board/task/create.twig', [
            'board'     => $board,
            'status'    => $status,
            'current_path' => $request->getUri()->getPath()
        ]);
    }

    public function processTask(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $this->db->insert('tbl_tasks', [
            'board_id'      => $data['board_id'],
            'status_id'     => $data['status_id'],
            'user_id'       => $_SESSION['user']['id'],
            'title'         => $data['title'],
            'priority'      => $data['priority'],
            'description'   => $data['description'],
            'created_at'    => IndoDate::now(),
            'updated_at'    => IndoDate::now(),
        ]);
        $_SESSION['flash'] = 'Data berhasil ditambakan.';

        return $response
        ->withHeader('Location', '/boards/' . $data['board_id'] . '/show')
        ->withStatus(302);
    }

     public function editTask(Request $request, Response $response, array $args): Response
    {
        $taskId = (int) $args['id'];
        $task = $this->db->get('tbl_tasks', '*', ['id' => $taskId]);
        if (!$task) {
            throw new HttpNotFoundException($request, "Task tidak ditemukan.");
        }

        $board = $this->db->get('tbl_boards', '*', ['id' => $task['board_id']]);
        if (!$board) {
            throw new HttpNotFoundException($request, "Board tidak ditemukan.");
        }

        $statuses = $this->db->select('tbl_task_statuses', '*');

        return $this->view->render($response, 'board/task/edit.twig', [
            'task' => $task,
            'board' => $board,
            'statuses' => $statuses,
            'current_path' => $request->getUri()->getPath()
        ]);
    }

    public function updateTask(Request $request, Response $response, array $args): Response
    {
        $taskId = (int) $args['id'];
        $data = $request->getParsedBody();

        $this->db->update('tbl_tasks', [
            'title'       => $data['title'],
            'description' => $data['description'],
            'priority'    => $data['priority'],
            'status_id'   => $data['status_id'],
            'updated_at'  => IndoDate::now(),
        ], ['id' => $taskId]);

        $_SESSION['flash'] = 'Task berhasil diperbarui.';

        return $response
            ->withHeader('Location', '/boards/' . $data['board_id'] . '/show')
            ->withStatus(302);
    }

    public function deleteTask(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int) $args['id'];
        $taskId = (int) $args['task_id'];

        $this->db->delete('tbl_tasks', ['id' => $taskId]);

        $_SESSION['flash'] = 'Data berhasil dihapus.';
        return $response
        ->withHeader('Location', '/boards/' . $id . '/show')
        ->withStatus(302);
    }


}
