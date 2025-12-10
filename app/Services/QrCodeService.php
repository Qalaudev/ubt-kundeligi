<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generateForTopic(int $topicId, string $baseUrl): string
    {
        $url = $baseUrl . '/quiz/' . $topicId;
        $filename = 'qr/topic_' . $topicId . '.png';
        $path = storage_path('app/public/' . $filename);

        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate QR code
        QrCode::format('png')
            ->size(300)
            ->generate($url, $path);

        // Return the public path
        return 'storage/' . $filename;
    }
}






