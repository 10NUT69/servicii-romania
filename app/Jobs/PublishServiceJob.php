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

class PublishServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $serviceId) {}

    public function handle(): void
    {
        /*
         |--------------------------------------------------------------------------
         | 1. Lock pe service (un singur worker procesează)
         |--------------------------------------------------------------------------
         */
        $service = DB::transaction(function () {
            $service = Service::where('id', $this->serviceId)
                ->lockForUpdate()
                ->firstOrFail();

            // Dacă e deja publicat, ieșim
            if ($service->status === 'active') {
                return $service;
            }

            // Acceptăm doar pending / rejected (retry)
            if (!in_array($service->status, ['pending', 'rejected'], true)) {
                return $service;
            }

            $service->status = 'pending'; // rămâne pending până la final
            $service->save();

            return $service;
        });

        if ($service->status === 'active') {
            return;
        }

        /*
         |--------------------------------------------------------------------------
         | 2. Procesare imagini
         |--------------------------------------------------------------------------
         */
        $manager = new ImageManager(new Driver());

        $tmpDir   = storage_path("app/services-tmp/{$service->id}");
        $finalDir = storage_path("app/public/services");

        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        $imagesTmp = $service->images_tmp;
        if (is_string($imagesTmp)) {
            $imagesTmp = json_decode($imagesTmp, true);
        }
        if (!is_array($imagesTmp)) {
            $imagesTmp = [];
        }

        $finalImages = [];

        // SEO base name (identic cu store)
        $words = Str::of($service->title)->explode(' ')->take(5)->implode(' ');
        $seoBaseName = Str::slug($words);

        foreach ($imagesTmp as $tmpName) {
            $tmpPath = $tmpDir . '/' . $tmpName;
            if (!file_exists($tmpPath)) {
                continue;
            }

            if (count($finalImages) >= 10) {
                break;
            }

            $finalName = $seoBaseName . '-' . Str::random(6) . '.jpg';
            $finalPath = $finalDir . '/' . $finalName;

            $manager
                ->read($tmpPath)
                ->scaleDown(1600)
                ->toJpeg(75)
                ->save($finalPath);

            $finalImages[] = $finalName;

            @unlink($tmpPath);
        }

        // Curățăm folderul tmp
        if (is_dir($tmpDir)) {
            @rmdir($tmpDir);
        }

        /*
         |--------------------------------------------------------------------------
         | 3. Publicare finală
         |--------------------------------------------------------------------------
         */
        $service->images       = $finalImages;
        $service->images_tmp   = null;
        $service->status       = 'active';
        $service->published_at = now();
        $service->fail_reason  = null;
        $service->save();
    }

    public function failed(\Throwable $e): void
    {
        Log::error(
            "PublishServiceJob failed for service {$this->serviceId}",
            ['error' => $e->getMessage()]
        );

        Service::where('id', $this->serviceId)->update([
            'status'      => 'rejected',
            'fail_reason' => $e->getMessage(),
        ]);
    }
}
