<?php

use Dotenv\Dotenv;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../Controllers/Usuario/usuarioController.php";
require_once __DIR__ . "/../Controllers/Mesa/mesaController.php";


$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Credentials: true');

$rota = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$metodo = $_SERVER['REQUEST_METHOD'];


if ($metodo === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($rota === '/usuario') {
    $usuarioController = new UsuarioController();

    if ($metodo === 'GET') {
        $usuarioController->listarUsuarios();
    }

    if ($metodo === 'POST') {
        $usuarioController->criarUsuario();
    }
    if ($metodo === 'PUT') {
        $usuarioController->atualizarUsuario();
    }
    if ($metodo === 'DELETE') {
        $usuarioController->deletarUsuario();
    }
}

if ($rota === '/usuario/login') {
    $usuarioController = new UsuarioController();

    if ($metodo === 'POST') {
        $usuarioController->fazerLogin();
    }
}


if ($rota === '/mesa') {
    $mesaController = new MesaController();

    if ($metodo === 'GET') {
        $mesaController->listarMesas();
    }

    if ($metodo === 'POST') {
        $mesaController->criarMesa();
    }
    if ($metodo === 'PUT') {
        $mesaController->atualizarMesa();
    }
    if ($metodo === 'DELETE') {
        $mesaController->deletarMesa();
    }
}

http_response_code(404);
echo json_encode([
    'sucesso' => false,
    'mensagem' => 'Rota não encontrada'
]);
exit;
