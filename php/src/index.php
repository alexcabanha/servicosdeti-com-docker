<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/api.php';

$resultado = apiRequest(
    'GET',
    'http://traefik/api/tickets'
);

$tickets = [];

$erro = '';

if ($resultado['success']) {

    $tickets = $resultado['data'] ?? [];

} else {

    $erro = 'Não foi possível carregar os chamados.';

}

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$perfilUsuario = $_SESSION['usuario_perfil'] ?? 'tecnico';

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ServiceTI - Chamados</title>

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

        .usuario span {
            font-size: 14px;
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
            max-width: 1200px;
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

        .novo {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .erro {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            text-align: left;
            padding: 13px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 13px;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .aberto {
            background: #fef3c7;
            color: #92400e;
        }

        .andamento {
            background: #dbeafe;
            color: #1e40af;
        }

        .fechado {
            background: #dcfce7;
            color: #166534;
        }

        .prioridade {
            font-weight: bold;
        }

        .alta {
            color: #dc2626;
        }

        .media {
            color: #d97706;
        }

        .baixa {
            color: #16a34a;
        }

        .link {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .vazio {
            padding: 40px;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 800px) {

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px;
            }

            .container {
                padding: 0 10px;
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

        <h2>Chamados</h2>

        <a
            class="novo"
            href="/novo.php"
        >
            + Novo chamado
        </a>

    </div>

    <?php if ($erro): ?>

        <div class="erro">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php elseif (empty($tickets)): ?>

        <div class="card">

            <div class="vazio">
                Nenhum chamado encontrado.
            </div>

        </div>

    <?php else: ?>

        <div class="card">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Título</th>
                        <th>Solicitante</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                        <th></th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($tickets as $ticket): ?>

                    <tr>

                        <td>
                            #<?= (int) $ticket['id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($ticket['titulo']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($ticket['solicitante']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $ticket['responsavel'] ?: 'Não atribuído'
                            ) ?>
                        </td>

                        <td>

                            <span
                                class="status <?= htmlspecialchars(
                                    $ticket['status']
                                ) ?>"
                            >
                                <?= htmlspecialchars(
                                    ucfirst($ticket['status'])
                                ) ?>
                            </span>

                        </td>

                        <td>

                            <span
                                class="prioridade <?= htmlspecialchars(
                                    $ticket['prioridade']
                                ) ?>"
                            >
                                <?= htmlspecialchars(
                                    ucfirst($ticket['prioridade'])
                                ) ?>
                            </span>

                        </td>

                        <td>

                            <a
                                class="link"
                                href="/chamado.php?id=<?= (int) $ticket['id'] ?>"
                            >
                                Abrir
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

</body>

</html>