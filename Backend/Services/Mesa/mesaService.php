<?php
date_default_timezone_set('America/Sao_Paulo');

use Firebase\JWT\JWT;

require_once __DIR__ . "/../../Connection/db.php";

class MesaService
{
    protected $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function buscarMesaPorId($idMesa)
    {
        if (empty($idMesa)) {
            throw new Exception('Dados inválidos', 400);
        }

        $buscar = $this->db->prepare('SELECT * FROM mesa WHERE id_mesa = :id_mesa');
        $buscar->execute([
            ':id_mesa' => $idMesa
        ]);

        $mesa = $buscar->fetch();

        if (empty($mesa)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Mesa não encontrada',
                'codigo' => '404'
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $mesa
        ];
    }


    public function listarMesas()
    {
        $buscar = $this->db->query('SELECT * FROM mesa');

        $buscar->execute();

        $mesas = $buscar->fetchAll();

        return [
            'sucesso' => true,
            'dados' => $mesas,
            'total' => count($mesas)
        ];
    }

    public function criarMesa($mesaDados)
    {
        try {

            $dataFormatada = date('Y-m-d');

            $criar = $this->db->prepare('INSERT INTO mesa (capacidade, restricao, data_e_hora) 
    VALUES (:capacidade, :restricao, :data_e_hora)');

            $criar->execute([
                ':capacidade' => $mesaDados['capacidade'],
                ':restricao' => $mesaDados['restricao'],
                ':data_e_hora' => $dataFormatada

            ]);


            return [
                'sucesso' => true,
                'mensagem' => 'Mesa criada com sucesso'
            ];
        } catch (PDOException $e) {
            throw new Exception('Erro ao tentar criar mesa', 500);
        }
    }




    public function atualizarMesa($mesaDados, $idMesa)
    {
        try {
            $mesa = $this->buscarMesaPorId($idMesa);

            if ($mesa['sucesso'] === false) {
                throw new Exception($mesa['mensagem'], $mesa['codigo']);
            }

            $dataFormatada = date('Y-m-d');


            $atualizar = $this->db->prepare('UPDATE mesa SET capacidade = :capacidade, restricao = :restricao, data_e_hora = :data_e_hora 
             WHERE id_mesa = :id_mesa');

            $atualizar->execute([
                ':capacidade' => $mesaDados['capacidade'],
                ':restricao' => $mesaDados['restricao'],
                ':data_e_hora' => $dataFormatada,
                ':id_mesa' => $idMesa
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Mesa atualizada com sucesso'
            ];
        } catch (PDOException $e) {

            throw new Exception('Erro ao tentar atualizar mesa', 500);
        }
    }


    public function deletarMesa($idMesa)
    {
        try {

            $mesa = $this->buscarMesaPorId($idMesa);

            if ($mesa['sucesso'] === false) {
                throw new Exception($mesa['mensagem'], $mesa['codigo']);
            }

            $deletar = $this->db->prepare('DELETE FROM mesa WHERE id_mesa = :id_mesa');

            $deletar->execute([
                ':id_mesa' => $idMesa
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Mesa deletada com sucesso'
            ];
        } catch (PDOException $e) {


            throw new Exception('Erro ao tentar deletar mesa', 500);
        }
    }
}
