# Quick test to verify the card system is working
import requests
import json

# Test the web server connection
print("🔧 Testing Web Server Connection...")
try:
    response = requests.get('http://localhost/projectsite/Elevator/card_login.php', timeout=5)
    if response.status_code == 200:
        print("✅ Web server is accessible")
    else:
        print(f"❌ Web server returned status: {response.status_code}")
        exit(1)
except Exception as e:
    print(f"❌ Cannot connect to web server: {e}")
    print("💡 Make sure XAMPP Apache is running!")
    exit(1)

# Test a card login
print("\n🎓 Testing Card Login...")
test_card = "UID: 04 5C 63 5A 7E 70 80"  # Use a known test card

data = {
    'student_card': test_card,
    'action': 'card_login'
}

try:
    response = requests.post('http://localhost/projectsite/Elevator/card_login.php', data=data, timeout=10)
    result = response.json()
    
    if result.get('success'):
        print("✅ Card login successful!")
        print(f"👤 Username: {result.get('username')}")
        
        # Test the AJAX endpoint
        print("\n🌐 Testing AJAX endpoint...")
        ajax_response = requests.get('http://localhost/projectsite/Elevator/check_card_login.php')
        ajax_result = ajax_response.json()
        
        if ajax_result.get('status') == 'logged_in':
            print("✅ AJAX endpoint detects login - browser will redirect!")
        else:
            print(f"⚠️ AJAX status: {ajax_result.get('status')}")
            
    else:
        print(f"❌ Card login failed: {result.get('message')}")
        print("💡 Make sure the test card is registered and approved in the database")

except Exception as e:
    print(f"❌ Test failed: {e}")

print("\n" + "="*50)
print("Now you can:")
print("1. Run clientalt_fixed.py and choose option 1")
print("2. Open http://localhost/projectsite/Elevator/login.html in browser")
print("3. Scan your card with Arduino")
print("4. Browser should auto-redirect to dashboard!")
