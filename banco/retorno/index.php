<?php
// Arquivo: retorno/index.php

require_once(__DIR__ . '/../conexao.php');

// Recebe o JSON do corpo da requisição
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Para registrar no log (opcional para debug)
file_put_contents('log_webhook.txt', $input . PHP_EOL, FILE_APPEND);

// Verifica se é uma notificação válida
if (!isset($data['notification'])) {
    http_response_code(400);
    exit('Notificação inválida');
}

$notificationToken = $data['notification'];

// Configurações da API Efí
require_once(__DIR__ . '/../caminho/para/vendor/autoload.php'); // ajuste o caminho se necessário

use Efi\EfiPay;
use Efi\Exception\EfiException;

$options = [
    "clientId" => "Client_Id_ef94544fb1f7e996c25991e60406eb0a938195bb",
    "clientSecret" => "Client_Secret_bfd16067d196cd392ec3a06562fae10ca8b14ea6",
    "sandbox" => true,
];

// Consulta os detalhes da notificação
$params = ["id" => $notificationToken];

try {
    $api = new EfiPay($options);
    $response = $api->getNotification($params);

    if (isset($response['data']) && is_array($response['data'])) {
        foreach ($response['data'] as $charge) {
            $status = $charge['status']['current'];
            $custom_id = $charge['custom_id']; // Aqui está o identificador que você definiu

            if ($status === 'paid') {
                // Atualize o status no banco de dados
                $stmt = $conexao->prepare("UPDATE pedidos SET status = 'pago', data_pagamento = NOW() WHERE codigo_pedido = ?");
                $stmt->execute([$custom_id]);

                // Você pode também enviar e-mail, gerar nota fiscal, etc.
            }
        }
    }

    http_response_code(200); // Retorna sucesso para o Efí
} catch (EfiException $e) {
    file_put_contents('log_erro.txt', $e->getMessage(), FILE_APPEND);
    http_response_code(500);
    echo 'Erro na consulta da notificação.';
} catch (Exception $e) {
    http_response_code(500);
    echo 'Erro geral: ' . $e->getMessage();
}