# Multi-School Database Migration & Setup Guide

આ દસ્તાવેજમાં **Ultra School** પ્રોજેક્ટ માટે Multi-School (Multi-Database) Migration, Database Creation, Seeding અને Data Synchronization ના બધા જ કમાન્ડ્સ અને સ્ટેપ્સ વિગતવાર આપેલા છે.

---

## ૧. શાળાઓ અને તેમના ડેટાબેઝનું લિસ્ટ (School Databases)

પ્રોજેક્ટમાં `config/schools.php` મુજબ નીચે મુજબના ૪ અલગ અલગ ડેટાબેઝ છે:

| School Code | School Name | Short Name | Database Name |
| :--- | :--- | :--- | :--- |
| `ues` | Ultra English School | **UES** | `ultra_school_ues` |
| `ups` | Ultra Primary School | **UPS** | `ultra_school_ups` |
| `uv` | Ultra Vidhyalay | **UV** | `ultra_school_uv` |
| `us` | Ultra Secondary School | **US** | `ultra_school_us` |

---

## ૨. સામાન્ય સેટઅપ (Initial Setup)

1. **XAMPP / MySQL ચાલુ કરો:** ખાતરી કરો કે MySQL સર્વર કાર્યરત છે.
2. **`.env` ફાઈલ ચકાસો:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ultra_school_ues
   DB_USERNAME=root
   DB_PASSWORD=
   ```

---

## ૩. Migration Commands (સ્ટેપ-બાય-સ્ટેપ)

### A. બધી જ શાળાઓમાં એકસાથે Migration રન કરવું (All Schools)

નવા માઇગ્રેશન બધી જ ૪ સ્કૂલના ડેટાબેઝમાં લાગુ કરવા:
```bash
php artisan schools:migrate
```
> **નોંધ:** જો ડેટાબેઝ MySQL માં બનેલો નહીં હોય તો આ કમાન્ડ આપોઆપ નવો ડેટાબેઝ બનાવી દેશે (`CREATE DATABASE IF NOT EXISTS`).

---

### B. કોઈ એક ચોક્કસ શાળા માટે Migration રન કરવું (Single School)

જો ફક્ત કોઈ એક જ સ્કૂલ માટે માઇગ્રેશન ચલાવવું હોય (દા.ત. `ups`, `ues`, `uv`, અથવા `us`):
```bash
# Ultra Primary School (UPS)
php artisan schools:migrate --school=ups

# Ultra Secondary School (US)
php artisan schools:migrate --school=us

# Ultra English School (UES)
php artisan schools:migrate --school=ues

# Ultra Vidhyalay (UV)
php artisan schools:migrate --school=uv
```

---

### C. Fresh Migration અને Seeding (ડેટાબેઝ રીસેટ કરવા માટે)

બધા ટેબલ્સ ડ્રોપ કરીને નવેસરથી માઇગ્રેશન + સીડર રન કરવા:
```bash
# બધી શાળાઓ માટે Fresh Migration + Seed
php artisan schools:migrate --fresh --seed

# માત્ર એક શાળા માટે Fresh Migration
php artisan schools:migrate --school=ues --fresh --seed
```

---

## ૪. ડેટા અને યુઝર્સ Sync કરવા માટે (Data Synchronization)

જો મૂળ ડેટાબેઝમાંથી તમામ સ્કૂલોમાં જરૂરી Users, Roles, Permissions અને Master Tables કોપી કરવા હોય:

```bash
# મૂળ ultra_school_ues અથવા ultra_school માંથી બધી સ્કૂલમાં ડેટા કોપી કરવા
php artisan schools:sync-initial --from=ultra_school_ues
```

---

## ૫. નવું માઇગ્રેશન બનાવતી વખતે Workflow

જ્યારે પણ તમે નવું માઇગ્રેશન બનાવો:

1. **નવું Migration Create કરો:**
   ```bash
   php artisan make:migration add_new_column_to_table
   ```
2. **Migration ફાઈલ કોડિંગ પૂર્ણ કરો.**
3. **બધી શાળાઓમાં Migration રન કરો:**
   ```bash
   php artisan schools:migrate
   ```

---

## ૬. જરૂરી Cache & Config Clear Commands

કોઈપણ મોટા ફેરફાર પછી કેશ ક્લિયર કરવા માટે:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```
