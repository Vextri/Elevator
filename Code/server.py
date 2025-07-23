# ============================================
# Blaise Swan
# Homework 3 - Access Request System
# Data Communications
# Instructor: Dr. Michael A. Galle
# Description: Client-server program for submitting and querying card access requests.
# ============================================
import socket
import threading
import mysql.connector

HOST = '0.0.0.0' #accept connections from any IP address
PORT = 5050
SEPARATOR = "|" # Separator for fields in the database
DISCONNECT_MESSAGE = "DISCONNECT"

db = mysql.connector.connect(
    host="localhost",
    user="root",         # <- default
    password="",         # <- default
    database="access_requests1"  # <- test database
)
cursor = db.cursor()

def handle_client(conn, addr): # Handle client connection
    print(f"[NEW CONNECTION] {addr} connected.")
    connected = True
    while connected:
        try:
            msg = conn.recv(1024).decode('utf-8')
            if not msg:
                break
            if msg == DISCONNECT_MESSAGE:
                connected = False
                break
            # Handle SAVE command
            if msg.startswith("SAVE"):
                _, data = msg.split(" ", 1)
                fields = data.split(SEPARATOR)
                # Expecting: id|student_card|email|username|password|reason|created_at|approved
                sql = """
                    INSERT INTO requests
                    (id, student_card, email, username, password, reason, created_at, approved)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    ON DUPLICATE KEY UPDATE
                        student_card=VALUES(student_card),
                        email=VALUES(email),
                        username=VALUES(username),
                        password=VALUES(password),
                        reason=VALUES(reason),
                        created_at=VALUES(created_at),
                        approved=VALUES(approved)
                """
                cursor.execute(sql, tuple(fields))
                db.commit()
                conn.send("Saved to database.".encode('utf-8'))
            # Handle QUERY command
            elif msg.startswith("QUERY"):
                _, id = msg.split(" ", 1)
                cursor.execute("SELECT * FROM requests WHERE id = %s", (id.strip(),))
                result = cursor.fetchone()
                if result:
                    response = SEPARATOR.join(map(str, result))
                else:
                    response = "ID not found."
                conn.send(response.encode('utf-8'))

        except Exception as e:
            conn.send(f"Error: {str(e)}".encode('utf-8'))

    conn.close()
    print(f"[DISCONNECTED] {addr} disconnected.")

def start(): # Start the server
    print("[STARTING] Server is starting...")
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.bind((HOST, PORT))
    s.listen()
    print(f"[LISTENING] Server listening on {HOST}:{PORT}")
    while True:
        conn, addr = s.accept()
        thread = threading.Thread(target=handle_client, args=(conn, addr))
        thread.start()

if __name__ == "__main__":
    start()