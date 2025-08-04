# ============================================
# Blaise Swan
# Homework 3 - Access Request System
# Data Communications
# Instructor: Dr. Michael A. Galle
# Description: Client-server program for submitting and querying card access requests.
# ============================================
import socket
from datetime import datetime
import serial
import serial.tools.list_ports
import time

SERVER_IP = '127.0.0.1'  # Change if connecting to classmate or Elevator (this is loopback mode)
PORT = 5050

def send_data(command, payload): # Function to send data to the server
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((SERVER_IP, PORT))
        s.sendall(f"{command} {payload}".encode())
        response = s.recv(4096).decode()
        print(f"Server response:\n{response}")

def check_port_availability(port):
    """Check if a COM port is available"""
    try:
        # Try to open and immediately close the port
        test_serial = serial.Serial(port, 9600, timeout=0.1)
        test_serial.close()
        return True
    except serial.SerialException:
        return False

def find_arduino_port():
    """Find the Arduino port automatically"""
    ports = serial.tools.list_ports.comports()
    
    # First, try to find Arduino-like devices
    for port in ports:
        if 'Arduino' in port.description or 'CH340' in port.description or 'USB Serial' in port.description:
            print(f"Found Arduino-like device on {port.device}")
            return port.device
    
    # If no Arduino found, show all available ports
    if ports:
        print("No Arduino detected automatically. Available COM ports:")
        for i, port in enumerate(ports):
            print(f"{i+1}. {port.device} - {port.description}")
        
        try:
            choice = int(input("Select COM port (enter number): ")) - 1
            if 0 <= choice < len(ports):
                return ports[choice].device
        except (ValueError, IndexError):
            print("Invalid selection.")
    
    return None

def read_from_arduino(port=None, baudrate=9600, timeout=10):
    """Read student ID from Arduino card scanner"""
    if port is None:
        port = find_arduino_port()
    
    if port is None:
        print("No Arduino port found or selected.")
        return None
    
    try:
        print(f"Attempting to connect to Arduino on {port}...")
        print("Note: Make sure Arduino IDE Serial Monitor is closed!")
        
        # Try to open the serial port
        ser = serial.Serial(port, baudrate, timeout=1)
        print(f"Successfully opened {port}")
        
        # Wait a moment for Arduino to reset/initialize
        print("Waiting for Arduino to initialize...")
        time.sleep(3)
        
        # Clear any existing data in the buffer
        ser.flushInput()
        print("Buffer cleared, ready to receive data")
        
        print("Connected! Please scan a card or send data from Arduino...")
        print(f"Listening for data (timeout: {timeout} seconds)...")
        print("Debug: Waiting for incoming data...")
        
        start_time = time.time()
        while time.time() - start_time < timeout:
            # Check if there's data waiting
            bytes_waiting = ser.in_waiting
            if bytes_waiting > 0:
                print(f"Debug: {bytes_waiting} bytes available to read")
                try:
                    # Read the line
                    line = ser.readline().decode('utf-8').strip()
                    print(f"Debug: Raw data received: '{line}'")
                    
                    if line:  # If we received non-empty data
                        print(f"✓ Data received! Student ID: {line}")
                        ser.close()
                        return line
                except UnicodeDecodeError as e:
                    print(f"Debug: Unicode decode error: {e}")
                    # Try reading as bytes and show what we got
                    ser.flushInput()
            
            time.sleep(0.1)  # Small delay
        
        print("⏰ Timeout: No data was received from Arduino.")
        ser.close()
        return None
        
    except serial.SerialException as e:
        error_msg = str(e).lower()
        if "access is denied" in error_msg or "permission" in error_msg:
            print(f"❌ Error: Port {port} is being used by another application.")
            print("\n🔧 Troubleshooting steps:")
            print("1. Close Arduino IDE completely (File → Exit)")
            print("2. Close any Serial Monitor or Terminal programs")
            print("3. Unplug Arduino USB cable for 5 seconds")
            print("4. Plug it back in and try again")
            print("5. Try a different USB port")
        elif "could not open port" in error_msg:
            print(f"❌ Error: Could not open {port}")
            print("This port might not exist or be accessible")
        else:
            print(f"❌ Serial connection error: {e}")
        return None
    except Exception as e:
        print(f"❌ Unexpected error: {e}")
        return None

def add_entry_with_arduino():
    """Function to add a new card request entry using Arduino for student card scanning"""
    print("\nScanning for student card...")
    
    # Try to read student ID from Arduino
    scanned_id = read_from_arduino()
    
    if scanned_id:
        student_card = scanned_id
        print(f"Using scanned Student Card: {student_card}")
    else:
        print("Card scan failed or timed out. Please enter manually:")
        student_card = input("Student Card: ")
    
    print("\nEnter the following details:")
    id_num = input("ID: ")
    email = input("Email: ")
    username = input("Username: ")
    password = input("Password: ")
    reason = input("Reason for request: ")
    created_at = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    approved = input("Approved (0 for No, 1 for Yes): ")

    # Use | as separator, in the correct order (matching our DB)
    record = f"{id_num}|{student_card}|{email}|{username}|{password}|{reason}|{created_at}|{approved}"
    send_data('SAVE', record)

def add_entry():# Function to add a new card request entry
    print("\nEnter the following details:")
    id_num = input("ID: ")
    student_card = input("Student Card: ")
    email = input("Email: ")
    username = input("Username: ")
    password = input("Password: ")
    reason = input("Reason for request: ")
    created_at = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    approved = input("Approved (0 for No, 1 for Yes): ")

    # Use | as separator, in the correct order (matching our DB)
    record = f"{id_num}|{student_card}|{email}|{username}|{password}|{reason}|{created_at}|{approved}"
    send_data('SAVE', record)

def query_entry():# Function to query an existing request by ID
    id_query = input("Enter ID to search: ")
    send_data('QUERY', id_query)

def main():# Main function to run the client interface
    while True:
        print("\n1. Submit New Card Request (Manual)")
        print("2. Submit New Card Request (Arduino Card Scanner)")
        print("3. Query Existing Request by ID")
        print("4. Exit")
        choice = input("Choose an option: ")

        if choice == '1':
            add_entry()
        elif choice == '2':
            add_entry_with_arduino()
        elif choice == '3':
            query_entry()
        elif choice == '4':
            break
        else:
            print("Invalid choice.")

if __name__ == '__main__':# Entry point for the client program
    main()