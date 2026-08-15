<?php

session_start();

require_once __DIR__ . '/api.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: /');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {

        $erro = 'Informe o e-mail e a senha.';

    } else {

        $dados = [
            'email' => $email,
            'senha' => $senha
        ];

        $resultado = apiRequest(
            'POST',
            'http://traefik/api/auth/login',
            $dados
        );

        if (!$resultado['success']) {

            $erro = 'E-mail ou senha inválidos.';

        } else {

            $usuario = $resultado['data'];

            if (
                !isset($usuario['id']) ||
                !isset($usuario['senha_hash'])
            ) {

                $erro = 'Resposta inválida da API.';

            } elseif (
                !password_verify(
                    $senha,
                    $usuario['senha_hash']
                )
            ) {

                $erro = 'E-mail ou senha inválidos.';

            } else {

                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];

                header('Location: /');
                exit;
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Lex TI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        h1 {
            margin-top: 0;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="login">

    <h1>Lex Corp</h1>

    <div class="subtitle">
        Sistema de Chamados da Lex Corp - Na dúvida? Abre um chamado!
    </div>

    <?php if ($erro): ?>

        <div class="error">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label for="email">
            E-mail
        </label>

        <input
            type="email"
            id="email"
            name="email"
            autocomplete="username"
            required
        >

        <label for="senha">
            Senha
        </label>

        <input
            type="password"
            id="senha"
            name="senha"
            autocomplete="current-password"
            required
        >

        <button type="submit">
            Entrar
        </button>

    </form>

</div>

</body>

</html>