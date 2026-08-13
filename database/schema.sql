CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    status ENUM('aberto', 'em_atendimento', 'resolvido', 'cancelado') NOT NULL DEFAULT 'aberto',
    prioridade ENUM('baixa', 'media', 'alta', 'critica') NOT NULL DEFAULT 'media',
    solicitante VARCHAR(150) NOT NULL,
    responsavel VARCHAR(150) DEFAULT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tickets_prioridade ON tickets(prioridade);
CREATE INDEX idx_tickets_solicitante ON tickets(solicitante);
CREATE INDEX idx_tickets_criado_em ON tickets(criado_em);
