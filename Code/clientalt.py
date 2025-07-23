# ============================================
# Blaise Swan
# Web Card Scanner Client for login.html/login1.php
# Data Communications
# Instructor: Dr. Michael A. Galle
# Description: Arduino card scanner that sends data to web login system
# ============================================
import socket
from datetime import datetime
import serial
import serial.tools.list_ports
import time
import requests
import json

# Web server configuration
WEB_SERVER_URL = 'http://localhost/projectsite/Elevator/card_login.php'  # New PHP file we'll create
REGISTER_USER_URL = 'http://localhost/projectsite/Elevator/register_user.php'  # New registration endpoint
XAMPP_SERVER = 'http://localhost'

def test_web_server_connection():
    """Test if the web server and PHP file are accessible"""
    print("🔧 DIAGNOSTIC INFORMATION:")
    print("=" * 50)
    print(f"Target URL: {WEB_SERVER_URL}")
    print(f"Expected file location: c:\\xampp\\htdocs\\projectsite\\Elevator\\card_login.php")
    
    try:
        # Test basic connectivity to localhost
        basic_response = requests.get('http://localhost', timeout=5)
        print(f"✅ Basic localhost connection: OK (Status: {basic_response.status_code})")
    except requests.exceptions.RequestException as e:
        print(f"❌ Basic localhost connection: FAILED ({e})")
        print("💡 XAMPP Apache server is not running!")
        return False
    
    try:
        # Test the specific PHP file
        response = requests.get(WEB_SERVER_URL, timeout=5)
        if response.status_code == 200:
            print(f"✅ PHP file accessible: OK")
            return True
        else:
            print(f"❌ PHP file response: {response.status_code}")
            return False
    except requests.exceptions.RequestException as e:
        print(f"❌ PHP file connection: FAILED ({e})")
        print("💡 Either XAMPP is not running OR the PHP file doesn't exist")
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

def send_card_to_web(student_card):
    """Send card data to web server for authentication"""
    try:
        # Prepare data to send to PHP
        data = {
            'student_card': student_card,
            'action': 'card_login'
        }
        
        # Send POST request to PHP script (which creates session)
        response = requests.post(WEB_SERVER_URL, data=data, timeout=10)
        
        if response.status_code == 200:
            result = response.json()
            return result
        else:
            return {'success': False, 'message': f'HTTP Error: {response.status_code}'}
            
    except requests.exceptions.RequestException as e:
        return {'success': False, 'message': f'Connection error: {str(e)}'}
    except json.JSONDecodeError:
        return {'success': False, 'message': 'Invalid response from server'}

def register_new_user(user_data):
    """Register a new user in the database"""
    try:
        # Add action to data
        user_data['action'] = 'register_user'
        
        # Send POST request to registration PHP script
        response = requests.post(REGISTER_USER_URL, data=user_data, timeout=10)
        
        if response.status_code == 200:
            result = response.json()
            return result
        else:
            return {'success': False, 'message': f'HTTP Error: {response.status_code}'}
            
    except requests.exceptions.RequestException as e:
        return {'success': False, 'message': f'Connection error: {str(e)}'}
    except json.JSONDecodeError:
        return {'success': False, 'message': 'Invalid response from server'}

def print_scan_result(result, card_data):
    """Print the result of a card scan with visual formatting"""
    print("=" * 50)
    if result.get('success'):
        print("✅ LOGIN SUCCESSFUL!")
        print(f"🎓 Card: {card_data}")
        print(f"👤 User: {result.get('username', 'Unknown')}")
        print("🌐 Web session created successfully")
        print("💡 User can now access the dashboard")
        print("🚀 Browser will auto-redirect if login.html is open")
    else:
        print("❌ LOGIN FAILED!")
        print(f"🎓 Card: {card_data}")
        print(f"📝 Reason: {result.get('message', 'Unknown error')}")
        if 'not found' in result.get('message', '').lower():
            print("💡 Card may need to be registered first")
        elif 'not approved' in result.get('message', '').lower():
            print("💡 Card may be pending approval")
    print("=" * 50)
    print("🔍 Ready for next card scan...\n")

