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
- **Site content:** hero text and image, story, stats, about page, contact details, social links and logo
- **Currencies:** all 166 ISO 4217 currencies. Choose which currency prices are entered in (the base), the default currency for visitors and an optional "about CA$…" second price. Enable the currencies visitors can switch to, and edit each symbol and its position. Exchange rates update daily, from the "Update rates now" button, or by hand.
- **Payments:** Stripe, PayPal, Authorize.net, 2Checkout and CoinPayments, each with on/off, test/live mode and encrypted keys. Set the deposit percentage, then copy or email a secure payment link from any booking.
- **Appearance:** six colour schemes (Pakistan Green, Indus Blue, Karakoram Teal, Truck Art Red, Royal Violet, Midnight Slate) or your own two colours, with a live preview

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

## Payments

Guests request a journey, then pay a deposit or the full amount on `/booking/{reference}/pay`. Every provider uses its own hosted checkout, so card details never reach this server. A payment is only marked paid after the server confirms it: Stripe and PayPal through their APIs on return, and every provider through its signed webhook.

| Provider | Checkout | Confirmation | Currency |
|---|---|---|---|
| Stripe | Checkout Session | API lookup on return + `checkout.session.*` webhook | Guest's currency |
| PayPal | Orders v2 | Capture on return (+ optional verified webhook) | Guest's currency if PayPal supports it, otherwise the base currency |
| Authorize.net | Accept Hosted form | Signed webhook + `getTransactionDetails` | Your merchant account currency |
| 2Checkout | Signed ConvertPlus buy link | Signed IPN | Guest's currency |
| CoinPayments | Simple checkout (guest picks the coin) | HMAC-signed IPN | Guest's currency |

Paste each provider's webhook URL from **Admin → Payments** into that provider's dashboard. **Test every provider in its sandbox before going live.** The integrations are covered by automated tests against mocked APIs, but they have not been run against the real provider services.

Exchange rates update daily through Laravel's scheduler, so add the standard cron entry on your server:
`* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`

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

The site uses a cool, crisp palette in the style of modern travel products such as Airbnb, Booking and the Tailwind travel templates. It deliberately avoids the cream, brown and gold look that reads as AI-generated.

| Token | Hex | Use |
|---|---|---|
| Brand 900 | `#01411C` | The official Pakistan flag green, used for the footer and featured sections |
| Brand 700 | `#097045` | Buttons and links |
| Lake 300 / 700 | `#67E4F6` / `#0E6F89` | Attabad Lake turquoise, used sparingly for highlights on dark backgrounds |
| Slate | Tailwind's `slate` | Text, borders and surfaces, on a white background |

- **Typography:** Plus Jakarta Sans for everything, with headings set extra-bold and tightly spaced.
- **Signature shape:** a pointed Mughal arch (the `.arch` class) appears only on the home page hero and the destination tiles.

## Images: importing them to your server

Content can reference images by URL, for example Wikimedia Commons, Unsplash or your old website. The **image importer** copies those images into `storage/app/public/media` and switches the content to the local copies. After that, the live site no longer depends on another website.

It runs in three ways:

1. **Automatically on deploy.** `npm install` runs `scripts/import-images.mjs` as a `postinstall` step, which calls `php artisan images:import`. The step skips itself if Laravel isn't installed yet and never fails the install. Set `SKIP_IMAGE_IMPORT=1` to turn it off.
2. **From the command line.**
   ```bash
   php artisan images:import                 # download new and missing images, then use the local copies
   php artisan images:import --retry-failed  # also retry earlier failures
   php artisan images:import --limit=10      # download in smaller batches
   ```
3. **From the admin panel.** Go to **Admin → Media library**:
   - paste image URLs, one per line
   - click **Import now** to download them in small batches with a progress bar
   - click **Retry failed** to try again
   - click **Copy local path** to use an imported image in any image field

Every remote URL you save in an admin form is added to the library automatically. The library keeps each image's original URL, so if a file goes missing on the server, for example after a fresh deploy, the next import downloads it again. Run `php artisan storage:link` once on each server so `/storage/media/...` is publicly reachable.

## Content

The seed data includes six journeys, fourteen destinations, thirteen stays, reviews and FAQs. All of it is sample content and can be edited in the admin panel. Hotel names, phone numbers and statistics are placeholders, so check them before you launch.

Seed photos point at real Wikimedia Commons files (CC BY-SA licensed, so credit the photographers if you keep them). Replace them with your own photography when you can. Every photo sits on a branded fallback, so a missing image never shows as broken.
