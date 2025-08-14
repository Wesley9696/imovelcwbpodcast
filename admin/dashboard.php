<?php
session_start();

// Verifica se o usuário está logado. Se não estiver, redireciona para a página de login.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Credenciais do banco de dados (as mesmas dos outros arquivos)
$servername = "bdnewslatter.mysql.dbaas.com.br";
$username = "bdnewslatter";
$password = "IMOVELcwb#64"; // <-- SUBSTITUA AQUI PELA SUA SENHA DO BANCO DE DADOS
$dbname = "bdnewslatter";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Consulta para buscar os e-mails (usando a coluna correta "subscribed_at")
$sql = "SELECT id, email, subscribed_at FROM subscribers ORDER BY subscribed_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #333;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            background-image: linear-gradient(to right, #e50914, #ff4800ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #e50914; /* Cor fallback para navegadores que não suportam */
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #555;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #444;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #3b3b3b;
        }
        .links-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px; /* Adiciona espaçamento entre os botões */
            margin-bottom: 20px;
        }
        .logout-link {
            color: #ff3b3f; 
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }
        .logout-link:hover {
            color: #f0f0f0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 20px;
            background-color:  #1E90FF; /* Gradiente inicial vermelho */
            color: #fff;
            text-decoration: none; /* Remove sublinhado do link */
            font-size: 16px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-image 0.3s ease; /* Transição para o gradiente */
        }
        .button:hover {
            background-color: #00BFFF; /* Gradiente verde no hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Inscritos na Newsletter</h1>
        <div class="links-container">
            <a href="send_newsletter.php" class="button">Enviar Newsletter</a>
            <a href="logout.php" class="logout-link">Sair</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>E-mail</th>
                    <th>Data de Inscrição</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["id"]. "</td><td>" . $row["email"]. "</td><td>" . $row["subscribed_at"]. "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Nenhum inscrito encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>