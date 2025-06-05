<?php
session_start();
require_once(__DIR__ . '/../conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    die("Usuário não está logado.");
}

// Obtém o nome do usuário logado
$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt = $conexao->prepare("SELECT nome, cpf FROM usuarios WHERE id_usuario = :id");
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuário não encontrado.");
    }

    $nome_usuario = $usuario['nome'];
	$cpf_usuario = preg_replace('/[^0-9]/', '', $usuario['cpf']); // limpa o CPF


} catch (PDOException $e) {
    die("Erro ao consultar o banco de dados: " . $e->getMessage());
}


 $dadosCarrinhoJSON = $_POST['dados_carrinho'] ?? null;

if (!$dadosCarrinhoJSON) {
    die("Erro: Nenhum dado de carrinho recebido.");
}

$dadosCarrinho = json_decode($dadosCarrinhoJSON, true);

// Verifica se a decodificação funcionou
if (!is_array($dadosCarrinho)) {
    die("Erro: dados do carrinho estão em formato inválido.");
}

/**
 * Detailed endpoint documentation
 * https://dev.efipay.com.br/docs/api-cobrancas/boleto/#criação-de-boleto-bolix-em-one-step-um-passo
 */

$autoload = realpath(__DIR__ . "/vendor/autoload.php");
if (!file_exists($autoload)) {
	die("Autoload file not found or on path <code>$autoload</code>.");
}
require_once $autoload;

use Efi\Exception\EfiException;
use Efi\EfiPay;


$options = [
    "clientId" => "Client_Id_ef94544fb1f7e996c25991e60406eb0a938195bb",
    "clientSecret" => "Client_Secret_bfd16067d196cd392ec3a06562fae10ca8b14ea6",
    "sandbox" => true, //ambiente de homologação
    "timeout" => 60,
];

$items = [];

foreach ($dadosCarrinho as $item) {
    if (isset($item['nome'], $item['quantidade'], $item['preco']) && !empty($item['nome'])) {
        $items[] = [
            "name" => $item['nome'],
            "amount" => (int) $item['quantidade'],
            "value" => (int) ($item['preco'] * 100) // converter para centavos
        ];
    }
}

if (empty($items)) {
    die("Erro: Nenhum item válido no carrinho.");
}


$metadata = [
	"custom_id" => "Order_00001",
	"notification_url" => "https://alesandrogomes.com.br" //transação que identifica a compra
];


$customer = [
    "name" => $nome_usuario,
    "email" => "cliente@email.com",
    "cpf" => $cpf_usuario,
    "birth" => "1980-05-10",
    "phone_number" => "11999999999"
];


$configurations = [
	"fine" => 200,
	"interest" => 33
];

$bankingBillet = [
	"expire_at" => "2025-06-15",
	"message" => "A Achei Pet Pet Shop agradece a sua compra!",
	"customer" => $customer,
	"configurations" => $configurations
];


$payment = [
    "banking_billet" => [
        "expire_at" => date('Y-m-d', strtotime('+3 days')),
        "customer" => $customer
    ]
];

$body = [
	"items" => $items,
	"metadata" => $metadata,
	"payment" => $payment
];

try {
	$api = new EfiPay($options);
	$response = $api->createOneStepCharge($params = [], $body);

	//var_dump($response['data']['billet_link']);
	//echo "<img src='{$response['data']['pix']['qrcode_image']}' width='250'>";
	echo "<script>window.open('{$response['data']['link']}', '_blank');</script>";



//	if (isset($options["responseHeaders"]) && $options["responseHeaders"]) {
//		print_r("<pre>" . json_encode($response->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
//		print_r("<pre>" . json_encode($response->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
//	} else {
//		print_r("<pre>" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
//	}
} catch (EfiException $e) {
	print_r($e->code . "<br>");
	print_r($e->error . "<br>");
	print_r($e->errorDescription) . "<br>";
	if (isset($options["responseHeaders"]) && $options["responseHeaders"]) {
		print_r("<pre>" . json_encode($e->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
	}
} catch (Exception $e) {
	print_r($e->getMessage());
}
?>
<meta http-equiv="refresh" content="1; URL='http://localhost/acheipet_git/carrinho.php'" />
<!--Define o redirecionamento, tempo e URL-->
<!--<a href="http://localhost/acheipet_git/carrinho.php" class="btn btn-secondary mt-3">Voltar ao Carrinho</a>-->