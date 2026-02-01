# 🚀 Social Media Management Tool (SMMT)

A full-stack PHP dashboard to schedule, automate, and analyze posts for Facebook and Instagram using the Meta Graph API.

## ✨ Features
- **Automated Scheduling:** PHP Cron-worker logic for timed posts.
- **Direct API Integration:** Facebook & Instagram Graph API v24.0.
- **Live Analytics:** Engagement tracking (Likes/Comments) with Chart.js.
- **Reporting:** Exportable PDF performance reports.

## 🚀 Setup & Installation (Step-by-Step)

### 1. Database Configuration
- Import the provided `database.sql` into your MySQL (phpMyAdmin).
- Create a `db.php` file based on `db.sample.php` and enter your database credentials.

### 2. Meta API Setup
- Create an App at [Meta for Developers](https://developers.facebook.com/).
- Add **Facebook Login** and **Instagram Graph API** products.
- Obtain your **Access Token**, **App ID**, and **App Secret**.

### 3. Ngrok for Local Development
Since Meta requires a secure `https` callback URL for webhooks and image hosting:
- Download [ngrok](https://ngrok.com/).
- Run `ngrok http 80` (or your local port).
- Use the provided **forwarding URL** (e.g., `https://xyz.ngrok-free.app`) in your Meta App settings and for your `image_url` paths.

### 4. Running the Cron
- To automate posts, ensure the `cron_worker.php` is called via a system cron job or keep the dashboard open to trigger the auto-ping script.

## 🔒 Security
Sensitive credentials are kept in local configuration and are excluded from this repository.
