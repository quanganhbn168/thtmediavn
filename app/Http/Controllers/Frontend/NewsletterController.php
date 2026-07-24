<?php
namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class NewsletterController extends Controller {
    public function store(Request $request):RedirectResponse{$data=$request->validate(['email'=>'required|email|max:150','website'=>'nullable|max:0']);Subscriber::query()->updateOrCreate(['email'=>$data['email']],['is_active'=>true]);return back()->with('newsletter_success','Đăng ký nhận tin thành công.');}
}
