# Multi-School Database Migration & Command Reference Guide

આ દસ્તાવેજમાં **Ultra School** પ્રોજેક્ટ માટે Multi-School (Multi-Database) Migration, Database Status, Single-School Migration, All-in-One One-Liner Commands, Live Server Configuration અને Cache Commands વિગતવાર આપેલા છે.

---

## ૧. શાળાઓ અને તેમના ડેટાબેઝ (4 Schools & Databases)

| School Code | School Name | Short Name | Standards / Medium | Local DB | Live Database |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `ues` | Ultra English School | **UES** | ધોરણ 1 થી 8 (English) | `ultra_school_ues` | `jrosvllq_ultra_school_ues` |
| `ups` | Ultra Primary School | **UPS** | ધોરણ 1 થી 8 | `ultra_school_ups` | `jrosvllq_ultra_school_ups` |
| `uv` | Ultra Vidhyalay | **UV** | ધોરણ 1 થી 8 | `ultra_school_uv` | `jrosvllq_ultra_school_uv` |
| `us` | Ultra Secondary School | **US** | ધોરણ 9 થી 10 (Guj / Eng) | `ultra_school_us` | `jrosvllq_ultra_school_us` |

---

## ૨. એકસાથે રન કરવાના One-Liner Commands (Copy & Paste)

### 🚀 A. બધી શાળાઓમાં એકસાથે Migration રન કરવાનો કમાન્ડ:
```bash
php artisan schools:migrate
```

### 🔍 B. બધી શાળાઓનું Migration Status એકસાથે ચેક કરવાનો કમાન્ડ:
```bash
php artisan schools:migrate --status
```

### ⚡ C. ચારેય શાળાઓ વારાફરતી એક જ લાઈનમાં Migration રન કરવી (Chained):
```bash
php artisan schools:migrate --school=ues && php artisan schools:migrate --school=ups && php artisan schools:migrate --school=uv && php artisan schools:migrate --school=us
```

### 📋 D. ચારેય શાળાઓનું Status એક જ લાઈનમાં જોવા માટે:
```bash
php artisan schools:migrate --school=ues --status && php artisan schools:migrate --school=ups --status && php artisan schools:migrate --school=uv --status && php artisan schools:migrate --school=us --status
```

### 🧹 E. બધા જ Cache એકસાથે ક્લિયર અને Cache બનાવવાનો All-in-One કમાન્ડ:
```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache
```

---

## ૩. અલગ-અલગ સ્કૂલ વાઇઝ Commands

### A. ચોક્કસ સ્કૂલનું Migration Status જોવા માટે:
```bash
# 1. UES Status
php artisan schools:migrate --school=ues --status

# 2. UPS Status
php artisan schools:migrate --school=ups --status

# 3. UV Status
php artisan schools:migrate --school=uv --status

# 4. US Status (ધોરણ 9 થી 10)
php artisan schools:migrate --school=us --status
```

### B. ચોક્કસ સ્કૂલમાં Migration રન કરવા માટે:
```bash
# 1. UES Migrate
php artisan schools:migrate --school=ues

# 2. UPS Migrate
php artisan schools:migrate --school=ups

# 3. UV Migrate
php artisan schools:migrate --school=uv

# 4. US Migrate (ધોરણ 9 થી 10)
php artisan schools:migrate --school=us
```

### C. Fresh Migration + Seeding (ડેટાબેઝ રીસેટ કરવા માટે):
```bash
# બધી શાળાઓ Fresh:
php artisan schools:migrate --fresh --seed

# માત્ર એક શાળા Fresh:
php artisan schools:migrate --school=ues --fresh --seed
```

---

## ૪. ડેટા અને યુઝર્સ Sync કરવા માટે (Data Synchronization)
```bash
php artisan schools:sync-initial --from=ultra_school_ues
```

---

## ૫. લાઈવ સર્વર `.env` ફોર્મેટ (Live Multi-Database Configuration)

```env
APP_NAME="Ultra School"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ultraschool.org/software/

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306

# 1. UES (Ultra English School)
DB_DATABASE_UES=jrosvllq_ultra_school_ues
DB_USERNAME_UES=jrosvllq_ultra_school_ues
DB_PASSWORD_UES="ubfXV828A#Q9NE2H"

# 2. UPS (Ultra Primary School)
DB_DATABASE_UPS=jrosvllq_ultra_school_ups
DB_USERNAME_UPS=jrosvllq_ultra_school_ups
DB_PASSWORD_UPS="JllUFO=Q!_J^[j{D"

# 3. UV (Ultra Vidhyalay)
DB_DATABASE_UV=jrosvllq_ultra_school_uv
DB_USERNAME_UV=jrosvllq_ultra_school_uv
DB_PASSWORD_UV="aLtqx9]=WnC#j&XF"

# 4. US (Ultra Secondary - ધોરણ 9 થી 10)
DB_DATABASE_US=jrosvllq_ultra_school_us
DB_USERNAME_US=jrosvllq_ultra_school_us
DB_PASSWORD_US="eLU5yvc.YYB7?4dg"

# Default Connection
DEFAULT_SCHOOL=ues
DB_DATABASE=jrosvllq_ultra_school_ues
DB_USERNAME=jrosvllq_ultra_school_ues
DB_PASSWORD="ubfXV828A#Q9NE2H"

# Session & Cache Settings (Always use 'file' for multi-school setup)
SESSION_DRIVER=file
CACHE_STORE=file
```

---

## ૬. નવું Migration બનાવતી વખતે Workflow

1. **નવું Migration Create કરો:**
   ```bash
   php artisan make:migration add_field_name_to_table
   ```
2. **Migration માં `hasColumn` / `hasTable` સેફ્ટી ચેક રાખો.**
3. **બધી શાળાઓમાં રન કરો:**
   ```bash
   php artisan schools:migrate
   ```
