<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email from RD Agent System', function($message) {
        $message->to('softmitrapvtltd@gmail.com')->subject('Test Email from RD Agent');
    });
    
    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Error sending email: " . $e->getMessage() . "\n";
}
