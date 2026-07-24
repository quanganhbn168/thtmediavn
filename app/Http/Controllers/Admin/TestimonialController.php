<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexTestimonialRequest;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(private readonly TestimonialService $testimonialService) {}

    public function index(IndexTestimonialRequest $request): View
    {
        return view('admin.testimonials.index', [
            'testimonials' => $this->testimonialService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.testimonials.create', ['testimonial' => new Testimonial]);
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $testimonial = $this->testimonialService->create($request->validated());

        return redirect()->route('admin.testimonials.edit', $testimonial)->with('success', 'Đã tạo cảm nhận khách hàng.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', ['testimonial' => $testimonial->load('media')]);
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->testimonialService->update($testimonial, $request->validated());

        return back()->with('success', 'Đã cập nhật cảm nhận khách hàng.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->testimonialService->delete($testimonial);

        return back()->with('success', 'Đã xóa cảm nhận khách hàng.');
    }
}
