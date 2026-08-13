#!/bin/bash

set -u

BACKUP_DIR="/backup/mysql"
LOG_DIR="/backup/logs"
CONTAINER="mysql"
DATABASE="servicoti"
DB_USER="servicoti"
RETENTION_DAYS=7

DATE=$(date +"%Y-%m-%d_%H-%M-%S")

BACKUP_FILE="${BACKUP_DIR}/${DATABASE}_${DATE}.sql.gz"
TEMP_FILE="${BACKUP_FILE}.tmp"
LOG_FILE="${LOG_DIR}/mysql-backup.log"

mkdir -p "$BACKUP_DIR"
mkdir -p "$LOG_DIR"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log "Iniciando backup do banco ${DATABASE}."

if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
    log "ERRO: container ${CONTAINER} não está em execução."
    exit 1
fi

if docker exec "$CONTAINER" sh -c \
    "mysqldump --no-tablespaces -u${DB_USER} -p\"\$MYSQL_PASSWORD\" ${DATABASE}" \
    | gzip > "$TEMP_FILE"; then

    if [ -s "$TEMP_FILE" ]; then

        mv "$TEMP_FILE" "$BACKUP_FILE"

        SIZE=$(du -h "$BACKUP_FILE" | cut -f1)

        log "Backup concluído: $(basename "$BACKUP_FILE") (${SIZE})."

    else

        rm -f "$TEMP_FILE"

        log "ERRO: backup gerado está vazio."
        exit 1

    fi

else

    rm -f "$TEMP_FILE"

    log "ERRO: mysqldump falhou."
    exit 1

fi

log "Removendo backups com mais de ${RETENTION_DAYS} dias."

find "$BACKUP_DIR" \
    -type f \
    -name "${DATABASE}_*.sql.gz" \
    -mtime +"$RETENTION_DAYS" \
    -delete

log "Backup finalizado com sucesso."

exit 0
