<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ContactController extends Controller {
    public const STATUSES=['new'=>'Mới','read'=>'Đã đọc','processing'=>'Đang xử lý','replied'=>'Đã phản hồi','spam'=>'Spam'];
    public function index(Request $request):View{$data=$request->validate(['search'=>'nullable|string|max:100','status'=>'nullable|in:new,read,processing,replied,spam','per_page'=>'nullable|integer|in:10,25,50']);$query=Contact::query();$search=trim((string)($data['search']??''));if($search!=='')$query->where(fn($q)=>$q->where('name','like',"%{$search}%")->orWhere('phone','like',"%{$search}%")->orWhere('email','like',"%{$search}%")->orWhere('subject','like',"%{$search}%"));if(!empty($data['status']))$query->where('status',$data['status']);return view('admin.contacts.index',['contacts'=>$query->latest()->paginate((int)($data['per_page']??10))->withQueryString(),'statuses'=>self::STATUSES]);}
    public function edit(Contact $contact):View{if($contact->status==='new')$contact->update(['status'=>'read']);return view('admin.contacts.edit',['contact'=>$contact,'statuses'=>self::STATUSES]);}
    public function update(UpdateContactRequest $request,Contact $contact):RedirectResponse{$contact->update($request->validated());return redirect()->route('admin.contacts.index')->with('success','Đã cập nhật tin nhắn liên hệ.');}
    public function destroy(Contact $contact):RedirectResponse{$contact->delete();return back()->with('success','Đã xóa tin nhắn.');}
}