def display_welcome_message():
    """Display welcome message and instructions"""
    print("=" * 60)
    print("🎓 WEB LOGIN CARD SCANNER")
    print("=" * 60)
    print("📱 Scan your student card to log into the web system")
    print("🌐 Connected to:", WEB_SERVER_URL)
    print("🔄 System will check for cards continuously")
    print("❌ Press Ctrl+C to exit")
    print("=" * 60)

def continuous_card_scanner():
    """Continuously scan for student cards and attempt web login"""
    port = find_arduino_port()
    
    if port is None:
        print("❌ No Arduino port found. Cannot start card scanning.")
        return
    
    display_welcome_message()
    
    try:
        print(f"🔌 Connecting to Arduino on {port}...")
        ser = serial.Serial(port, 9600, timeout=1)
        print("✅ Successfully connected to Arduino!")
        
        # Wait for Arduino to initialize
        time.sleep(3)
        ser.flushInput()
        
        print("🔍 Ready to scan cards for web login...\n")
        
        last_scanned_card = ""
        last_scan_time = 0
        
        while True:
            try:
                # Check if there's data waiting
                if ser.in_waiting > 0:
                    # Read the data
                    card_data = ser.readline().decode('utf-8').strip()
                    
                    if card_data:
                        current_time = time.time()
                        
                        # Prevent duplicate scans within 3 seconds
                        if card_data != last_scanned_card or (current_time - last_scan_time) > 3:
                            print(f"📱 Card scanned: {card_data}")
                            print("🔍 Checking web database...")
                            print("⏳ Please wait...")
                            
                            # Send card data to web server
                            result = send_card_to_web(card_data)
                            
                            # Display result with color coding
                            print_scan_result(result, card_data)
                            
                            # Update tracking variables to prevent immediate re-scanning
                            last_scanned_card = card_data
                            last_scan_time = current_time
                
                time.sleep(0.5)  # Check every 500ms
                
            except KeyboardInterrupt:
                print("\n🛑 Card scanning stopped by user")
                break
            except Exception as e:
                print(f"❌ Error during scanning: {e}")
                time.sleep(1)
        
        ser.close()
        print("🔌 Arduino connection closed")
        
    except serial.SerialException as e:
        error_msg = str(e).lower()
        if "access is denied" in error_msg or "permission" in error_msg:
            print(f"❌ Error: Port {port} is being used by another application.")
            print("🔧 Please close Arduino IDE Serial Monitor and try again.")
        else:
            print(f"❌ Serial connection error: {e}")
    except Exception as e:
        print(f"❌ Unexpected error: {e}")

def manual_test_mode():
    """Manual mode for testing card login without Arduino"""
    print("🖱️  Manual testing mode for web login")
    print("Enter student card numbers to test the web login system:")
    
    while True:
        try:
            card_input = input("\n💳 Enter student card number (or 'quit' to exit): ").strip()
            
            if card_input.lower() == 'quit':
                break
            
            if card_input:
                print("🔍 Checking web database...")
                result = send_card_to_web(card_input)
                
                if result['success']:
                    print("✅ WEB LOGIN SUCCESSFUL!")
                    print(f"👤 Username: {result.get('username', 'N/A')}")
                    print(f"📧 Email: {result.get('email', 'N/A')}")
                else:
                    print("❌ WEB LOGIN FAILED")
                    print(f"📝 Reason: {result.get('message', 'Unknown error')}")
                    
        except KeyboardInterrupt:
            break

