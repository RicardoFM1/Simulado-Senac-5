<?php
require_once __DIR__ . "/../../Middleware/middleware.php";

class DashboardController
{

    public function apenasAdmin()
    {
        $jwt = Middleware::validarMiddleware();

        if ($jwt->dados->cargo_usuario !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Usuário sem permissão'
            ]);
            exit;
        }
    }

    public function listarDashboard()
    {
        $this->apenasAdmin();
        $convidados = new ConvidadoController()->listarConvidados();


        $convidadosConfirmados = null;
        $convidadosNaoConfirmados = null;
        $convidadosCancelados = null;

        foreach ($convidados['dados'] as $convidado) {
            if ($convidado['confirmacao'] === 'confirmado') {
                $convidadosConfirmados++;
            }

            if ($convidado['confirmacao'] === 'não confirmado') {
                $convidadosNaoConfirmados++;
            }

            if ($convidado['confirmacao'] === 'cancelado') {
                $convidadosCancelados++;
            }
        }

        echo json_encode([
            'sucesso' => true,
            'dados' => [
                'convidados' => [
                    'listagem' => $convidados['dados'] ?? [],
                    'confirmados' => $convidadosConfirmados,
                    'nao_confirmados' => $convidadosNaoConfirmados,
                    'cancelados' => $convidadosCancelados,
                    'total' => count($convidados['dados'])
                ]
            ]
        ]);
        exit;
    }
}
