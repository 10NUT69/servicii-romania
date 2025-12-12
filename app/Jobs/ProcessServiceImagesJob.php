<?php

namespace App\Jobs;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessServiceImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $serviceId) {}

    public function handle(): void
    {
        /*
         |--------------------------------------------------------------------------
         | 1) Lock pe service
         |--------------------------------------------------------------------------
         */
        $service = DB::transaction(function () {
            return Service::where('id', $this->serviceId)
                ->lockForUpdate()
                ->firstOrFail();
        });

        // Dacă nu are nimic în tmp, nu avem ce procesa
        $imagesTmp = $service->images_tmp;
        if (is_string($imagesTmp)) {
            $imagesTmp = json_decode($imagesTmp, true);
        }
        if (!is_array($imagesTmp) || count($imagesTmp) === 0) {
            return;
        }

        /*
         |--------------------------------------------------------------------------
         | 2) Pregătim directoare + manager
         |--------------------------------------------------------------------------
         */
        $manager  = new ImageManager(new Driver());
        $tmpDir   = storage_path("app/services-tmp/{$service->id}");
        $finalDir = storage_path("app/public/services");

        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        /*
         |--------------------------------------------------------------------------
         | 3) Păstrăm pozele existente și adăugăm pe cele noi până la max 10
         |--------------------------------------------------------------------------
         */
        $currentImages = $service->images;
        if (is_string($currentImages)) {
            $currentImages = json_decode($currentImages, true);
        }
        if (!is_array($currentImages)) {
            $currentImages = [];
        }

        // SEO base name (similar cu publish)
        $words = Str::of($service->title)->explode(' ')->take(5)->implode(' ');
        $seoBaseName = Str::slug($words);

        foreach ($imagesTmp as $tmpName) {
            if (count($currentImages) >= 10) break;

            $tmpPath = $tmpDir . '/' . $tmpName;
            if (!file_exists($tmpPath)) continue;

            $finalName = $seoBaseName . '-' . Str::random(6) . '.jpg';
            $finalPath = $finalDir . '/' . $finalName;

            $manager
                ->read($tmpPath)
                ->scaleDown(1600)
                ->toJpeg(75)
                ->save($finalPath);

            $currentImages[] = $finalName;

            @unlink($tmpPath);
        }

        // Curățăm folderul tmp (dacă e gol)
        if (is_dir($tmpDir)) {
            @rmdir($tmpDir);
        }

        /*
         |--------------------------------------------------------------------------
         | 4) Salvare finală: update imagini + curățare tmp
         |    IMPORTANT: status rămâne active (nu ascundem anunțul)
         |--------------------------------------------------------------------------
         */
        $service->images      = $currentImages;
        $service->images_tmp  = null;
        $service->fail_reason = null;
        $service->save();
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ProcessServiceImagesJob failed for service {$this->serviceId}", [
            'error' => $e->getMessage(),
        ]);

        Service::where('id', $this->serviceId)->update([
            'fail_reason' => $e->getMessage(),
        ]);
    }
}
