# Test the user registration system
import requests
import json

REGISTER_URL = 'http://localhost/projectsite/Elevator/register_user.php'

# Test data
test_user = {
    'id': '999',
    'student_card': 'UID: FF FF FF FF FF FF FF',
    'email': 'test@example.com',
    'username': 'testuser999',
    'password': 'TestPass123!',
    'reason': 'Testing registration system',
    'approved': 1,
    'action': 'register_user'
}

print("🧪 Testing User Registration System...")
print("=" * 50)

try:
    response = requests.post(REGISTER_URL, data=test_user, timeout=10)
    
    if response.status_code == 200:
        result = response.json()
        
        if result.get('success'):
            print("✅ Registration test successful!")
            print(f"👤 Username: {result.get('username')}")
            print(f"🆔 ID: {result.get('id')}")
            print(f"🎓 Card: {result.get('student_card')}")
            print(f"📧 Email: {result.get('email')}")
            print(f"✅ Approved: {result.get('approved')}")
        else:
            print(f"❌ Registration failed: {result.get('message')}")
            if 'already exists' in result.get('message', ''):
                print("💡 This is expected if running test multiple times")
    else:
        print(f"❌ HTTP Error: {response.status_code}")
        
except Exception as e:
    print(f"❌ Test failed: {e}")

print("\n💡 Now you can test the complete registration system!")
print("Run: python clientalt.py")
print("Choose option 3 or 4 to register new users")
