<?php


use Ghadeer\LaraMailPreview\Http\Controllers\MailPreviewController;
use Ghadeer\LaraMailPreview\MailPreview;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Route;



Route::get('email-preview', [MailPreviewController::class, 'showForm'])->name('email.preview.form');
Route::post('email-preview', [MailPreviewController::class, 'handleForm'])->name('email.preview.submit');
Route::post('email-render', [MailPreviewController::class, 'render'])->name('email.preview.render');

Route::get('email-preview/{mailable}', function ($mailable) {
    $mailableClass = 'App\\Mail\\' . $mailable;
    if (class_exists($mailableClass)) {
        $mailableInstance = new $mailableClass();
        return MailPreview::render($mailableInstance);
    }

    throw new FileNotFoundException();
});
