<?php
namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:150', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'website' => ['nullable', 'max:0'],
        ]);

        $email = filled($data['email'] ?? null) ? mb_strtolower(trim($data['email'])) : null;
        $phone = filled($data['phone'] ?? null) ? preg_replace('/\D+/', '', $data['phone']) : null;

        $subscriber = Subscriber::query()->firstOrNew($phone ? ['phone' => $phone] : ['email' => $email]);
        $subscriber->fill([
            'email' => $email ?: $subscriber->email,
            'phone' => $phone ?: $subscriber->phone,
            'is_active' => true,
        ])->save();

        return back()->with('newsletter_success', $phone
            ? 'Đã đăng ký nhận ưu đãi qua Zalo.'
            : 'Đăng ký nhận tin thành công.');
    }
}
