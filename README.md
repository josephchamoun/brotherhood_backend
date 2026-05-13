# Kfarhawra Brotherhood Platform — Backend

Laravel REST API powering the Kfarhawra Brotherhood platform. Handles authentication, role-based access control, and all data management for a real Lebanese community organization with three sections and 8 roles per section.

🔗 **Frontend:** https://kfarhaoura-brotherhood.vercel.app  
🔗 **Frontend Repo:** https://github.com/josephchamoun/kfarhawrabrotherhood

---

## About

The Brotherhood is divided into three sections: **Chabiba**, **Tala2e3**, and **Forsan**. Each section has up to **8 roles** (President, Amin Ser, Amin Sandou2, Moustachar, and others) assigned to members for a specific time period. Each role has different permissions enforced at the API level.

---

## Auth & Permissions

- Authentication via **Laravel Sanctum** (Bearer tokens)
- Only **Admins** can create users, assign roles, and assign members to sections
- Each role has specific API access — for example, only the Amin Sandou2 role can create or edit financial records
- All permission checks are enforced server-side on every request

---

## API Modules

| Module | Description |
|---|---|
| **Auth** | Login, logout, profile — Sanctum token-based |
| **Users** | Member management — admin only for create/assign |
| **Sections** | Chabiba, Tala2e3, Forsan membership management |
| **Roles** | Role assignment to members within sections |
| **Events** | Event creation, details, and financial records per event |
| **Moneyboxes** | Financial balance tracking per section and overall |
| **Meetings** | Meeting records per section |
| **Elections** | Election management per section |
| **Shops** | Shop listings |
| **Contacts** |  contact information of other brotherhoods and more |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel |
| Auth | Laravel Sanctum |
| Database | MySQL |
| Deployment | Render |

---

## Related Repository

- [Brotherhood Frontend (React + TypeScript)](https://github.com/josephchamoun/kfarhawrabrotherhood)
