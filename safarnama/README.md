# Safarnama: Pakistan travel website and CMS

A premium, responsive travel website for a Pakistan tour operator that serves guests from abroad (mostly Canada, the UK and the US). It is built with **Laravel 13**, **Tailwind CSS v4** and **Alpine.js**, and it has a full admin panel and a JSON API.

## Features

**Public website**
- Home page with a journey finder, experiences, featured journeys, a Sikh Yatra spotlight, destinations, steps, reviews and an FAQ
- Journeys list with filters for experience, destination, length, sort order and text search
- Journey detail page with:
  - a day-by-day itinerary showing where you stay each night and which meals are included
  - "Where you'll stay" cards, what is and isn't included, a gallery lightbox and reviews
  - a booking form that shows a live price estimate in USD and approximate CAD
- Destinations grouped by region, a page for each destination and a "Stays" page for hotels
- About and contact pages (contact has a honeypot spam trap), plus a booking confirmation page
- A floating WhatsApp button, a mobile booking bar and an accessible mobile menu

**Admin panel** (`/admin`)
- Dashboard with booking counts, the open pipeline value, the latest bookings and enquiries
- **Journeys:** full editor with an itinerary builder (add, reorder and remove days, and pick a hotel and meals for each day), highlights and inclusions, photo upload or URL, gallery, featured and published switches
- **Destinations**, **Stays (hotels)**, **Experiences (categories)**, **Reviews**, **FAQs:** add, edit and delete
- **Bookings:** filter by status, search, change status, add a quoted total and internal notes, and email or WhatsApp the guest
- **Enquiries** inbox with read/unread state
- **Site content:** hero text and image, story, stats, about page, contact details, USD→CAD rate, social links and logo

**JSON API** (`/api/v1`)
| Method | Endpoint | |
|---|---|---|
| GET | `/settings` | Public site content |
| GET | `/categories` | Experiences |
| GET | `/destinations`, `/destinations/{slug}` | Destinations, with their journeys and hotels |
| GET | `/packages?category=&featured=1&per_page=` | Journeys (paginated) |
| GET | `/packages/{slug}` | A full journey, including the itinerary and stays |
| GET | `/hotels`, `/testimonials`, `/faqs` | |
| POST | `/bookings` | `package` (slug), `full_name`, `email`, `adults`, `start_date`, … |
| POST | `/inquiries` | `name`, `email`, `message`, … |

The POST endpoints are rate-limited.

## Getting started

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite          # or set DB_* in .env for MySQL
php artisan migrate --seed
php artisan storage:link
npm install && npm run build            # or `npm run dev` while developing
php artisan serve
```

Open http://127.0.0.1:8000 for the website and http://127.0.0.1:8000/admin for the admin panel.

**Default admin login:** `admin@safarnama.test` / `password`. **Change this before going live.**

Run the tests with `php artisan test`.

## Brand

| Token | Hex | Where it comes from |
|---|---|---|
| Pine 900 | `#0e2c25` | The green of the national flag and the Shalimar Gardens |
| Brass 400 | `#d3a955` | Gilded Mughal metalwork and truck-art trim |
| Marble 50 | `#fbf9f4` | The white marble of the Badshahi Mosque domes |
| Sandstone 500 | `#a5452f` | The red sandstone of Lahore Fort (used sparingly) |

- **Typography:** Cormorant Garamond for display text and Manrope for body text.
- **Signature motif:** a pointed Mughal arch frames the photos (the `.arch` class), with a subtle jali lattice pattern behind them.

## Content and images

The seed data includes six journeys, fourteen destinations, thirteen stays, reviews and FAQs. All of it is sample content and can be edited in the admin panel. Hotel names, phone numbers and statistics are placeholders, so check them before you launch.

Seed images point at Wikimedia Commons file names. **Replace them with your own photography** by uploading photos in the admin panel. Every image sits on a branded fallback, so a missing photo never shows as a broken image.
