<?php

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
    "clientId" => "Client_Id_18304e582b8428a038875d1502e5b4a8cd50158b",
    "clientSecret" => "Client_Secret_87d1082fb7e7deb32f7f3bfed1fee9086024ca2f",
    "sandbox" => false,
    "timeout" => 60,
];

$items = [
	[
		"name" => "Cat nip",
		"amount" => 1,
		"value" => 570
	]
];


$metadata = [
	"custom_id" => "Order_00001",
	"notification_url" => "https://maykonsilveira.com.br/retorno/" //transação que identifica a compra
];

$customer = [
	"name" => "Gorbadoc Oldbuck",
	"cpf" => "44762066036",
	// "email" => "",
	// "phone_number" => "",
	// "birth" => "",
	// "juridical_person" => [
	// 	"corporate_name" => "Nome da Empresa",
	// 	"cnpj" => "99794567000144"
	// ],
	// "address" => [
	// 	"street" => "",
	// 	"number" => "",
	// 	"neighborhood" => "",
	// 	"zipcode" => "",
	// 	"city" => "",
	// 	"complement" => "",
	// 	"state" => ""
	// ],
];


$configurations = [
	"fine" => 200,
	"interest" => 33
];

$bankingBillet = [
	"expire_at" => "2030-01-01",
	"message" => "A Achei Pet Pet Shop agradece a sua compra!",
	"customer" => $customer,
	"configurations" => $configurations
];


$payment = [
	"banking_billet" => $bankingBillet,
];

$body = [
	"items" => $items,
	"metadata" => $metadata,
	"payment" => $payment
];

try {
	$api = new EfiPay($options);
	$response = $api->createOneStepCharge($params = [], $body);

	if (isset($options["responseHeaders"]) && $options["responseHeaders"]) {
		print_r("<pre>" . json_encode($response->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
		print_r("<pre>" . json_encode($response->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
	} else {
		print_r("<pre>" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
	}
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
