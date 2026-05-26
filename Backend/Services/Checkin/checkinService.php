<?php

require_once __DIR__ . "/../../Connection/db.php";

class CheckinService
{
    protected $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function buscarCheckinPorId($idCheckin)
    {
        if (empty($idCheckin)) {
            throw new Exception('Dados inválidos', 400);
        }

        $buscar = $this->db->prepare('SELECT * FROM checkin WHERE id_checkin = :id_checkin');
        $buscar->execute([
            ':id_checkin' => $idCheckin
        ]);

        $checkin = $buscar->fetch();

        if (empty($checkin)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Checkin não encontrado',
                'codigo' => 404
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $checkin
        ];
    }





    public function listarCheckins()
    {
        $buscar = $this->db->query('SELECT c.id_checkin, c.data_e_hora, u.nome as usuario_nome, u.cpf as usuario_cpf,
        co.nome as convidado_nome, co.cpf as convidado_cpf
         FROM checkin c INNER JOIN usuario u ON u.id_usuario = c.usuario_idusuario 
         INNER JOIN convidado co ON co.id_convidado = c.convidado_idconvidado
         ORDER BY id_checkin DESC');

        $buscar->execute();
        $resultado = [];

       while($row = $buscar->fetch()){
            $resultado[] = [
                'id_checkin' => $row['id_checkin'],
                'data_e_hora' => $row['data_e_hora'],
                'usuario' => [
                    'nome' => $row['usuario_nome'],
                    'cpf' => $row['usuario_cpf']
                ],
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

    public function criarCheckin($checkinDados, $jwt)
    {
        try {

            $buscarConvidado = $this->db->prepare('SELECT * FROM convidado WHERE id_convidado = :id_convidado');

            $buscarConvidado->execute([
                ':id_convidado' => $checkinDados['convidado_idconvidado']
            ]);

            $convidado = $buscarConvidado->fetch();

            if (empty($convidado)) {
                throw new Exception('Convidado não encontrado', 404);
            }

            if ($convidado['confirmacao'] === 'confirmado') {
                throw new Exception('Não é possível fazer checkin de um convidado já confirmado', 409);
            }

            if ($convidado['confirmacao'] === 'cancelado') {
                throw new Exception('Não é possível fazer checkin de um convidado cancelado', 409);
            }


            $dataFormatada = date('Y-m-d');

            $criar = $this->db->prepare('INSERT INTO checkin (usuario_idusuario, convidado_idconvidado, data_e_hora) 
    VALUES (:usuario_idusuario, :convidado_idconvidado, :data_e_hora)');

            $criar->execute([
                ':usuario_idusuario' => $jwt->dados->id_usuario,
                ':convidado_idconvidado' => $checkinDados['convidado_idconvidado'],
                ':data_e_hora' => $dataFormatada
            ]);

            $atualizarConvidado = $this->db->prepare('UPDATE convidado SET confirmacao = "confirmado" WHERE id_convidado = :id_convidado');

            $atualizarConvidado->execute([
                ':id_convidado' => $checkinDados['convidado_idconvidado']
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Checkin criado com sucesso'
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'convidado_idconvidado')) {
                throw new Exception('Convidado com checkin já realizado', 409);
            }
            if (str_contains($e->getMessage(), 'fk_checkin_usuario')) {
                throw new Exception('Usuário referenciado não encontrado', 404);
            }

            if (str_contains($e->getMessage(), 'fk_checkin_convidado')) {
                throw new Exception('Convidado referenciado não encontrado', 404);
            }

            throw new Exception('Erro ao tentar criar checkin', 500);
        }
    }
}