def register_user_with_card_scan():
    """Register a new user by scanning their card and entering details"""
    print("\n🎓 NEW USER REGISTRATION WITH CARD SCAN")
    print("=" * 50)
    
    # First, scan the card
    port = find_arduino_port()
    
    if port is None:
        print("❌ No Arduino port found. Cannot scan card.")
        return
    
    print("📱 Please scan the student card for the new user...")
    
    try:
        print(f"🔌 Connecting to Arduino on {port}...")
        ser = serial.Serial(port, 9600, timeout=1)
        print("✅ Successfully connected to Arduino!")
        
        # Wait for Arduino to initialize
        time.sleep(3)
        ser.flushInput()
        
        print("🔍 Ready to scan card for new user registration...\n")
        print("⏳ Waiting for card scan (timeout: 30 seconds)...")
        
        start_time = time.time()
        card_data = None
        
        while time.time() - start_time < 30:  # 30 second timeout
            try:
                if ser.in_waiting > 0:
                    # Read the data
                    card_data = ser.readline().decode('utf-8').strip()
                    
                    if card_data:
                        print(f"📱 Card scanned: {card_data}")
                        break
                
                time.sleep(0.5)
                
            except Exception as e:
                print(f"❌ Error during scanning: {e}")
                time.sleep(1)
        
        ser.close()
        
        if not card_data:
            print("❌ No card was scanned within the timeout period.")
            return
        
        # Now get user details
        print("\n📋 Enter the following details for the new user:")
        print("=" * 50)
        
        id_num = input("ID Number: ").strip()
        email = input("Email: ").strip()
        username = input("Username: ").strip()
        password = input("Password: ").strip()
        reason = input("Reason for request: ").strip()
        
        print("\nApproval Options:")
        print("0 = Pending approval (requires admin approval)")
        print("1 = Auto-approved (immediate access)")
        approved = input("Approved status (0 or 1): ").strip()
        
        # Validate inputs
        if not all([id_num, email, username, password, reason]):
            print("❌ All fields are required!")
            return
        
        if approved not in ['0', '1']:
            print("❌ Approved status must be 0 or 1!")
            return
        
        # Prepare user data
        user_data = {
            'id': id_num,
            'student_card': card_data,
            'email': email,
            'username': username,
            'password': password,
            'reason': reason,
            'approved': int(approved)
        }
        
        print("\n🔄 Registering new user...")
        print("⏳ Please wait...")
        
        # Register the user
        result = register_new_user(user_data)
        
        # Display result
        print("\n" + "=" * 50)
        if result.get('success'):
            print("✅ USER REGISTRATION SUCCESSFUL!")
            print(f"🆔 ID: {result.get('id')}")
            print(f"🎓 Card: {result.get('student_card')}")
            print(f"👤 Username: {result.get('username')}")
            print(f"📧 Email: {result.get('email')}")
            print(f"📅 Created: {result.get('created_at')}")
            
            if result.get('approved') == 1:
                print("✅ Status: APPROVED - User can login immediately")
                print("🌐 User can now use card login system")
            else:
                print("⏳ Status: PENDING - Awaiting admin approval")
        else:
            print("❌ USER REGISTRATION FAILED!")
            print(f"📝 Reason: {result.get('message', 'Unknown error')}")
            
            if 'already exists' in result.get('message', '').lower():
                print("💡 Try using different ID, card, or username")
        print("=" * 50)
        
    except serial.SerialException as e:
        error_msg = str(e).lower()
        if "access is denied" in error_msg or "permission" in error_msg:
            print(f"❌ Error: Port {port} is being used by another application.")
            print("🔧 Please close Arduino IDE Serial Monitor and try again.")
        else:
            print(f"❌ Serial connection error: {e}")
    except Exception as e:
        print(f"❌ Unexpected error: {e}")

