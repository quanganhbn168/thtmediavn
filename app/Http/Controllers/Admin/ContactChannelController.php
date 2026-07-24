<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactChannelRequest;
use App\Models\ContactChannel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class ContactChannelController extends Controller {
    public const TYPES=['phone'=>'Điện thoại','zalo'=>'Zalo','messenger'=>'Messenger','email'=>'Email','whatsapp'=>'WhatsApp','other'=>'Khác'];
    public function index():View{return view('admin.settings.contact_channels.index',['channels'=>ContactChannel::query()->orderBy('sort_order')->orderBy('id')->get(),'types'=>self::TYPES]);}
    public function create():View{return view('admin.settings.contact_channels.create',['types'=>self::TYPES]);}
    public function store(ContactChannelRequest $request):RedirectResponse{$channel=ContactChannel::create($this->payload($request));$this->syncPrimary($channel);return redirect()->route('admin.settings.contact-channels.index')->with('success','Đã thêm liên hệ.');}
    public function edit(ContactChannel $contactChannel):View{return view('admin.settings.contact_channels.edit',['channel'=>$contactChannel,'types'=>self::TYPES]);}
    public function update(ContactChannelRequest $request,ContactChannel $contactChannel):RedirectResponse{$contactChannel->update($this->payload($request));$this->syncPrimary($contactChannel);return redirect()->route('admin.settings.contact-channels.index')->with('success','Đã cập nhật liên hệ.');}
    public function destroy(ContactChannel $contactChannel):RedirectResponse{$contactChannel->delete();return back()->with('success','Đã xóa liên hệ.');}
    private function payload(ContactChannelRequest $request):array{$data=$request->validated();foreach(['is_primary','show_topbar','show_footer','show_floating','is_active'] as $field)$data[$field]=$request->boolean($field);$data['sort_order']=$data['sort_order']??0;return $data;}
    private function syncPrimary(ContactChannel $channel):void{if($channel->is_primary)ContactChannel::query()->whereKeyNot($channel->id)->update(['is_primary'=>false]);}
}
