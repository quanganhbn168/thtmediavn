<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subscriber\UpdateSubscriberRequest;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50'],
        ]);

        $query = Subscriber::query();

        if ($search = trim((string) ($data['search'] ?? ''))) {
            $query->where(fn ($query) => $query
                ->where('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }

        if (($data['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }

        if (($data['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.subscribers.index', [
            'subscribers' => $query->latest()->paginate((int) ($data['per_page'] ?? 10))->withQueryString(),
        ]);
    }

    public function edit(Subscriber $subscriber): View
    {
        return view('admin.subscribers.edit', compact('subscriber'));
    }

    public function update(UpdateSubscriberRequest $request, Subscriber $subscriber): RedirectResponse
    {
        $subscriber->update([
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.subscribers.index')->with('success', 'Đã cập nhật người đăng ký.');
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Đã xóa người đăng ký.');
    }
}
