<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gerador de Boleto</title>
</head>
<body>
	<section>
		
		<form action="gerador.php" method="post">
			<input type="text" name="produto" placeholder="Produto" required><br>
			<input type="number" name="valor" placeholder="Valor" required><br>
			<input type="number" name="qtd" placeholder="QTD" required><br>
			<input type="text" name="nome" placeholder="Nome Completo" required><br>
			<input type="text" name="cpf" placeholder="Digite o seu CPF" required><br>
			<input type="date" name="data" placeholder="Data" required><br>
			<button type="submit" name="sendMs">Gerar Boleto</button>
		</form>
	</section>
	
</body>
</html>