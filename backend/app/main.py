import os
import secrets
from typing import Optional

from fastapi import FastAPI, HTTPException, Header, Depends
from pydantic import BaseModel

from app.database import get_connection
from prometheus_fastapi_instrumentator import Instrumentator

app = FastAPI(
    title="ServiceTI API",
    version="1.0.0"
)

Instrumentator().instrument(app).expose(app)

API_INTERNAL_KEY = os.getenv("API_INTERNAL_KEY", "")


def require_internal_api_key(
    x_api_key: Optional[str] = Header(default=None)
):
    if not API_INTERNAL_KEY:
        raise HTTPException(
            status_code=500,
            detail="API_INTERNAL_KEY não configurada"
        )

    if not x_api_key or not secrets.compare_digest(
        x_api_key,
        API_INTERNAL_KEY
    ):
        raise HTTPException(
            status_code=401,
            detail="API key inválida"
        )

    return True


class LoginRequest(BaseModel):
    email: str
    senha: str


class TicketCreate(BaseModel):
    titulo: str
    descricao: str
    prioridade: str = "media"
    solicitante: str
    responsavel: Optional[str] = None


class TicketUpdate(BaseModel):
    titulo: Optional[str] = None
    descricao: Optional[str] = None
    status: Optional[str] = None
    prioridade: Optional[str] = None
    responsavel: Optional[str] = None


@app.get("/")
def root():
    return {
        "application": "ServiceTI",
        "status": "online"
    }


@app.get("/health")
@app.get("/api/health")
def health():
    return {
        "status": "healthy"
    }


@app.post("/api/auth/login")
def login(
    login_data: LoginRequest,
    _: bool = Depends(require_internal_api_key)
):
    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    cursor.execute("""
        SELECT
            id,
            nome,
            email,
            senha_hash,
            perfil,
            ativo
        FROM usuarios
        WHERE email = %s
        LIMIT 1
    """, (login_data.email,))

    usuario = cursor.fetchone()

    cursor.close()
    connection.close()

    if not usuario:
        raise HTTPException(
            status_code=401,
            detail="E-mail ou senha inválidos"
        )

    if not usuario["ativo"]:
        raise HTTPException(
            status_code=403,
            detail="Usuário desativado"
        )

    return {
        "id": usuario["id"],
        "nome": usuario["nome"],
        "email": usuario["email"],
        "perfil": usuario["perfil"],
        "senha_hash": usuario["senha_hash"]
    }


@app.get(
    "/api/tickets",
    dependencies=[Depends(require_internal_api_key)]
)
def list_tickets():

    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    cursor.execute("""
        SELECT
            id,
            titulo,
            descricao,
            status,
            prioridade,
            solicitante,
            responsavel,
            criado_em,
            atualizado_em
        FROM tickets
        ORDER BY criado_em DESC
    """)

    tickets = cursor.fetchall()

    cursor.close()
    connection.close()

    return tickets


@app.get(
    "/api/tickets/{ticket_id}",
    dependencies=[Depends(require_internal_api_key)]
)
def get_ticket(ticket_id: int):

    connection = get_connection()
    cursor = connection.cursor(dictionary=True)

    cursor.execute("""
        SELECT
            id,
            titulo,
            descricao,
            status,
            prioridade,
            solicitante,
            responsavel,
            criado_em,
            atualizado_em
        FROM tickets
        WHERE id = %s
    """, (ticket_id,))

    ticket = cursor.fetchone()

    cursor.close()
    connection.close()

    if not ticket:
        raise HTTPException(
            status_code=404,
            detail="Chamado não encontrado"
        )

    return ticket


@app.post(
    "/api/tickets",
    status_code=201,
    dependencies=[Depends(require_internal_api_key)]
)
def create_ticket(ticket: TicketCreate):

    connection = get_connection()
    cursor = connection.cursor()

    cursor.execute("""
        INSERT INTO tickets (
            titulo,
            descricao,
            prioridade,
            solicitante,
            responsavel
        )
        VALUES (%s, %s, %s, %s, %s)
    """, (
        ticket.titulo,
        ticket.descricao,
        ticket.prioridade,
        ticket.solicitante,
        ticket.responsavel
    ))

    connection.commit()

    ticket_id = cursor.lastrowid

    cursor.close()
    connection.close()

    return {
        "id": ticket_id,
        "message": "Chamado criado com sucesso"
    }


@app.put(
    "/api/tickets/{ticket_id}",
    dependencies=[Depends(require_internal_api_key)]
)
def update_ticket(
    ticket_id: int,
    ticket: TicketUpdate
):

    connection = get_connection()
    cursor = connection.cursor()

    fields = []
    values = []

    if ticket.titulo is not None:
        fields.append("titulo = %s")
        values.append(ticket.titulo)

    if ticket.descricao is not None:
        fields.append("descricao = %s")
        values.append(ticket.descricao)

    if ticket.status is not None:
        fields.append("status = %s")
        values.append(ticket.status)

    if ticket.prioridade is not None:
        fields.append("prioridade = %s")
        values.append(ticket.prioridade)

    if ticket.responsavel is not None:
        fields.append("responsavel = %s")
        values.append(ticket.responsavel)

    if not fields:
        raise HTTPException(
            status_code=400,
            detail="Nenhum campo informado para atualização"
        )

    values.append(ticket_id)

    query = f"""
        UPDATE tickets
        SET {", ".join(fields)}
        WHERE id = %s
    """

    cursor.execute(query, values)
    connection.commit()

    if cursor.rowcount == 0:
        cursor.close()
        connection.close()

        raise HTTPException(
            status_code=404,
            detail="Chamado não encontrado"
        )

    cursor.close()
    connection.close()

    return {
        "id": ticket_id,
        "message": "Chamado atualizado com sucesso"
    }


@app.delete(
    "/api/tickets/{ticket_id}",
    dependencies=[Depends(require_internal_api_key)]
)
def delete_ticket(ticket_id: int):

    connection = get_connection()
    cursor = connection.cursor()

    cursor.execute(
        "DELETE FROM tickets WHERE id = %s",
        (ticket_id,)
    )

    connection.commit()

    if cursor.rowcount == 0:
        cursor.close()
        connection.close()

        raise HTTPException(
            status_code=404,
            detail="Chamado não encontrado"
        )

    cursor.close()
    connection.close()

    return {
        "id": ticket_id,
        "message": "Chamado removido com sucesso"
    }