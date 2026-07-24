<?php
namespace Database\Seeders;
use App\Models\ContactChannel;
use App\Settings\ContactSettings;
use Illuminate\Database\Seeder;
class ContactChannelSeeder extends Seeder {
    public function run():void{$settings=app(ContactSettings::class);$rows=array_filter([
        $settings->phone?['name'=>'Số điện thoại chính','type'=>'phone','value'=>$settings->phone,'is_primary'=>true,'show_topbar'=>true,'show_footer'=>true,'show_floating'=>true,'is_active'=>true,'sort_order'=>10]:null,
        $settings->zalo?['name'=>'Zalo tư vấn','type'=>'zalo','value'=>$settings->zalo,'url'=>str_starts_with($settings->zalo,'http')?$settings->zalo:null,'show_footer'=>true,'show_floating'=>true,'is_active'=>true,'sort_order'=>20]:null,
        $settings->email?['name'=>'Email','type'=>'email','value'=>$settings->email,'show_footer'=>true,'is_active'=>true,'sort_order'=>30]:null,
    ]);foreach($rows as $row)ContactChannel::query()->firstOrCreate(['type'=>$row['type'],'value'=>$row['value']],$row);}
}
