<?php

session_start();

function isAuthenticated(): bool
{
    return isset($_SESSION['usuario_id']);
}

function requireLogin(): void
{
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit;
    }
}

function currentUser(): ?array
{
    if (!isAuthenticated()) {
        return null;
    }

    return [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'],
        'email' => $_SESSION['usuario_email'],
        'perfil' => $_SESSION['usuario_perfil']
    ];
}

function isAdmin(): bool
{
    return isset($_SESSION['usuario_perfil'])
        && $_SESSION['usuario_perfil'] === 'admin';
}

function requireAdmin(): void
{
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}