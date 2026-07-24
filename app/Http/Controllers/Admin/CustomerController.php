<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customer\IndexCustomerRequest;
use App\Http\Requests\Admin\Customer\StoreCustomerRequest;
use App\Http\Requests\Admin\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class CustomerController extends Controller {
    public function __construct(private readonly CustomerService $service) {}
    public function index(IndexCustomerRequest $request): View { return view('admin.customers.index',['customers'=>$this->service->paginate($request->validated())]); }
    public function create(): View { return view('admin.customers.create',['genders'=>CustomerService::GENDERS]); }
    public function store(StoreCustomerRequest $request): RedirectResponse { $customer=$this->service->create($request->validated());return redirect()->route('admin.customers.edit',$customer)->with('success','Tạo khách hàng thành công.'); }
    public function edit(Customer $customer): View { return view('admin.customers.edit',compact('customer')+['genders'=>CustomerService::GENDERS]); }
    public function update(UpdateCustomerRequest $request,Customer $customer): RedirectResponse { $this->service->update($customer,$request->validated());return redirect()->route('admin.customers.index')->with('success','Cập nhật khách hàng thành công.'); }
    public function destroy(Customer $customer): RedirectResponse { $this->service->delete($customer);return back()->with('success','Đã xóa khách hàng.'); }
}
