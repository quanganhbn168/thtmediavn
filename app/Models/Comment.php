<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Comment extends Model {protected $fillable=['post_id','parent_id','name','email','content','status'];public function post():BelongsTo{return $this->belongsTo(Post::class);}public function parent():BelongsTo{return $this->belongsTo(self::class,'parent_id');}public function replies():HasMany{return $this->hasMany(self::class,'parent_id')->where('status','approved')->oldest();}}
