<?php

use Firebase\JWT\JWT;

require_once __DIR__ . "/../../Connection/db.php";


class AcompanhanteService
{
    protected $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function buscarAcompanhantePorEmail($emailAcompanhante)
    {
        if (empty($emailAcompanhante)) {
            throw new Exception('Dados inválidos', 400);
        }

        $buscar = $this->db->prepare('SELECT * FROM acompanhante WHERE email = :email');
        $buscar->execute([
            ':email' => $emailAcompanhante
        ]);

        $acompanhante = $buscar->fetch();

        if (empty($acompanhante)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Acompanhante não encontrado',
                'codigo' => 404
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $acompanhante
        ];
    }





    public function listarAcompanhantes()
    {
        $buscar = $this->db->query('SELECT a.id_acompanhante, a.nome as acompanhante_nome, a.sobrenome as
        acompanhante_sobrenome, a.email, a.cpf as acompanhante_cpf, a.idade, c.nome as convidado_nome,
        c.cpf as convidado_cpf FROM acompanhante a 
        INNER JOIN convidado c ON c.id_convidado = a.convidado_idconvidado
        ORDER BY id_acompanhante DESC');

        $buscar->execute();

        $resultado = [];

        while ($row = $buscar->fetch()) {
            $resultado[] = [
                'id_acompanhante' => $row['id_acompanhante'],
                'nome' => $row['acompanhante_nome'],
                'sobrenome' => $row['acompanhante_sobrenome'],
                'email' => $row['email'],
                'cpf' => $row['acompanhante_cpf'],
                'idade' => $row['idade'],
                'convidado' => [
                    'nome' => $row['convidado_nome'],
                    'cpf' => $row['convidado_cpf']
                ]
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $resultado,
            'total' => count($resultado)
        ];
    }

    public function criarAcompanhante($acompanhanteDados)
    {
        try {

            $acompanhanteDados['cpf'] = preg_replace('/\D/', '', $acompanhanteDados['cpf']);

            $criar = $this->db->prepare('INSERT INTO acompanhante (nome, sobrenome, email, cpf, idade, convidado_idconvidado) 
    VALUES (:nome, :sobrenome, :email, :cpf, :idade, :convidado_idconvidado)');

            $criar->execute([
                ':nome' => $acompanhanteDados['nome'],
                ':sobrenome' => $acompanhanteDados['sobrenome'],
                ':email' => $acompanhanteDados['email'],
                ':cpf' => $acompanhanteDados['cpf'],
                ':idade' => $acompanhanteDados['idade'],
                ':convidado_idconvidado' => $acompanhanteDados['convidado_idconvidado']

            ]);


            return [
                'sucesso' => true,
                'mensagem' => 'Acompanhante criado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'email')) {
                throw new Exception('Email já em uso', 409);
            }
            if (str_contains($e->getMessage(), 'cpf')) {
                throw new Exception('Cpf já em uso', 409);
            }

            if (str_contains($e->getMessage(), 'fk_acompanhante_convidado')) {
                throw new Exception('Convidado referenciado não encontrado', 404);
            }

            throw new Exception('Erro ao tentar criar acompanhante', 500);
        }
    }





    public function atualizarAcompanhante($acompanhanteDados, $emailAcompanhante)
    {
        try {
            $acompanhante = $this->buscarAcompanhantePorEmail($emailAcompanhante);

            if ($acompanhante['sucesso'] === false) {
                throw new Exception($acompanhante['mensagem'], $acompanhante['codigo']);
            }

            $acompanhante['cpf'] = preg_replace('/\D/', '', $acompanhante['cpf']);



            $atualizar = $this->db->prepare('UPDATE acompanhante SET nome = :nome, sobrenome = :sobrenome,
            email = :email, cpf = :cpf, idade = :idade, convidado_idconvidado = :convidado_idconvidado   
            WHERE email = :email_acompanhante');

            $atualizar->execute([
                ':nome' => $acompanhanteDados['nome'],
                ':sobrenome' => $acompanhanteDados['sobrenome'],
                ':email' => $acompanhanteDados['email'],
                ':cpf' => $acompanhanteDados['cpf'],
                ':idade' => $acompanhanteDados['idade'],
                ':convidado_idconvidado' => $acompanhanteDados['convidado_idconvidado'],
                ':email_acompanhante' => $emailAcompanhante
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Acompanhante atualizado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'email')) {
                throw new Exception('Email já em uso', 409);
            }
            if (str_contains($e->getMessage(), 'cpf')) {
                throw new Exception('Cpf já em uso', 409);
            }

            if (str_contains($e->getMessage(), 'fk_acompanhante_convidado')) {
                throw new Exception('Convidado referenciado não encontrado', 404);
            }

            throw new Exception('Erro ao tentar atualizar acompanhante', 500);
        }
    }


    public function deletarAcompanhante($emailAcompanhante)
    {
        try {

            $acompanhante = $this->buscarAcompanhantePorEmail($emailAcompanhante);



            if ($acompanhante['sucesso'] === false) {
                throw new Exception($acompanhante['mensagem'], $acompanhante['codigo']);
            }

            $deletar = $this->db->prepare('DELETE FROM acompanhante WHERE email = :email');

            $deletar->execute([
                ':email' => $emailAcompanhante
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Acompanhante deletado com sucesso'
            ];
        } catch (PDOException $e) {

            throw new Exception('Erro ao tentar deletar acompanhante', 500);
        }
    }
}
