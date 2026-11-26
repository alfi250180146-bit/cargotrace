<?php

return [
    // Open-Meteo gratis dan tidak membutuhkan API key.
    // Alamatnya boleh diganti lewat .env bila perlu.
    'url' => env('OPEN_METEO_URL', 'https://api.open-meteo.com'),
    'timeout' => 5,
];