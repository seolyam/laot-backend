# La-ot API Structure - Cleaned & Unified

## 🧹 API Cleanup Summary

### Before (Duplicate Files):
- ❌ `register_simple.php` - Basic registration
- ❌ `register.php` - Full registration  
- ❌ `login_simple.php` - Basic login
- ❌ `login.php` - Full login

### After (Unified Structure):
- ✅ `register.php` - **Unified registration** (handles both simple & full modes)
- ✅ `login.php` - **Unified login** (enhanced with JWT tokens)
- ✅ `profile.php` - User profile management
- ✅ `workouts.php` - Workout session management
- ✅ `goals.php` - Fitness goals tracking

## 🔄 How the Unified System Works

### Registration Endpoint: `/api/register.php`

#### Simple Mode (Username + Password Only)
```json
{
    "username": "testuser123",
    "password": "TestPass123"
}
```
**Response includes:** `"registration_mode": "simple"`

#### Full Mode (All Fields)
```json
{
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "password": "SecurePass123",
    "university": "La-ot University",
    "age": 25,
    "weight": 75.5,
    "height": "180cm",
    "user_role": "athlete",
    "sport": "Football",
    "position": "Forward",
    "team": "Team Alpha",
    "fitness_level": "intermediate"
}
```
**Response includes:** `"registration_mode": "full"`

### Login Endpoint: `/api/login.php`
- Returns JWT token for authentication
- Includes user profile data
- Supports both athlete and coach roles

## 🎯 Benefits of the Unified Structure

1. **Single Endpoint**: No more confusion about which registration/login to use
2. **Backward Compatible**: Simple registration still works exactly as before
3. **Enhanced Features**: Full registration includes all profile fields
4. **Consistent Responses**: All endpoints return standardized JSON with JWT tokens
5. **Easier Maintenance**: One codebase instead of duplicate files
6. **Better Documentation**: Clear examples for both modes

## 📱 API Usage Examples

### For Quick Testing/Development:
```bash
# Simple registration
curl -X POST https://laot.great-site.net/laot-api/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"username": "testuser", "password": "TestPass123"}'
```

### For Production Applications:
```bash
# Full registration with complete user data
curl -X POST https://laot.great-site.net/laot-api/api/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "johndoe",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "password": "SecurePass123",
    "university": "La-ot University",
    "age": 25,
    "user_role": "athlete"
  }'
```

## 🔐 Security Features

- **JWT Authentication**: Secure token-based system
- **Password Hashing**: Bcrypt with PASSWORD_DEFAULT
- **Input Validation**: Comprehensive sanitization
- **SQL Injection Prevention**: Prepared statements
- **CORS Support**: Cross-origin requests enabled

## 📊 Database Integration

The unified API automatically:
- Creates user records in the `users` table
- Generates athlete profiles in `athlete_profiles` table
- Sets appropriate default values for simple registrations
- Maintains data consistency across all tables

## 🚀 Next Steps

1. **Test the unified endpoints** using the updated `test_curl.php`
2. **Update your applications** to use the new unified endpoints
3. **Remove references** to the old `*_simple.php` files
4. **Enjoy the benefits** of a cleaner, more maintainable API

## 📞 Support

Your La-ot API is now:
- ✅ **Clean** - No duplicate files
- ✅ **Unified** - Single endpoints for each function
- ✅ **Flexible** - Supports both simple and full modes
- ✅ **Secure** - JWT authentication and input validation
- ✅ **Maintainable** - Easier to update and extend

The API maintains full backward compatibility while providing enhanced features for advanced use cases!
