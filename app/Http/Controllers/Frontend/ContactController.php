<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitContactRequest;
use App\Mail\ContactRequestReceived;
use App\Models\Contact;
use App\Models\Service;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('frontend.contact', [
            'consultingServices' => Service::query()->where('is_active', true)->orderBy('sort_order')->get()
                ->mapWithKeys(fn (Service $service) => [$service->id => $service->getTranslation('name', app()->getLocale())])->all(),
        ]);
    }

    /**
     * Gửi liên hệ từ người dùng
     */
    public function submit(SubmitContactRequest $request)
    {
        $validated = $request->validated();

        unset($validated['website']);
        unset($validated['attachment']);

        $contact = Contact::query()->create($validated);
        if ($request->hasFile('attachment')) {
            $contact->addMediaFromRequest('attachment')->toMediaCollection('brief_attachment', 'public_media');
        }

        if (filled($recipient = config('contact.notification_email'))) {
            try {
                Mail::to($recipient)->send(new ContactRequestReceived($contact->loadMissing('service')));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->back()->with('success', 'THT Media đã nhận yêu cầu. Đội ngũ tư vấn sẽ liên hệ để làm rõ mục tiêu và phạm vi dự án.');
    }
}
