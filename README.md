# Will you go on a date with me?

A tiny PHP app: ask the question, the "No" button runs away, then they pick a day, time and activity.

## Run it locally

```bash
php -S localhost:8000 router.php
```

Then open http://localhost:8000

## Make it yours

Edit `config.php`:
- `ASKER_NAME` – your name (or leave as "me")
- `ADMIN_PASSWORD` – **change this** before putting it online
- time slots, quips and activities

## See who said yes

Open `/responses.php` and log in with `ADMIN_PASSWORD`. Answers are stored in `data/responses.json`
(blocked from the web by `data/.htaccess` on Apache; on Nginx, deny `/data/` yourself).

## Host on Render (free)

1. Push this folder to a **private** GitHub repo (it contains your photos and passwords).
2. On render.com: New → Web Service → connect the repo. Render reads the `Dockerfile`; pick the Free plan.
3. Install the **ntfy** app on your phone and subscribe to the topic in `NTFY_TOPIC` (config.php).
   You get a push when she says yes and when she picks a date.

Render's free plan sleeps after 15 minutes idle (the first visit then takes ~1 minute to load)
and wipes `data/responses.json` when it does, so the ntfy notifications are the reliable record.
