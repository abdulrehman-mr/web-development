<?php

namespace App\Console\Commands;

use App\Support\ImageImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('images:import {--retry-failed : Also retry images that failed before} {--limit=0 : Maximum number of images to download} {--no-localize : Download only; keep remote URLs in the content}')]
#[Description('Download remote images used by the site to local storage and point the content at the local copies')]
class ImportImages extends Command
{
    public function handle(ImageImporter $importer): int
    {
        $new = $importer->scan();
        $queue = $importer->queue($this->option('retry-failed'));
        if ($limit = (int) $this->option('limit')) {
            $queue = $queue->take($limit);
        }

        $this->components->info("Found {$new} new remote image(s); {$queue->count()} to download.");

        $ok = $failed = 0;
        foreach ($queue as $asset) {
            $label = str($asset->source_url)->limit(100)->toString();
            if ($importer->download($asset)) {
                $ok++;
                $this->line("  <fg=green>✓</> {$label}");
            } else {
                $failed++;
                $this->line("  <fg=red>✗</> {$label}");
                $this->line("    <fg=gray>{$asset->error}</>");
            }
        }

        $summary = "Downloaded {$ok}, failed {$failed}.";
        if (! $this->option('no-localize')) {
            $summary .= ' Updated '.$importer->localize().' content field(s) to use local files.';
        }
        $this->components->info($summary);

        return self::SUCCESS;
    }
}
