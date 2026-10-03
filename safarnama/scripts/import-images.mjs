// Runs after `npm install`: downloads any remote images the site uses into
// storage/app/public/media and points the content at the local copies.
// It never fails the install — if Laravel isn't ready yet it just skips.
import { existsSync } from 'node:fs';
import { spawnSync } from 'node:child_process';

if (process.env.SKIP_IMAGE_IMPORT) {
    console.log('[images] SKIP_IMAGE_IMPORT is set — skipping image import.');
    process.exit(0);
}

if (!existsSync('vendor/autoload.php') || !existsSync('.env')) {
    console.log('[images] Laravel is not installed yet (run composer install and create .env) — skipping image import.');
    process.exit(0);
}

const php = process.env.PHP_BINARY || 'php';
const result = spawnSync(php, ['artisan', 'images:import', '--no-interaction'], { stdio: 'inherit' });

if (result.error || result.status !== 0) {
    console.log('[images] Image import did not finish. Run `php artisan images:import` or use Admin → Media library later.');
}
process.exit(0);
