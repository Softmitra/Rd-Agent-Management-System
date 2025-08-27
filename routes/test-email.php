<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function () {
    try {
        Mail::raw('Test email from RD Agent System', function($message) {
            $message->to('softmitrapvtltd@gmail.com')->subject('Test Email from RD Agent');
        });
        
        return response()->json(['success' => true, 'message' => 'Email sent successfully!']);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
});
