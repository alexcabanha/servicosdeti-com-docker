import os
import mysql.connector


def get_connection():
    return mysql.connector.connect(
        host="mysql",
        port=3306,
        database=os.getenv("MYSQL_DATABASE"),
        user=os.getenv("MYSQL_USER"),
        password=os.getenv("MYSQL_PASSWORD")
    )