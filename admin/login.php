<?php
session_start();

// Dados de acesso para o administrador
$admin_username = "admin";
$admin_password = "TWK645802"; // <-- SUBSTITUA AQUI PELA SENHA QUE VOCÊ QUER USAR

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifica as credenciais
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['logged_in'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Usuário ou senha inválidos.";
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #333;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 300px;
            text-align: center;
        }
        .login-container img {
            max-width: 200px; /* Limita o tamanho máximo da imagem */
            height: auto; /* Mantém a proporção da imagem */
            margin-bottom: 20px; /* Espaço abaixo da imagem */
        }
        h2 {
            margin-bottom: 20px;
            /* Aplicando o gradiente ao texto usando background-clip */
            background-image: linear-gradient(to right, #e50914, #ff4800ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #e50914; /* Cor fallback para navegadores que não suportam */
        }
        input[type="text"],
        input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #555;
            border-radius: 15px;
            background-color: #444;
            color: #f0f0f0;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 15px;
            /* Aplicando o gradiente no botão */
            background-image: linear-gradient(to right, #e50914, #ff4800ff);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: opacity 0.3s ease; /* Transição para o hover */
        }
        button:hover {
            opacity: 0.8; /* Efeito de hover com opacidade */
        }
        .error-message {
            color: #ff3b3f;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img alt="Logo da Sua Empresa" src="/img/logo-imovelcwb-icon.png" />
        <h2>PAINEL NEWSLATTER</h2>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Usuário" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
        <?php
        if (isset($_SESSION['login_error'])) {
            echo '<p class="error-message">' . $_SESSION['login_error'] . '</p>';
            unset($_SESSION['login_error']);
        }
        ?>
    </div>
</body>
</html>