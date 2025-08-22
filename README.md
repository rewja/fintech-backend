# 🏦 Fintech App

A simple **Fintech application** built with **Laravel + MySQL**.  
This project simulates a mini bank system of SMKN 10 Jakarta with roles:
- **Admin**
- **Bank**
- **Canteen**
- **BC (Bussiness Center)**
- **Student**

---

## ⚡ Quick Start

### 1️⃣ Clone Project

```bash
git clone https://github.com/username/fintech-app.git
cd backend
```

### 2️⃣ Setup Environment

Copy .env.example to .env:
```bash
cp .env.example .env
```

Configure database in .env:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fintech
DB_USERNAME=root
DB_PASSWORD=
```

### 3️⃣ Install Dependencies
```bash
composer install
```

### 4️⃣ Run Migration
```bash
php artisan migrate
```

###5️⃣ Seed data
```bash
php artisan db:seed
```
