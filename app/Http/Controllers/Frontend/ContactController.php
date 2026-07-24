<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Gửi liên hệ từ người dùng
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
            'website' => 'nullable|max:0',
        ]);

        unset($validated['website']);

        Contact::create($validated);

        return redirect()->back()->with('success', 'Cảm ơn bạn đã gửi liên hệ. Chúng tôi sẽ phản hồi sớm nhất!');
    }
}
