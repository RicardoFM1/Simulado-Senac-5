<?php

use Firebase\JWT\JWT;

require_once __DIR__ . "/../../Connection/db.php";
require_once __DIR__ . "/../Mesa/mesaService.php";

class ConvidadoService
{
    protected $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function buscarConvidadoPorEmail($emailConvidado)
    {
        if (empty($emailConvidado)) {
            throw new Exception('Dados inválidos', 400);
        }

        $buscar = $this->db->prepare('SELECT * FROM convidado WHERE email = :email');
        $buscar->execute([
            ':email' => $emailConvidado
        ]);

        $convidado = $buscar->fetch();

        if (empty($convidado)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Convidado não encontrado',
                'codigo' => 404
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $convidado
        ];
    }

    public function buscarConvidadosPorIdMesa($idMesa)
    {
        if (empty($idMesa)) {
            throw new Exception('Dados inválidos', 400);
        }

        $buscar = $this->db->prepare('SELECT * FROM convidado WHERE mesa_idmesa = :mesa_idmesa');
        $buscar->execute([
            ':mesa_idmesa' => $idMesa
        ]);

        $convidados = $buscar->fetchAll();

        if (empty($convidado)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Convidado não encontrado',
                'codigo' => '404'
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $convidados
        ];
    }



    public function listarConvidados()
    {
        $buscar = $this->db->query('SELECT * FROM convidado ORDER BY id_convidado DESC');

        $buscar->execute();

        $convidados = $buscar->fetchAll();

        return [
            'sucesso' => true,
            'dados' => $convidados,
            'total' => count($convidados)
        ];
    }

    public function criarConvidado($convidadoDados)
    {
        try {

            $convidadoDados['cpf'] = preg_replace('/\D/', '', $convidadoDados['cpf']);
            $convidadoDados['telefone'] = preg_replace('/\D/', '', $convidadoDados['telefone']);


            $mesaReferenciada = new MesaService()->buscarMesaPorId($convidadoDados['mesa_idmesa']);
            $convidadosReferenciando = $this->buscarConvidadosPorIdMesa($convidadoDados['mesa_idmesa']);

            if (count($convidadosReferenciando['dados']) >= $mesaReferenciada['dados']['capacidade']) {
                throw new Exception('Mesa lotada', 409);
            }

            $criar = $this->db->prepare('INSERT INTO convidado (nome, sobrenome, email, cpf, telefone, categoria, mesa_idmesa) 
    VALUES (:nome, :sobrenome, :email, :cpf, :telefone, :categoria, :mesa_idmesa)');

            $criar->execute([
                ':nome' => $convidadoDados['nome'],
                ':sobrenome' => $convidadoDados['sobrenome'],
                ':email' => $convidadoDados['email'],
                ':cpf' => $convidadoDados['cpf'],
                ':telefone' => $convidadoDados['telefone'],
                ':categoria' => $convidadoDados['categoria'],
                ':mesa_idmesa' => $convidadoDados['mesa_idmesa']
            ]);


            return [
                'sucesso' => true,
                'mensagem' => 'Convidado criado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'email')) {
                throw new Exception('Email já em uso', 409);
            }
            if (str_contains($e->getMessage(), 'cpf')) {
                throw new Exception('Cpf já em uso', 409);
            }



            if (str_contains($e->getMessage(), 'fk_convidado_mesa')) {
                throw new Exception('Mesa referenciada não encontrada', 404);
            }

            throw new Exception('Erro ao tentar criar convidado', 500);
        }
    }





    public function atualizarConvidado($convidadoDados, $emailConvidado)
    {
        try {
            $convidado = $this->buscarConvidadoPorEmail($emailConvidado);

            if ($convidado['sucesso'] === false) {
                throw new Exception($convidado['mensagem'], $convidado['codigo']);
            }

            $convidadoDados['cpf'] = preg_replace('/\D/', '', $convidadoDados['cpf']);
            $convidadoDados['telefone'] = preg_replace('/\D/', '', $convidadoDados['telefone']);


            $mesaReferenciada = new MesaService()->buscarMesaPorId($convidadoDados['mesa_idmesa']);
            $convidadosReferenciando = $this->buscarConvidadosPorIdMesa($convidadoDados['mesa_idmesa']);

            if (count($convidadosReferenciando['dados']) >= $mesaReferenciada['dados']['capacidade'] && $convidadoDados['mesa_idmesa'] !== $convidado['dados']['mesa_idmesa']) {
                throw new Exception('Mesa lotada', 409);
            }

            if ($convidadoDados['confirmacao'] !== 'cancelado') {
                throw new Exception('Só é possível cancelar um convidado', 400);
            }

            if ($convidado['dados']['confirmacao'] === 'confirmado') {
                throw new Exception('Não é possível cancelar um convidado já confirmado', 409);
            }

            $atualizar = $this->db->prepare('UPDATE convidado SET nome = :nome, sobrenome = :sobrenome,
            email = :email, cpf = :cpf, telefone = :telefone, categoria = :categoria, confirmacao = :confirmacao, mesa_idmesa = :mesa_idmesa
           WHERE email = :email_convidado');

            $atualizar->execute([
                ':nome' => $convidadoDados['nome'],
                ':sobrenome' => $convidadoDados['sobrenome'],
                ':email' => $convidadoDados['email'],
                ':cpf' => $convidadoDados['cpf'],
                ':telefone' => $convidadoDados['telefone'],
                ':categoria' => $convidadoDados['categoria'],
                ':confirmacao' => $convidadoDados['confirmacao'],
                ':mesa_idmesa' => $convidadoDados['mesa_idmesa'],
                ':email_convidado' => $emailConvidado
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Convidado atualizado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'email')) {
                throw new Exception('Email já em uso', 409);
            }
            if (str_contains($e->getMessage(), 'cpf')) {
                throw new Exception('Cpf já em uso', 409);
            }

            if (str_contains($e->getMessage(), 'fk_convidado_mesa')) {
                throw new Exception('Mesa referenciada não encontrada', 404);
            }

            throw new Exception('Erro ao tentar atualizar convidado', 500);
        }
    }


    public function deletarConvidado($emailConvidado)
    {
        try {

            $convidado = $this->buscarConvidadoPorEmail($emailConvidado);


            if ($convidado['sucesso'] === false) {
                throw new Exception($convidado['mensagem'], $convidado['codigo']);
            }
            if ($convidado['dados']['confirmacao'] === 'confirmado') {
                throw new Exception('Não é possível deletar um convidado confirmado', 409);
            }

            $deletar = $this->db->prepare('DELETE FROM convidado WHERE email = :email');

            $deletar->execute([
                ':email' => $emailConvidado
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Convidado deletado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'parent row')) {
                throw new Exception('Impossível deletar um convidado referenciado', 409);
            }

            throw new Exception('Erro ao tentar deletar convidado', 500);
        }
    }
}
