<?php
header('Content-Type: application/json');

$servername = "bdnewslatter.mysql.dbaas.com.br";
$username = "bdnewslatter";
$password = 'IMOVELcwb#64'; // <-- SUBSTITUA AQUI PELA SUA SENHA DO BANCO DE DADOS
$dbname = "bdnewslatter";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Erro de conexão: ' . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('O e-mail é inválido.');
        }

        // Usamos INSERT IGNORE para que o banco de dados ignore duplicatas
        $stmt = $conn->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)");
        if (!$stmt) {
            throw new Exception('Erro na preparação da consulta: ' . $conn->error);
        }

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            // Verifica se alguma linha foi afetada para saber se foi uma nova inserção
            if ($stmt->affected_rows > 0) {
                echo json_encode(['message' => 'Obrigado por se inscrever!']);
            } else {
                echo json_encode(['message' => 'Este e-mail já está cadastrado. Por favor, use outro.']);
            }
        } else {
            throw new Exception('Erro ao se inscrever: ' . $conn->error);
        }
        $stmt->close();
    } else {
        throw new Exception('O email é obrigatório.');
    }

} catch (Exception $e) {
    echo json_encode(['message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
exit();
?>