<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/api.php';

$ticketId = (int) ($_GET['id'] ?? 0);

if ($ticketId <= 0) {
    header('Location: /');
    exit;
}

$erro = '';
$sucesso = '';

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$perfilUsuario = $_SESSION['usuario_perfil'] ?? 'tecnico';


/*
|--------------------------------------------------------------------------
| Atualização do chamado
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Atualizar
    |--------------------------------------------------------------------------
    */

    if ($acao === 'atualizar') {

        $dados = [];

        if (isset($_POST['titulo'])) {
            $dados['titulo'] = trim($_POST['titulo']);
        }

        if (isset($_POST['descricao'])) {
            $dados['descricao'] = trim($_POST['descricao']);
        }

        if (isset($_POST['status'])) {
            $dados['status'] = trim($_POST['status']);
        }

        if (isset($_POST['prioridade'])) {
            $dados['prioridade'] = trim($_POST['prioridade']);
        }

        if (isset($_POST['responsavel'])) {
            $dados['responsavel'] = trim($_POST['responsavel']);

            if ($dados['responsavel'] === '') {
                $dados['responsavel'] = null;
            }
        }

        $resultado = apiRequest(
            'PUT',
            'http://traefik/api/tickets/' . $ticketId,
            $dados
        );

        if ($resultado['success']) {

            $sucesso = 'Chamado atualizado com sucesso.';

        } else {

            $erro = 'Não foi possível atualizar o chamado.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Excluir
    |--------------------------------------------------------------------------
    */

    if ($acao === 'excluir') {

        $resultado = apiRequest(
            'DELETE',
            'http://traefik/api/tickets/' . $ticketId
        );

        if ($resultado['success']) {

            header('Location: /');
            exit;

        } else {

            $erro = 'Não foi possível excluir o chamado.';
        }
    }
}


/*
|--------------------------------------------------------------------------
| Buscar chamado
|--------------------------------------------------------------------------
*/

$resultado = apiRequest(
    'GET',
    'http://traefik/api/tickets/' . $ticketId
);

if (!$resultado['success']) {

    $erro = 'Chamado não encontrado.';
    $ticket = null;

} else {

    $ticket = $resultado['data'];
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

    <title>
        Chamado #<?= $ticketId ?> - ServiceTI
    </title>

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
            max-width: 1000px;
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
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .campo {
            margin-bottom: 18px;
        }

        .campo-full {
            grid-column: 1 / -1;
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
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .informacao {
            padding: 11px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .botoes {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        button {
            padding: 11px 18px;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-salvar {
            background: #2563eb;
        }

        .btn-excluir {
            background: #dc2626;
        }

        .erro {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .sucesso {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .descricao {
            white-space: pre-wrap;
            line-height: 1.6;
        }

        @media (max-width: 700px) {

            .grid {
                grid-template-columns: 1fr;
            }

            .campo-full {
                grid-column: auto;
            }

            header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

        }

    </style>

</head>

<body>

<header>

    <h1>ServiceTI</h1>

    <div class="usuario">

        <span>
            <?= htmlspecialchars($nomeUsuario) ?>
            (<?= htmlspecialchars($perfilUsuario) ?>)
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

        <h2>
            Chamado #<?= $ticketId ?>
        </h2>

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


    <?php if ($sucesso): ?>

        <div class="sucesso">
            <?= htmlspecialchars($sucesso) ?>
        </div>

    <?php endif; ?>


    <?php if ($ticket): ?>

        <form method="POST">

            <input
                type="hidden"
                name="acao"
                value="atualizar"
            >

            <div class="card">

                <div class="grid">

                    <div class="campo">

                        <label>
                            ID
                        </label>

                        <div class="informacao">
                            #<?= (int) $ticket['id'] ?>
                        </div>

                    </div>


                    <div class="campo">

                        <label>
                            Criado em
                        </label>

                        <div class="informacao">
                            <?= htmlspecialchars(
                                $ticket['criado_em']
                            ) ?>
                        </div>

                    </div>


                    <div class="campo campo-full">

                        <label for="titulo">
                            Título
                        </label>

                        <input
                            type="text"
                            id="titulo"
                            name="titulo"
                            value="<?= htmlspecialchars(
                                $ticket['titulo']
                            ) ?>"
                            maxlength="150"
                            required
                        >

                    </div>


                    <div class="campo campo-full">

                        <label for="descricao">
                            Descrição
                        </label>

                        <textarea
                            id="descricao"
                            name="descricao"
                            required
                        ><?= htmlspecialchars(
                            $ticket['descricao']
                        ) ?></textarea>

                    </div>


                    <div class="campo">

                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >

                            <option
                                value="aberto"
                                <?= (
                                    $ticket['status'] === 'aberto'
                                ) ? 'selected' : '' ?>
                            >
                                Aberto
                            </option>

                            <option
                                value="andamento"
                                <?= (
                                    $ticket['status'] === 'andamento'
                                ) ? 'selected' : '' ?>
                            >
                                Em andamento
                            </option>

                            <option
                                value="fechado"
                                <?= (
                                    $ticket['status'] === 'fechado'
                                ) ? 'selected' : '' ?>
                            >
                                Fechado
                            </option>

                        </select>

                    </div>


                    <div class="campo">

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
                                    $ticket['prioridade'] === 'baixa'
                                ) ? 'selected' : '' ?>
                            >
                                Baixa
                            </option>

                            <option
                                value="media"
                                <?= (
                                    $ticket['prioridade'] === 'media'
                                ) ? 'selected' : '' ?>
                            >
                                Média
                            </option>

                            <option
                                value="alta"
                                <?= (
                                    $ticket['prioridade'] === 'alta'
                                ) ? 'selected' : '' ?>
                            >
                                Alta
                            </option>

                        </select>

                    </div>


                    <div class="campo">

                        <label for="solicitante">
                            Solicitante
                        </label>

                        <div class="informacao">

                            <?= htmlspecialchars(
                                $ticket['solicitante']
                            ) ?>

                        </div>

                    </div>


                    <div class="campo">

                        <label for="responsavel">
                            Responsável
                        </label>

                        <input
                            type="text"
                            id="responsavel"
                            name="responsavel"
                            value="<?= htmlspecialchars(
                                $ticket['responsavel'] ?? ''
                            ) ?>"
                            maxlength="150"
                            placeholder="Nome do técnico"
                        >

                    </div>

                </div>


                <div class="botoes">

                    <button
                        type="submit"
                        class="btn-salvar"
                    >
                        Salvar alterações
                    </button>

                </div>

            </div>

        </form>


        <div class="card">

            <h3>
                Zona de perigo
            </h3>

            <p>
                A exclusão remove permanentemente este chamado.
            </p>

            <form
                method="POST"
                onsubmit="return confirm('Tem certeza que deseja excluir este chamado?');"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="excluir"
                >

                <button
                    type="submit"
                    class="btn-excluir"
                >
                    Excluir chamado
                </button>

            </form>

        </div>

    <?php endif; ?>

</div>

</body>

</html>