# 🚀 Laravel To-Do API Application

Приложение полностью упаковано в Docker. Для его запуска **не требуются** установленные PHP, Composer или PostgreSQL на вашем компьютере. Поддерживается работа на Windows (включая WSL2), macOS и Linux.

## 🛠️ Быстрый старт (Развертывание)

### 1. Клонируйте репозиторий
```bash
git clone https://github.com/damirco/todo-api
cd todo-api
```

### 2. Запустите проект (Всего одна команда)
Выполните в терминале (PowerShell, CMD, Terminal):
```bash
docker compose up -d --build
```

> ⏳ **Обратите внимание:** При первом запуске Docker сам создаст `.env` из шаблона, развернет PHP 8.4 и PostgreSQL, установит все необходимые пакеты и применит миграции базы данных вместе с сидами. Это может занять около 1–2 минут.

### 3. Готово!
Приложение доступно по адресу: **[http://localhost:8000](http://localhost:8000)**

---

## 🧪 Тестирование

Чтобы запустить тесты (Feature) внутри изолированного окружения Docker, выполните:

```bash
docker compose exec app php artisan test
```

---

## 🐳 Полезные команды для управления
* **Остановить проект:** `docker compose down`
* **Посмотреть логи приложения:** `docker compose logs app -f`
* **Выполнить команду Artisan:** `docker compose exec app php artisan <команда>`
