<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Review extends Model
{
    protected $fillable=['reviewable_type','reviewable_id','product_id','user_id','order_item_id','name','rating','content','images','status','is_verified'];
    protected $casts=['rating'=>'integer','images'=>'array','is_verified'=>'boolean'];
    public function product():BelongsTo{return $this->belongsTo(Product::class);}
    public function user():BelongsTo{return $this->belongsTo(User::class);}
    public function orderItem():BelongsTo{return $this->belongsTo(OrderItem::class);}
}
