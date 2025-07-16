<?php

namespace App\Application\Controllers;

use Slim\Views\Twig;
use App\Application\Settings\SettingsInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Medoo\Medoo;

class DashboardController
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
        return $this->view->render($response, 'home.twig');
    }

    public function dashboard(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'dashboard.twig', [
            'user' => $_SESSION['user'],
            'totalUsers' => $this->db->count('tbl_users'),
            'totalBoards' => $this->db->count('tbl_boards'),
            'totalTasks' => $this->db->count('tbl_tasks'),
            'totalStatuses' => $this->db->count('tbl_task_statuses'),
            'current_path' => $request->getUri()->getPath()
        ]);
    }
}