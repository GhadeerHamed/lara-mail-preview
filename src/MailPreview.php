<?php

namespace Ghadeer\LaraMailPreview;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Mailable;
use ReflectionException;

class MailPreview
{

    /**
     * @param Mailable $mailable
     * @return Factory|View|Application|\Illuminate\View\View
     * @throws ReflectionException
     */
    public static function render(Mailable $mailable)
    {
        return view('live-email-preview::preview', [
            'html' => $mailable->render(),
        ]);
    }
}
