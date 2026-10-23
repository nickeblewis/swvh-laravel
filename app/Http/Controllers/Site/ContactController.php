<?php

namespace App\Http\Controllers\Site;

use App\Mail\EnquiryMail;
use App\Models\Enquiry;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Response;
use Throwable;

class ContactController extends SiteController
{
    public function show(): Response
    {
        return $this->page('Contact', [], new Seo(
            title: 'Contact',
            description: 'Get in touch with '.config('site.name').'.',
        ));
    }

    /**
     * The enquiry form, posted from `/contact` and from admin pages with the
     * contact template. Goes back to the page it came from, which then shows
     * the thank-you panel.
     */
    public function store(Request $request): RedirectResponse
    {
        // A field people never see; bots fill it in. They get a fake success.
        if (filled($request->input('website'))) {
            return back()->with('enquiry_sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $enquiry = Enquiry::create([
            ...$data,
            'subject' => Str::squish($data['subject'] ?? '') ?: config('site.name')." contact enquiry from {$data['name']}",
        ]);

        // The enquiry is saved either way, so a failed send is logged and the
        // visitor still sees the thank-you panel.
        try {
            Mail::to(config('site.contact.recipient'))
                ->cc(array_filter([config('site.contact.cc')]))
                ->send(new EnquiryMail($enquiry));
        } catch (Throwable $e) {
            report($e);
        }

        return back()->with('enquiry_sent', true);
    }
}
