<?php

use App\Mail\EnquiryMail;
use App\Models\Enquiry;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    config(['site.contact.recipient' => 'admin@swvh.org.uk', 'site.contact.cc' => 'copy@example.com']);
});

it('saves the enquiry and emails it to the hall, cc the copy address, reply-to the sender', function () {
    $this->from('/contact')->post('/contact', [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'subject' => '  Hiring   the hall ',
        'message' => 'Is the hall free on 1 May?',
    ])->assertRedirect('/contact')->assertSessionHas('enquiry_sent', true);

    $enquiry = Enquiry::sole();
    expect($enquiry->subject)->toBe('Hiring the hall');

    Mail::assertSent(EnquiryMail::class, fn (EnquiryMail $mail) => $mail->hasTo('admin@swvh.org.uk')
        && $mail->hasCc('copy@example.com')
        && $mail->hasReplyTo('ada@example.com')
        && $mail->hasSubject('Hiring the hall'));
});

it('gives a blank subject the default', function () {
    $this->post('/contact', ['name' => 'Ada', 'email' => 'ada@example.com', 'message' => 'Hello']);

    expect(Enquiry::sole()->subject)->toBe('Spencers Wood Village Hall contact enquiry from Ada');
});

it('renders the email with the enquiry details', function () {
    $enquiry = Enquiry::create(['name' => 'Ada', 'email' => 'ada@example.com', 'subject' => 'Hi', 'message' => "Line one\nLine two"]);

    (new EnquiryMail($enquiry))
        ->assertSeeInHtml('ada@example.com')
        ->assertSeeInText('Name: Ada')
        ->assertSeeInText('Line two');
});

it('fakes success for bots that fill the honeypot', function () {
    $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'Spam', 'website' => 'http://spam.test'])
        ->assertSessionHas('enquiry_sent', true);

    expect(Enquiry::count())->toBe(0);
    Mail::assertNothingSent();
});

it('validates the form', function () {
    $this->post('/contact', ['name' => '', 'email' => 'nope', 'message' => str_repeat('a', 5001), 'subject' => str_repeat('b', 201)])
        ->assertSessionHasErrors(['name', 'email', 'message', 'subject']);
});

it('keeps the enquiry when the email fails to send', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('Resend is down'));

    $this->post('/contact', ['name' => 'Ada', 'email' => 'ada@example.com', 'message' => 'Hello'])
        ->assertSessionHas('enquiry_sent', true);

    expect(Enquiry::count())->toBe(1);
});

it('throttles the form', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/contact', ['name' => 'Ada', 'email' => 'ada@example.com', 'message' => "Hello {$attempt}"]);
    }

    $this->post('/contact', ['name' => 'Ada', 'email' => 'ada@example.com', 'message' => 'Hello again'])->assertTooManyRequests();
});

it('shows the thank-you panel after sending', function () {
    $this->withSession(['enquiry_sent' => true])->get('/contact')
        ->assertInertia(fn ($page) => $page->where('enquirySent', true));
});
