<?php
// Linhas para exibir todos os erros do PHP na tela
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verifica se o usuário está logado. Se não estiver, redireciona para a página de login.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Inclui os arquivos da biblioteca PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Credenciais do banco de dados
$servername = "bdnewslatter.mysql.dbaas.com.br";
$username = "bdnewslatter";
$password = "IMOVELcwb#64";
$dbname = "bdnewslatter";

// Conecta-se ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Lógica de envio de e-mail com PHPMailer
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $body = nl2br($_POST['body']);
    $sent_count = 0;
    
    // Configuração do PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Configurações do Servidor
        $mail->isSMTP();
        $mail->Host = 'email-ssl.com.br';
        $mail->SMTPAuth = true;
        $mail->Username = 'contato@imovelcwbpodcast.com.br';
        $mail->Password = 'AXXE645802#pod';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('contato@imovelcwbpodcast.com.br', 'Imovel CWB Podcast');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->CharSet = 'UTF-8';

        // Lidar com o arquivo anexo
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
            $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
        }
        
        // --- CÓDIGO FINAL: ENVIAR PARA TODOS OS EMAILS DO BANCO ---
        $sql = "SELECT email FROM subscribers";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $mail->addAddress($row['email']);
            }
            
            $mail->send();
            
            $_SESSION['message'] = "Newsletter enviada com sucesso para todos os inscritos!";
        } else {
            $_SESSION['message'] = "Nenhum inscrito encontrado para enviar a newsletter.";
        }

    } catch (Exception $e) {
        $_SESSION['message'] = "Erro ao enviar a newsletter: " . $mail->ErrorInfo;
    }

    header("Location: send_newsletter.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            background-image: linear-gradient(to right, #e50914, #ff4800ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #e50914;
            text-align: center;
        }
        label {
            display: block;
            margin-top: 15px;
            font-size: 16px;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #444;
            color: #f0f0f0;
            box-sizing: border-box;
        }
        textarea {
            height: 200px;
            resize: vertical;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 20px;
            background-image: linear-gradient(to right, #e50914, #ff4800ff);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background-image: linear-gradient(to right, #00BFFF, #1E90FF);
        }
        .logout-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #e50914;
            text-decoration: none;
            font-size: 14px;
        }
        .message-container {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #4CAF50;
            color: white;
        }
        .error {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Enviar Newsletter</h1>
        <?php
        if (isset($_SESSION['message'])) {
            $class = strpos($_SESSION['message'], 'Erro') !== false || strpos($_SESSION['message'], 'Falha') !== false ? 'error' : 'success';
            echo '<div class="message-container ' . $class . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <form action="send_newsletter.php" method="POST" enctype="multipart/form-data">
            <label for="subject">Assunto:</label>
            <input type="text" id="subject" name="subject" required>

            <label for="body">Corpo do E-mail (HTML):</label>
            <textarea id="body" name="body" required></textarea>

            <label for="attachment">Anexar Imagem ou Arquivo:</label>
            <input type="file" id="attachment" name="attachment">

            <button type="submit">Enviar para todos os inscritos</button>
        </form>
    </div>
    <a href="logout.php" class="logout-link">Sair</a>
</body>
</html>