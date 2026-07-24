<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContactChannel extends Model {
    protected $fillable=['name','type','value','url','icon','is_primary','show_topbar','show_footer','show_floating','is_active','sort_order'];
    protected $casts=['is_primary'=>'boolean','show_topbar'=>'boolean','show_footer'=>'boolean','show_floating'=>'boolean','is_active'=>'boolean','sort_order'=>'integer'];
    public function getLinkAttribute():string { if($this->url)return $this->url;return match($this->type){'phone'=>'tel:'.preg_replace('/[^0-9+]/','',$this->value),'email'=>'mailto:'.$this->value,'zalo'=>'https://zalo.me/'.preg_replace('/[^0-9]/','',$this->value),default=>$this->value}; }
}
