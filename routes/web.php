<?php

use Illuminate\Support\Facades\Route;

// Redirect root to simple widget
Route::get('/', function () {
    return redirect('/simple-widget.html');
});