def manual_user_registration():
    """Register a new user manually (without Arduino)"""
    print("\n🖱️  MANUAL USER REGISTRATION")
    print("=" * 50)
    print("Enter user details manually (for testing without Arduino)")
    
    print("\n📋 Enter the following details for the new user:")
    
    id_num = input("ID Number: ").strip()
    student_card = input("Student Card UID: ").strip()
    email = input("Email: ").strip()
    username = input("Username: ").strip()
    password = input("Password: ").strip()
    reason = input("Reason for request: ").strip()
    
    print("\nApproval Options:")
    print("0 = Pending approval (requires admin approval)")
    print("1 = Auto-approved (immediate access)")
    approved = input("Approved status (0 or 1): ").strip()
    
    # Validate inputs
    if not all([id_num, student_card, email, username, password, reason]):
        print("❌ All fields are required!")
        return
    
    if approved not in ['0', '1']:
        print("❌ Approved status must be 0 or 1!")
        return
    
    # Prepare user data
    user_data = {
        'id': id_num,
        'student_card': student_card,
        'email': email,
        'username': username,
        'password': password,
        'reason': reason,
        'approved': int(approved)
    }
    
    print("\n🔄 Registering new user...")
    print("⏳ Please wait...")
    
    # Register the user
    result = register_new_user(user_data)
    
    # Display result
    print("\n" + "=" * 50)
    if result.get('success'):
        print("✅ USER REGISTRATION SUCCESSFUL!")
        print(f"🆔 ID: {result.get('id')}")
        print(f"🎓 Card: {result.get('student_card')}")
        print(f"👤 Username: {result.get('username')}")
        print(f"📧 Email: {result.get('email')}")
        print(f"📅 Created: {result.get('created_at')}")
        
        if result.get('approved') == 1:
            print("✅ Status: APPROVED - User can login immediately")
            print("🌐 User can now use card login system")
        else:
            print("⏳ Status: PENDING - Awaiting admin approval")
    else:
        print("❌ USER REGISTRATION FAILED!")
        print(f"📝 Reason: {result.get('message', 'Unknown error')}")
        
        if 'already exists' in result.get('message', '').lower():
            print("💡 Try using different ID, card, or username")
    print("=" * 50)

def test_ajax_system():
    """Test the browser AJAX polling system"""
    print("🌐 AJAX POLLING SYSTEM TEST")
    print("=" * 50)
    print("This will simulate a card login and test if the browser")
    print("would detect it and redirect automatically.")
    print("")
    
    # Test the check_card_login.php endpoint
    check_url = 'http://localhost/projectsite/Elevator/check_card_login.php'
    clear_url = 'http://localhost/projectsite/Elevator/clear_session.php'
    
    print("0. Clearing any existing sessions...")
    try:
        clear_response = requests.get(clear_url, timeout=5)
        if clear_response.status_code == 200:
            print("✅ Sessions cleared")
        else:
            print(f"⚠️ Session clear responded with: {clear_response.status_code}")
    except Exception as e:
        print(f"⚠️ Session clear failed: {e}")
    
    print("\n1. Testing AJAX endpoint...")
    try:
        response = requests.get(check_url, timeout=5)
        if response.status_code == 200:
            result = response.json()
            print(f"✅ AJAX endpoint accessible: {result.get('status', 'unknown')}")
            print(f"📋 Current status: {result.get('message', 'No message')}")
        else:
            print(f"❌ AJAX endpoint error: {response.status_code}")
            return
    except Exception as e:
        print(f"❌ Cannot reach AJAX endpoint: {e}")
        return
    
    print("\n2. Simulating card login...")
    test_card = input("Enter a test card number (or press Enter for default): ").strip()
    if not test_card:
        test_card = "UID: 04 5C 63 5A 7E 70 80"
    
    # Perform card login
    print("🔄 Attempting login...")
    login_result = send_card_to_web(test_card)
    print_scan_result(login_result, test_card)
    
    if login_result.get('success'):
        print("\n3. Testing AJAX detection...")
        print("🔍 Checking if browser would detect this login...")
        
        # Check what the AJAX endpoint returns now
        for i in range(5):  # Try multiple times to ensure session is created
            time.sleep(1)  # Give the session time to be created
            try:
                # Add cache buster to prevent caching
                response = requests.get(f"{check_url}?t={int(time.time())}", timeout=5)
                if response.status_code == 200:
                    result = response.json()
                    print(f"📊 Attempt {i+1} - AJAX status: {result.get('status', 'unknown')}")
                    print(f"📊 Message: {result.get('message', 'No message')}")
                    
                    if result.get('status') == 'logged_in':
                        print("✅ SUCCESS! Browser would auto-redirect to dashboard")
                        print("🌐 Open login.html in browser to see real-time redirect!")
                        print(f"🔗 Dashboard URL: {result.get('redirect_url', 'dashboard.php')}")
                        break
                    elif result.get('status') == 'logged_in_manual':
                        print("✅ Manual login detected - this would also redirect")
                        break
                    else:
                        print("⚠️  Session may not be properly created yet...")
                else:
                    print(f"❌ AJAX check failed: {response.status_code}")
            except Exception as e:
                print(f"❌ AJAX check error: {e}")
        else:
            print("❌ Session was not detected after 5 attempts")
            print("💡 Check if XAMPP sessions are working correctly")
            print("💡 Try manually visiting the test page to debug")
            
        # Additional debugging info
        print(f"\n🔧 DEBUG INFO:")
        print(f"Card used: {test_card}")
        print(f"Login success: {login_result.get('success')}")
        print(f"Session ID returned: {login_result.get('session_id', 'None')}")
        
        # Test cleanup
        print(f"\n4. Cleaning up test session...")
        try:
            clear_response = requests.get(clear_url, timeout=5)
            if clear_response.status_code == 200:
                print("✅ Test session cleaned up")
            else:
                print(f"⚠️ Cleanup responded with: {clear_response.status_code}")
        except Exception as e:
            print(f"⚠️ Cleanup failed: {e}")
        
    else:
        print("\n⚠️  Card login failed, so AJAX redirect won't work")
        print("💡 Try with a registered and approved card")
        print(f"💡 Error: {login_result.get('message', 'Unknown error')}")
    
    print("\n" + "=" * 50)
    print("💡 TIP: To test browser auto-redirect:")
    print("1. Open: http://localhost/projectsite/Elevator/login.html")
    print("2. Run this test again")
    print("3. Browser should redirect within 2 seconds after successful login")
    input("Press Enter to continue...")

