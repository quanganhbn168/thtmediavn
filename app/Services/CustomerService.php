<?php
namespace App\Services;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
class CustomerService {
    public const GENDERS=['male'=>'Nam','female'=>'Nữ','other'=>'Khác'];
    public function paginate(array $filters):LengthAwarePaginator{$query=Customer::query()->withCount('orders');$search=trim((string)($filters['search']??''));if($search!=='')$query->where(fn(Builder $q)=>$q->where('name','like',"%{$search}%")->orWhere('phone','like',"%{$search}%")->orWhere('email','like',"%{$search}%"));if(($filters['status']??null)==='active')$query->where('is_active',true);if(($filters['status']??null)==='inactive')$query->where('is_active',false);if(!empty($filters['gender']))$query->where('gender',$filters['gender']);$columns=['newest'=>'id','name'=>'name','orders'=>'orders_count'];$sort=$filters['sort']??'newest';$direction=$filters['direction']??($sort==='newest'?'desc':'asc');return $query->orderBy($columns[$sort]??'id',$direction)->paginate((int)($filters['per_page']??10))->withQueryString();}
    public function create(array $data):Customer{return Customer::create($this->payload($data));}
    public function update(Customer $customer,array $data):void{$customer->update($this->payload($data));}
    public function delete(Customer $customer):void{$customer->delete();}
    private function payload(array $data):array{$payload=collect($data)->only(['name','phone','email','address','birthday','gender','notes'])->all();$payload['is_active']=(bool)($data['is_active']??false);return $payload;}
}
