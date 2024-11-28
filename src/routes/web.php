<?php


use Ghadeer\LaraMailPreview\Http\Controllers\MailPreviewController;
use Ghadeer\LaraMailPreview\MailPreview;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Route;



Route::get('mail-preview', [MailPreviewController::class, 'showForm'])->name('mail.preview.form');
Route::post('mail-preview', [MailPreviewController::class, 'handleForm'])->name('mail.preview.submit');
Route::post('mail-render', [MailPreviewController::class, 'render'])->name('mail.preview.render');
