# Testing Login Endpoint

## Test Credentials (After Seeding)
- Email: `doctor@example.com`
- Password: `password123`

OR

- Email: `sara@example.com`
- Password: `password123`

## Test with cURL

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"doctor@example.com","password":"password123"}'
```

## Expected Response

```json
{
  "doctor": {
    "id": 1,
    "name": "د. أحمد محمد",
    "email": "doctor@example.com",
    "title": "استشاري جراحة العظام",
    "specialty": "دكتوراة جراحة العظام - جامعة القاهرة",
    "bio": "أكثر من 15 عاماً من الخبرة في جراحات المناظير وإصابات الملاعب.",
    "photo": "...",
    "clinics": []
  },
  "token": "1|..."
}
```

## CORS Test

If you're testing from the browser console:

```javascript
fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'doctor@example.com',
    password: 'password123'
  })
})
.then(r => r.json())
.then(console.log)
.catch(console.error);
```

## Common Issues

1. **CORS Error**: Make sure `config/cors.php` has `localhost:3000` in `allowed_origins`
2. **401 Unauthorized**: Check if the doctor exists in the database and password is hashed correctly
3. **500 Error**: Check Laravel logs in `storage/logs/laravel.log`
