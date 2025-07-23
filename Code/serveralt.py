# ============================================
# Blaise Swan
# Homework 3 - Access Request System (Alternative Server)
# Data Communications
# Instructor: Dr. Michael A. Galle
# Description: Server for handling automatic card sign-in requests
# ============================================
import socket
import threading
import mysql.connector
from datetime import datetime

HOST = '0.0.0.0'
PORT = 5051  # Different port for alternative system
DISCONNECT_MESSAGE = "DISCONNECT"

# Database connection
def get_db_connection():
    """Get a fresh database connection"""
    try:
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="access_requests1"
        )
        return db
    except mysql.connector.Error as e:
        print(f"Database connection error: {e}")
        return None

def log_access_attempt(student_card, success, reason=""):
    """Log access attempts to a separate table"""
    db = get_db_connection()
    if db:
        try:
            cursor = db.cursor()
            sql = """
                INSERT INTO access_logs 
                (student_card, access_time, success, reason) 
                VALUES (%s, %s, %s, %s)
            """
            cursor.execute(sql, (student_card, datetime.now(), success, reason))
            db.commit()
            cursor.close()
        except mysql.connector.Error as e:
            print(f"Error logging access attempt: {e}")
        finally:
            db.close()

def handle_login_request(student_card):
    """Handle card-based login request"""
    db = get_db_connection()
    if not db:
        return "Error: Database connection failed"
    
    try:
        cursor = db.cursor()
        
        # Query for the student card
        sql = """
            SELECT id, student_card, email, username, approved, created_at
            FROM requests 
            WHERE student_card = %s
        """
        cursor.execute(sql, (student_card.strip(),))
        result = cursor.fetchone()
        
        if result:
            id_num, card, email, username, approved, created_at = result
            
            # Check if account is approved
            if approved == 1:
                # Log successful access
                log_access_attempt(student_card, True, "Login successful")
                
                response = f"""Login successful!
👤 User ID: {id_num}
📧 Email: {email}
👨‍💼 Username: {username}
💳 Card: {card}
📅 Account created: {created_at}
⏰ Access time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
✅ Status: APPROVED - Access granted"""
                
                print(f"[ACCESS GRANTED] Card {student_card} - User: {username}")
                
            else:
                # Log failed access attempt
                log_access_attempt(student_card, False, "Account not approved")
                
                response = f"""Account not approved
👤 User: {username}
📧 Email: {email}
💳 Card: {card}
❌ Status: PENDING APPROVAL
⚠️  Please contact administrator"""
                
                print(f"[ACCESS DENIED] Card {student_card} - Account not approved")
        else:
            # Log failed access attempt
            log_access_attempt(student_card, False, "Card not found")
            
            response = f"""User not found
💳 Card number: {student_card}
❌ This card is not registered in the system
ℹ️  Please register for access first"""
            
            print(f"[ACCESS DENIED] Card {student_card} - Not found in database")
        
        cursor.close()
        return response
        
    except mysql.connector.Error as e:
        log_access_attempt(student_card, False, f"Database error: {str(e)}")
        return f"Error: Database query failed - {str(e)}"
    finally:
        db.close()

def handle_client(conn, addr):
    """Handle client connection"""
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
            
            # Handle LOGIN command for card scanning
            if msg.startswith("LOGIN"):
                try:
                    _, student_card = msg.split(" ", 1)
                    response = handle_login_request(student_card)
                    conn.send(response.encode('utf-8'))
                except ValueError:
                    conn.send("Error: Invalid LOGIN command format".encode('utf-8'))
            
            # Handle legacy SAVE command (from original client)
            elif msg.startswith("SAVE"):
                try:
                    _, data = msg.split(" ", 1)
                    fields = data.split("|")
                    
                    db = get_db_connection()
                    if db:
                        cursor = db.cursor()
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
                        cursor.close()
                        db.close()
                        conn.send("Saved to database.".encode('utf-8'))
                    else:
                        conn.send("Error: Database connection failed".encode('utf-8'))
                except Exception as e:
                    conn.send(f"Error saving data: {str(e)}".encode('utf-8'))
            
            # Handle legacy QUERY command
            elif msg.startswith("QUERY"):
                try:
                    _, id_query = msg.split(" ", 1)
                    
                    db = get_db_connection()
                    if db:
                        cursor = db.cursor()
                        cursor.execute("SELECT * FROM requests WHERE id = %s", (id_query.strip(),))
                        result = cursor.fetchone()
                        
                        if result:
                            response = "|".join(map(str, result))
                        else:
                            response = "ID not found."
                        
                        cursor.close()
                        db.close()
                        conn.send(response.encode('utf-8'))
                    else:
                        conn.send("Error: Database connection failed".encode('utf-8'))
                except Exception as e:
                    conn.send(f"Error querying data: {str(e)}".encode('utf-8'))
            
            else:
                conn.send("Error: Unknown command".encode('utf-8'))

        except Exception as e:
            print(f"Error handling client {addr}: {e}")
            conn.send(f"Error: {str(e)}".encode('utf-8'))

    conn.close()
    print(f"[DISCONNECTED] {addr} disconnected.")

def create_access_logs_table():
    """Create access logs table if it doesn't exist"""
    db = get_db_connection()
    if db:
        try:
            cursor = db.cursor()
            sql = """
                CREATE TABLE IF NOT EXISTS access_logs (
                    log_id INT AUTO_INCREMENT PRIMARY KEY,
                    student_card VARCHAR(50),
                    access_time DATETIME,
                    success BOOLEAN,
                    reason VARCHAR(255),
                    INDEX idx_card (student_card),
                    INDEX idx_time (access_time)
                )
            """
            cursor.execute(sql)
            db.commit()
            cursor.close()
            print("[DATABASE] Access logs table ready")
        except mysql.connector.Error as e:
            print(f"Error creating access logs table: {e}")
        finally:
            db.close()

def start():
    """Start the server"""
    print("[STARTING] Alternative Access Server is starting...")
    
    # Create access logs table
    create_access_logs_table()
    
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)  # Allow port reuse
        s.bind((HOST, PORT))
        s.listen()
        
        print(f"[LISTENING] Server listening on {HOST}:{PORT}")
        print("[INFO] Ready to handle card scanning requests")
        print("[INFO] Press Ctrl+C to stop the server")
        
        while True:
            try:
                conn, addr = s.accept()
                thread = threading.Thread(target=handle_client, args=(conn, addr))
                thread.daemon = True  # Allow program to exit even with active threads
                thread.start()
                print(f"[ACTIVE CONNECTIONS] {threading.active_count() - 1}")
            except KeyboardInterrupt:
                print("\n[SHUTDOWN] Server stopping...")
                break
    except Exception as e:
        print(f"[ERROR] Server error: {e}")
    finally:
        try:
            s.close()
        except:
            pass
        print("[SHUTDOWN] Server stopped")

if __name__ == "__main__":
    start()