def main():
    """Main function"""
    while True:
        print("\n🎓 WEB LOGIN CARD SCANNER & USER REGISTRATION")
        print("1. 📱 Start Card Scanning for Web Login")
        print("2. 🖱️  Manual Testing Mode")
        print("3. 👤 Register New User (with Card Scan)")
        print("4. ✍️  Register New User (Manual Entry)")
        print("5. ⚙️  Test Web Connection")
        print("6. 🌐 Test Browser AJAX System")
        print("7. ❌ Exit")
        
        choice = input("Choose an option: ").strip()
        
        if choice == '1':
            continuous_card_scanner()
        elif choice == '2':
            manual_test_mode()
        elif choice == '3':
            register_user_with_card_scan()
        elif choice == '4':
            manual_user_registration()
        elif choice == '5':
            print("🔗 Testing connection to web server...")
            if test_web_server_connection():
                print("✅ Web server connection successful!")
                print("🧪 Testing with sample card data...")
                result = send_card_to_web("UID: 04 5C 63 5A 7E 70 80")
                if result.get('success') == False:
                    print(f"📝 Expected result: {result.get('message')}")
                    print("✅ PHP script is working correctly!")
                else:
                    print("✅ Connection test completed!")
            else:
                print("❌ Web server connection failed")
                print("\n💡 TROUBLESHOOTING STEPS:")
                print("1. Open XAMPP Control Panel")
                print("2. Start Apache (should show green)")
                print("3. Start MySQL (should show green)")
                print("4. Verify file exists: c:\\xampp\\htdocs\\projectsite\\Elevator\\card_login.php")
                print("5. Test basic XAMPP: http://localhost in browser")
        elif choice == '6':
            test_ajax_system()
        elif choice == '7':
            print("👋 Goodbye!")
            break
        else:
            print("❌ Invalid choice. Please try again.")

if __name__ == '__main__':
    # Install requests if not available
    try:
        import requests
    except ImportError:
        print("Installing required package...")
        import subprocess
        subprocess.check_call(['pip', 'install', 'requests'])
        import requests
    
    main()
