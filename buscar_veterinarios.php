<?php
include "conexao.php";

header('Content-Type: application/json'); 

$latitude = filter_input(INPUT_GET, 'latitude', FILTER_VALIDATE_FLOAT);
$longitude = filter_input(INPUT_GET, 'longitude', FILTER_VALIDATE_FLOAT);

$raio = filter_input(INPUT_GET, 'raio', FILTER_VALIDATE_INT);
if ($raio === false || $raio <= 0) {
    $raio = 25; 
}

$response = [
    'success' => false,
    'message' => '',
    'veterinarios' => []
];

if ($latitude === false || $longitude === false) {
    $response['message'] = "Latitude ou Longitude inválidas.";
    echo json_encode($response);
    exit();
}

try {
    $stmt = $conexao->prepare("
        SELECT
            id_veterinario, -- Assumindo que você tem um ID para o veterinário
            nome_clinica,
            endereco,
            telefone,
            latitude,
            longitude,
            ( 6371 * acos(
                cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) )
                + sin( radians(?) ) * sin( radians( latitude ) )
            ) ) AS distance
        FROM veterinarios
        HAVING distance < ?
        ORDER BY distance
    ");
    $stmt->execute([$latitude, $longitude, $latitude, $raio]);
    $veterinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($veterinarios) > 0) {
        $response['success'] = true;
        $response['message'] = "Veterinários encontrados com sucesso.";
        $response['veterinarios'] = $veterinarios;
    } else {
        $response['success'] = true; 
        $response['message'] = "Nenhum veterinário encontrado próximo a você no raio de " . $raio . " km.";
    }

} catch (PDOException $e) {
    $response['message'] = "Erro ao buscar veterinários: " . $e->getMessage();
    error_log("Erro PDO em buscar_veterinarios.php: " . $e->getMessage());
}

echo json_encode($response);
exit();
?>
