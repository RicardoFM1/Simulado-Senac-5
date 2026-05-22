<?php

use Firebase\JWT\JWT;

require_once __DIR__ . "/../../Connection/db.php";

class UsuarioService
{
    protected $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function buscarUsuarioPorEmail($emailUsuario)
    {
        if (empty($emailUsuario)) {
            throw new Exception('Dados inválidos', 400);
        }

        $buscar = $this->db->prepare('SELECT * FROM usuario WHERE email = :email');
        $buscar->execute([
            ':email' => $emailUsuario
        ]);

        $usuario = $buscar->fetch();

        if (empty($usuario)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Usuário não encontrado',
                'codigo' => '404'
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $usuario
        ];
    }


    public function listarUsuarios()
    {
        $buscar = $this->db->query('SELECT nome, email, cpf, cargo FROM usuario');

        $buscar->execute();

        $usuarios = $buscar->fetchAll();

        return [
            'sucesso' => true,
            'dados' => $usuarios,
            'total' => count($usuarios)
        ];
    }

    public function criarUsuario($usuarioDados)
    {
        try {

            $usuarioDados['cpf'] = preg_replace('/\D/', '', $usuarioDados['cpf']);

            $criar = $this->db->prepare('INSERT INTO usuario (nome, email, cpf, senha, cargo) 
    VALUES (:nome, :email, :cpf, :senha, :cargo)');

            $criar->execute([
                ':nome' => $usuarioDados['nome'],
                ':email' => $usuarioDados['email'],
                ':cpf' => $usuarioDados['cpf'],
                ':senha' => password_hash($usuarioDados['senha'], PASSWORD_DEFAULT),
                ':cargo' => $usuarioDados['cargo']
            ]);


            return [
                'sucesso' => true,
                'mensagem' => 'Usuário criado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'email')) {
                throw new Exception('Email já em uso', 409);
            }
            if (str_contains($e->getMessage(), 'cpf')) {
                throw new Exception('Cpf já em uso', 409);
            }

            throw new Exception('Erro ao tentar criar usuario', 500);
        }
    }


    public function fazerLogin($usuarioDados)
    {
        try {
            $usuario = $this->buscarUsuarioPorEmail($usuarioDados['email']);

            if ($usuario['sucesso'] === false) {
                throw new Exception('Credenciais inválidas', 404);
            }

            $senhaCorreta = password_verify($usuarioDados['senha'], $usuario['dados']['senha']);

            if (!$senhaCorreta) {
                throw new Exception('Credenciais inválidas', 404);
            }

            $payload = [
                'exp' => time() + 10000,
                'dados' => [
                    'id_usuario' => $usuario['dados']['id_usuario'],
                    'email_usuario' => $usuario['dados']['email'],
                    'cargo_usuario' => $usuario['dados']['cargo']
                ]
            ];

            $jwt = JWT::encode($payload, $_ENV['JWT_SECRET_KEY'], 'HS256');


            return [
                'sucesso' => true,
                'mensagem' => 'Usuário logado com sucesso',
                'token' => $jwt
            ];
        } catch (PDOException $e) {
            throw new Exception('Erro ao tentar fazer login', 500);
        }
    }


    public function atualizarUsuario($usuarioDados, $emailUsuario)
    {
        try {
            $usuario = $this->buscarUsuarioPorEmail($emailUsuario);

            if ($usuario['sucesso'] === false) {
                throw new Exception($usuario['mensagem'], $usuario['codigo']);
            }
            $usuarioDados['cpf'] = preg_replace('/\D/', '', $usuarioDados['cpf']);

            $atualizar = $this->db->prepare('UPDATE usuario SET nome = :nome, email = :email, cpf = :cpf, 
            senha = :senha, cargo = :cargo WHERE email = :email_usuario');

            $atualizar->execute([
                ':nome' => $usuarioDados['nome'],
                ':email' => $usuarioDados['email'],
                ':cpf' => $usuarioDados['cpf'],
                ':senha' => password_hash($usuarioDados['senha'], PASSWORD_DEFAULT),
                ':cargo' => $usuarioDados['cargo'],
                ':email_usuario' => $emailUsuario
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Usuário atualizado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'email')) {
                throw new Exception('Email já em uso', 409);
            }
            if (str_contains($e->getMessage(), 'cpf')) {
                throw new Exception('Cpf já em uso', 409);
            }

            throw new Exception('Erro ao tentar atualizar usuario', 500);
        }
    }


    public function deletarUsuario($emailUsuario)
    {
        try {

            $usuario = $this->buscarUsuarioPorEmail($emailUsuario);

            if ($usuario['sucesso'] === false) {
                 throw new Exception($usuario['mensagem'], $usuario['codigo']);
            }

            $deletar = $this->db->prepare('DELETE FROM usuario WHERE email = :email');
            
            $deletar->execute([
                ':email' => $emailUsuario
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Usuário deletado com sucesso'
            ];


        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'parent row')) {
                throw new Exception('Impossível deletar um usuário referenciado', 409);
            }

            throw new Exception('Erro ao tentar deletar usuário', 500);
        }
    }
}
