<?php

declare(strict_types=1);

use App\Application\Controllers\AuthController;
use App\Application\Controllers\BoardController;
use App\Application\Controllers\BookController;
use App\Application\Controllers\DashboardController;
use App\Application\Controllers\ProfileController;
use App\Application\Controllers\StatusTaskController;
use App\Application\Controllers\UserController;
use App\Application\Middleware\AuthMiddleware;
use Slim\App;


return function (App $app) {
    $app->get('/', [DashboardController::class, 'index']);

    $app->get('/login', [AuthController::class, 'index']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/register', [AuthController::class, 'showRegister']);
    $app->post('/register', [AuthController::class, 'register']);
    $app->get('/logout', [AuthController::class, 'logout']);

    $app->group('', function (\Slim\Routing\RouteCollectorProxy $group) {
        $group->get('/dashboard', [DashboardController::class, 'dashboard']);

        // status-task
        $group->get('/status-task', [StatusTaskController::class, 'index']);
        $group->get('/status-task/create', [StatusTaskController::class, 'create']);
        $group->post('/status-task/create', [StatusTaskController::class, 'store']);
        $group->get('/status-task/{id}/edit', [StatusTaskController::class, 'edit']);
        $group->post('/status-task/{id}/update', [StatusTaskController::class, 'update']);
        $group->post('/status-task/{id}/delete', [StatusTaskController::class, 'delete']);

        // boards
        $group->get('/boards', [BoardController::class, 'index']);
        $group->get('/boards/create', [BoardController::class, 'create']);
        $group->post('/boards/create', [BoardController::class, 'store']);
        $group->get('/boards/{id}/edit', [BoardController::class, 'edit']);
        $group->get('/boards/{id}/show', [BoardController::class, 'show']);
        $group->post('/boards/{id}/update', [BoardController::class, 'update']);
        $group->post('/boards/{id}/delete', [BoardController::class, 'delete']);
        $group->get('/boards/{id}/task/{status_id}', [BoardController::class, 'task']);
        $group->post('/boards/task', [BoardController::class, 'processTask']);
        $group->get('/tasks/{id}/delete/{task_id}', [BoardController::class, 'deleteTask']);


        // users
        $group->get('/users', [UserController::class, 'index']);
        $group->get('/users/create', [UserController::class, 'create']);
        $group->post('/users/create', [UserController::class, 'store']);
        $group->get('/users/{id}/edit', [UserController::class, 'edit']);
        $group->post('/users/{id}/update', [UserController::class, 'update']);
        $group->post('/users/{id}/delete', [UserController::class, 'delete']);

        $group->get('/profile', [ProfileController::class, 'show']);
    })->add(new AuthMiddleware());
};