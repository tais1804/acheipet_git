<?php

ob_start();
require('./sheep_core/config.php');

require('vendor/autoload.php');

$gerar = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (isset($gerar['gerarBoleto'])) {
    unset($gerar['gerarBoleto']);

    $ler = new Ler();
    $ler->Leitura('usuarios', "WHERE id = :id", "id={$gerar['id']}");
    if ($ler->getResultado()) {
        foreach ($ler->getResultado() as $cliente) {
            $cliente = (object) $cliente;
        }
    }

    $vencimento = date('d/m/Y', strtotime($gerar['data']));

    // echo "Nome: {$cliente->nome} data: {$vencimento} plano: {$gerar['plano']} valor: {$gerar['valor']} ";

{
  "items": [
    {
      "name": "Meu Produto",
      "value": 5990,
      "amount": 1
    }
  ],
  "payment": {
    "banking_billet": {
      "customer": {
        "name": "Gorbadoc Oldbuck",
        "cpf": "94271564656",
        "email": "email_do_cliente@servidor.com.br",
        "phone_number": "5144916523",
        "address": {
          "street": "Avenida Juscelino Kubitschek",
          "number": "909",
          "neighborhood": "Bauxita",
          "zipcode": "35400000",
          "city": "Ouro Preto",
          "complement": "",
          "state": "MG"
        }
      },
      "expire_at": "2023-12-15",
      "configurations": {
        "fine": 200,
        "interest": 33
      },
      "message": "Usando o atributo message, este conteúdo é exibido no campo OBSERVAÇÃO da cobrança emitida via API 
       e também no campo OBSERVAÇÃO DO VENDEDOR nos e-mails de cobrança enviados ao cliente 
       É possível utilizar até 4 linhas de conteúdo, com no máximo 100 caracteres por linha 
       Essa mensagem poderá ser vista nos e-mails relacionados à cobrança, no boleto ou carnê"
    }
  }
}

}

?>