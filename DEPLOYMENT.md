# Deployment Guide - Face Recognition System

## 📋 Pre-requisites

- Face++ API credentials (API Key & API Secret)
- Same Face++ account across all environments
- Redis or Memcached in production (recommended)

## 🚀 Deploying to Production

### Step 1: Export FaceSet Configuration (Development)

In your **development** environment:

```bash
php artisan facepp:export
```

This will output:
```
FACEPP_FACESET_TOKEN=2ac1cae9ac0e70822ced8e199174d701
FACEPP_FACESET_OUTER_ID=employee_faceset
```

**📝 Copy these values** - you'll need them for production.

---

### Step 2: Configure Production Environment

In your **production** `.env` file, add:

```env
# Face++ API Configuration
FACEPP_API_KEY=your_production_api_key
FACEPP_API_SECRET=your_production_api_secret
FACEPP_API_URL=https://api-us.faceplusplus.com/facepp/v3

# Face++ FaceSet Configuration (from development export)
FACEPP_FACESET_TOKEN=2ac1cae9ac0e70822ced8e199174d701
FACEPP_FACESET_OUTER_ID=employee_faceset
```

**Important Notes:**
- ✅ Use the **SAME** FaceSet token across all environments
- ✅ This ensures all employee face_tokens are in one FaceSet
- ✅ Face++ API Key/Secret can be the same or different (depending on your account setup)

---

### Step 3: Import FaceSet in Production

In your **production** environment:

```bash
php artisan facepp:import
```

This will:
1. Read `FACEPP_FACESET_TOKEN` from `.env`
2. Verify it exists with Face++ API
3. Store it in production cache
4. Display FaceSet details (name, face count, etc.)

Expected output:
```
✓ FaceSet imported successfully!
+---------------+----------------------------------+
| Property      | Value                            |
+---------------+----------------------------------+
| Display Name  | Employee Faces                   |
| Outer ID      | employee_faceset                 |
| FaceSet Token | 2ac1cae9ac0e70822ced8e199174d701 |
| Face Count    | 5                                |
+---------------+----------------------------------+
```

---

## 🔄 Multi-Environment Strategy

### Option A: Shared FaceSet (Recommended) ✅

**Use the SAME FaceSet across dev/staging/prod**

**Pros:**
- All employee faces work everywhere
- No need to re-register in each environment
- Database can be synced/copied between environments

**Cons:**
- Dev/staging face registrations appear in production FaceSet

**When to use:** If you sync databases or want seamless testing

---

### Option B: Separate FaceSets per Environment

**Create different FaceSets for each environment**

```bash
# Development
php artisan facepp:setup
# Creates: dev_employee_faceset

# Staging
php artisan facepp:setup
# Creates: staging_employee_faceset

# Production
php artisan facepp:setup
# Creates: prod_employee_faceset
```

**Pros:**
- Isolated environments
- No test data in production

**Cons:**
- Employees must re-register in each environment
- Can't easily copy database between environments

**When to use:** If you want strict environment separation

---

## 🗄️ Database Migration

When deploying to production:

1. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

2. **Import FaceSet:**
   ```bash
   php artisan facepp:import
   ```

3. **Verify cache:**
   ```bash
   php artisan tinker --execute="echo Cache::get('facepp_faceset_token');"
   ```

---

## 🔧 Cache Configuration

### Development (Current)
```env
CACHE_STORE=database
```

### Production (Recommended)
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**After changing cache driver:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan facepp:import  # Re-import FaceSet
```

---

## 🧪 Testing After Deployment

1. **Verify FaceSet:**
   ```bash
   php artisan tinker --execute="
   echo 'Token: ' . \Illuminate\Support\Facades\Cache::get('facepp_faceset_token');
   "
   ```

2. **Test Registration:**
   - Go to `/auth/employee/register`
   - Complete registration with face capture
   - Verify face_token is added to FaceSet

3. **Test Login:**
   - Go to `/auth/login`
   - Login with face + PIN
   - Verify authentication works

---

## 📊 Monitoring

### Check FaceSet Details
```bash
php artisan tinker --execute="
\$service = app(App\Services\FaceRecognitionService::class);
\$token = \Illuminate\Support\Facades\Cache::get('facepp_faceset_token');
\$result = \$service->getFaceSetDetail(\$token);
print_r(\$result);
"
```

### Face Count in FaceSet
Shows how many employees are registered:
```bash
php artisan tinker --execute="
\$service = app(App\Services\FaceRecognitionService::class);
\$token = \Illuminate\Support\Facades\Cache::get('facepp_faceset_token');
\$result = \$service->getFaceSetDetail(\$token);
echo 'Registered employees: ' . \$result['faceset']['face_count'];
"
```

---

## 🔐 Security Checklist

- [ ] Face++ API credentials stored in `.env` (not committed)
- [ ] Photos stored in private storage (`storage/app/private`)
- [ ] Temporary URLs expire (default: 60 minutes)
- [ ] `face_token` hidden in model serialization
- [ ] No remember me for employees
- [ ] HTTPS enabled in production
- [ ] Redis password configured (if using Redis)

---

## 🆘 Troubleshooting

### "No FaceSet token found"
```bash
# Solution: Import from .env
php artisan facepp:import
```

### "FaceSet token invalid"
```bash
# Solution: Verify Face++ credentials
php artisan tinker --execute="echo config('facepp.api_key');"

# Re-export from working environment
php artisan facepp:export
```

### Cache not persisting
```bash
# Check cache driver
php artisan tinker --execute="echo config('cache.default');"

# Clear and reconfigure
php artisan config:clear
php artisan cache:clear
php artisan facepp:import
```

---

## 📞 Commands Reference

| Command | Description |
|---------|-------------|
| `php artisan facepp:setup` | Create new FaceSet (first time only) |
| `php artisan facepp:export` | Export FaceSet config to copy to production |
| `php artisan facepp:import` | Import FaceSet config from .env |

---

## 📝 Notes

- FaceSet tokens **do not expire**
- Each Face++ account can have multiple FaceSets
- Face tokens expire in 72 hours **unless** added to a FaceSet
- Maximum 10,000 faces per FaceSet
- 3 QPS (queries per second) on free tier
