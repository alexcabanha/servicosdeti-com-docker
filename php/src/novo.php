<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/api.php';

$erro = '';

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $prioridade = trim($_POST['prioridade'] ?? 'media');

    if ($titulo === '') {

        $erro = 'Informe o título do chamado.';

    } elseif ($descricao === '') {

        $erro = 'Informe a descrição do chamado.';

    } else {

        $dados = [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'prioridade' => $prioridade,
            'solicitante' => $nomeUsuario,
            'responsavel' => null
        ];

        $resultado = apiRequest(
            'POST',
            'http://traefik/api/tickets',
            $dados
        );

        if ($resultado['success']) {

            header('Location: /');
            exit;

        } else {

            $erro = 'Não foi possível criar o chamado.';

        }
    }
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Novo Chamado - ServiceTI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #1e293b;
        }

        header {
            background: #1e293b;
            color: white;
            padding: 16px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 22px;
        }

        .usuario {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 13px;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .topo h2 {
            margin: 0;
        }

        .voltar {
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 11px;
            margin-bottom: 18px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .erro {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<header>

    <h1>ServiceTI</h1>

    <div class="usuario">

        <span>
            <?= htmlspecialchars($nomeUsuario) ?>
        </span>

        <a
            class="logout"
            href="/logout.php"
        >
            Sair
        </a>

    </div>

</header>

<div class="container">

    <div class="topo">

        <h2>Novo chamado</h2>

        <a
            class="voltar"
            href="/"
        >
            ← Voltar para chamados
        </a>

    </div>

    <?php if ($erro): ?>

        <div class="erro">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>

    <div class="card">

        <form method="POST">

            <label for="titulo">
                Título
            </label>

            <input
                type="text"
                id="titulo"
                name="titulo"
                maxlength="150"
                value="<?= htmlspecialchars(
                    $_POST['titulo'] ?? ''
                ) ?>"
                placeholder="Ex.: Computador não liga"
                required
            >

            <label for="descricao">
                Descrição
            </label>

            <textarea
                id="descricao"
                name="descricao"
                placeholder="Descreva o problema..."
                required
            ><?= htmlspecialchars(
                $_POST['descricao'] ?? ''
            ) ?></textarea>

            <label for="prioridade">
                Prioridade
            </label>

            <select
                id="prioridade"
                name="prioridade"
            >

                <option
                    value="baixa"
                    <?= (
                        ($_POST['prioridade'] ?? '') === 'baixa'
                    ) ? 'selected' : '' ?>
                >
                    Baixa
                </option>

                <option
                    value="media"
                    <?= (
                        ($_POST['prioridade'] ?? 'media') === 'media'
                    ) ? 'selected' : '' ?>
                >
                    Média
                </option>

                <option
                    value="alta"
                    <?= (
                        ($_POST['prioridade'] ?? '') === 'alta'
                    ) ? 'selected' : '' ?>
                >
                    Alta
                </option>

            </select>

            <button type="submit">
                Criar chamado
            </button>

        </form>

    </div>

</div>

</body>

</html>