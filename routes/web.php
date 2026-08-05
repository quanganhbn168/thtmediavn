<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\CommonController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\SliderItemController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Frontend;
use Illuminate\Support\Facades\Route;

Route::get('/', [Frontend\HomeController::class, 'index'])->name('home');
Route::view('/gioi-thieu', 'frontend.about')->name('about');
Route::get('/san-pham', [Frontend\ProductController::class, 'index'])->name('catalog');
Route::get('/danh-muc/{category}', [Frontend\SlugController::class, 'category'])
    ->name('products.by-category')
    ->where('category', '[a-z0-9-]+');
    Route::get('/san-pham/{slug}', [Frontend\SlugController::class, 'product'])
        ->name('product.show')
        ->where('slug', '[a-z0-9-]+');
Route::get('/combo', [Frontend\ComboController::class, 'index'])->name('combos.index');
Route::get('/danh-muc-combo/{category}', [Frontend\ComboController::class, 'byCategory'])->name('combos.by-category')->where('category', '[a-z0-9-]+');
Route::get('/combo/{slug}', [Frontend\ComboController::class, 'show'])->name('combo.show')->where('slug', '[a-z0-9-]+');
Route::get('/gio-hang', [Frontend\CartController::class, 'index'])->name('cart');
Route::post('/gio-hang', [Frontend\CartController::class, 'store'])->name('cart.store');
Route::patch('/gio-hang/{item}', [Frontend\CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{item}', [Frontend\CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/gio-hang/ma-giam-gia', [Frontend\CartController::class, 'coupon'])->name('cart.coupon');
Route::delete('/gio-hang/ma-giam-gia', [Frontend\CartController::class, 'removeCoupon'])->name('cart.coupon.destroy');
Route::get('/thanh-toan', [Frontend\CheckoutController::class, 'create'])->name('checkout');
Route::post('/thanh-toan', [Frontend\CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/thanh-toan/{publicToken}', [Frontend\CheckoutController::class, 'payment'])
    ->where('publicToken', '[A-Za-z0-9]{64}')
    ->name('checkout.payment');
Route::get('/thanh-toan/{publicToken}/trang-thai', [Frontend\CheckoutController::class, 'paymentStatus'])
    ->where('publicToken', '[A-Za-z0-9]{64}')
    ->middleware('throttle:30,1')
    ->name('checkout.payment.status');
Route::get('/dat-hang-thanh-cong/{code}', [Frontend\CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/tin-tuc', [Frontend\PostController::class, 'index'])->name('news.index');
Route::get('/tin-tuc/{slug}', [Frontend\SlugController::class, 'show'])->name('news.show');
Route::view('/lien-he', 'frontend.contact')->name('contact');
Route::view('/chinh-sach-mua-hang', 'frontend.policies.purchase')->name('policies.purchase');
Route::view('/chinh-sach-bao-mat', 'frontend.policies.privacy')->name('policies.privacy');
Route::view('/chinh-sach-doi-tra', 'frontend.policies.returns')->name('policies.returns');
Route::post('/lien-he', [Frontend\ContactController::class, 'submit'])->middleware('throttle:frontend-forms')->name('contact.submit');
Route::post('/dang-ky-nhan-tin', [Frontend\NewsletterController::class, 'store'])->middleware('throttle:frontend-forms')->name('newsletter.store');

// Các route dành cho khách chưa đăng nhập (guest)
Route::middleware('guest:web')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showFrontendLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'loginFrontend'])->middleware('throttle:frontend-forms')->name('login.store');
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->name('register.store');
});
Route::redirect('/login', '/dang-nhap');
Route::redirect('/admin/dang-nhap', '/admin/login');

Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->middleware('throttle:admin-login')->name('admin.login.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Các route yêu cầu đã đăng nhập (auth)
Route::middleware('auth:web')->group(function () {
    Route::get('/yeu-thich', [Frontend\WishlistController::class, 'index'])->name('wishlist');
    Route::post('/yeu-thich/{product}', [Frontend\WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/san-pham/{product}/danh-gia', [Frontend\ReviewController::class, 'store'])->middleware('throttle:frontend-forms')->name('product.reviews.store');
    Route::prefix('tai-khoan')->name('account.')->group(function () {
        Route::get('/', [Frontend\AccountController::class, 'index'])->name('index');
        Route::get('/don-hang', [Frontend\AccountController::class, 'orders'])->name('orders');
        Route::get('/don-hang/{order}', [Frontend\AccountController::class, 'order'])->name('orders.show');
        Route::get('/thong-tin', [Frontend\AccountController::class, 'profile'])->name('profile');
        Route::put('/thong-tin', [Frontend\AccountController::class, 'updateProfile'])->name('profile.update');
        Route::post('/dia-chi', [Frontend\AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::delete('/dia-chi/{address}', [Frontend\AccountController::class, 'destroyAddress'])->name('addresses.destroy');
    });

});

Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý Đơn hàng
    Route::resource('/orders', OrderController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::resource('/payments', Admin\PaymentController::class)->except(['show']);
    Route::get('/payment-transactions', [Admin\PaymentTransactionController::class, 'index'])->name('payment-transactions.index');
    Route::get('/payment-transactions/{paymentTransaction}', [Admin\PaymentTransactionController::class, 'show'])->name('payment-transactions.show');
    Route::post('/payment-transactions/{paymentTransaction}/attach', [Admin\PaymentTransactionController::class, 'attach'])->name('payment-transactions.attach');

    // Mini e-commerce
    Route::resource('/products', Admin\ProductController::class)->except(['show']);
    Route::resource('/combos', Admin\ComboController::class)->except(['show']);
    Route::get('/combos/{combo}/components', [Admin\ComboComponentController::class, 'index'])->name('combos.components.index');
    Route::get('/combos/{combo}/components/create', [Admin\ComboComponentController::class, 'create'])->name('combos.components.create');
    Route::post('/combos/{combo}/components', [Admin\ComboComponentController::class, 'store'])->name('combos.components.store');
    Route::get('/combos/{combo}/components/{comboItem}/edit', [Admin\ComboComponentController::class, 'edit'])->name('combos.components.edit');
    Route::put('/combos/{combo}/components/{comboItem}', [Admin\ComboComponentController::class, 'update'])->name('combos.components.update');
    Route::delete('/combos/{combo}/components/{comboItem}', [Admin\ComboComponentController::class, 'destroy'])->name('combos.components.destroy');
    Route::resource('/combo-categories', Admin\ComboCategoryController::class)->except(['show']);
    Route::resource('/product-categories', Admin\ProductCategoryController::class)->except(['show']);
    Route::resource('/brands', Admin\BrandController::class)->except(['show']);
    Route::resource('/product-options', Admin\ProductOptionController::class)->except(['show']);
    Route::resource('/product-attributes', Admin\ProductAttributeController::class)->except(['show']);
    Route::get('/flash-sales/products', [Admin\FlashSaleController::class, 'products'])->name('flash-sales.products');
    Route::resource('/flash-sales', Admin\FlashSaleController::class)->except(['show']);
    Route::resource('/coupons', Admin\CouponController::class)->except(['show']);

    // CMS: Trang tĩnh
    Route::resource('/pages', PageController::class)->except(['show']);

    // CMS: Danh mục bài viết & Bài viết
    Route::resource('/post-categories', PostCategoryController::class)->except(['show'])->names([
        'index' => 'post-categories.index',
        'create' => 'post-categories.create',
        'store' => 'post-categories.store',
        'edit' => 'post-categories.edit',
        'update' => 'post-categories.update',
        'destroy' => 'post-categories.destroy',
    ]);

    Route::resource('/posts', Admin\PostController::class)->except(['show'])->names([
        'index' => 'posts.index',
        'create' => 'posts.create',
        'store' => 'posts.store',
        'edit' => 'posts.edit',
        'update' => 'posts.update',
        'destroy' => 'posts.destroy',
    ]);

    // CMS: Sliders
    Route::get('/sliders', [SliderController::class, 'index'])->name('sliders.index');
    Route::get('/sliders/create', [SliderController::class, 'create'])->name('sliders.create');
    Route::post('/sliders', [SliderController::class, 'store'])->name('sliders.store');
    Route::get('/sliders/{slider}/edit', [SliderController::class, 'edit'])->name('sliders.edit');
    Route::put('/sliders/{slider}', [SliderController::class, 'update'])->name('sliders.update');
    Route::delete('/sliders/{slider}', [SliderController::class, 'destroy'])->name('sliders.destroy');

    Route::resource('/testimonials', Admin\TestimonialController::class)->except(['show']);

    // CMS: Slider Items
    Route::get('/sliders/{slider}/items/create', [SliderItemController::class, 'create'])->name('slider-items.create');
    Route::post('/slider-items', [SliderItemController::class, 'store'])->name('slider-items.store');
    Route::get('/slider-items/{item}/edit', [SliderItemController::class, 'edit'])->name('slider-items.edit');
    Route::put('/slider-items/{item}', [SliderItemController::class, 'update'])->name('slider-items.update');
    Route::delete('/slider-items/{item}', [SliderItemController::class, 'destroy'])->name('slider-items.destroy');

    // CMS: Menus (jQuery Nestable2)
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
    Route::post('/menus/{menu}/items', [MenuController::class, 'addItems'])->name('menus.items.add');
    Route::post('/menus/{menu}/items/order', [MenuController::class, 'updateItemsOrder'])->name('menus.items.order');
    Route::put('/menus/{menu}/items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
    Route::delete('/menus/{menu}/items/{item}', [MenuController::class, 'deleteItem'])->name('menus.items.delete');

    // CRM: Khách hàng
    // CRM: Tin nhắn liên hệ
    Route::resource('/contacts', Admin\ContactController::class)->only(['index', 'edit', 'update', 'destroy']);

    // CRM: Đăng ký nhận tin
    Route::resource('/subscribers', SubscriberController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::get('/reviews', [Admin\InteractionModerationController::class, 'reviews'])->name('reviews.index');
    Route::patch('/reviews/{review}', [Admin\InteractionModerationController::class, 'updateReview'])->name('reviews.update');
    Route::delete('/reviews/{review}', [Admin\InteractionModerationController::class, 'destroyReview'])->name('reviews.destroy');
    Route::get('/comments', [Admin\InteractionModerationController::class, 'comments'])->name('comments.index');
    Route::patch('/comments/{comment}', [Admin\InteractionModerationController::class, 'updateComment'])->name('comments.update');
    Route::delete('/comments/{comment}', [Admin\InteractionModerationController::class, 'destroyComment'])->name('comments.destroy');

    // Cài đặt & Hệ thống
    Route::resource('/users', UserController::class)->except(['show']);
    Route::resource('/roles', RoleController::class)->except(['show']);
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::post('/general', [SettingController::class, 'updateGeneral'])->name('general.update');
        Route::get('/contact', [SettingController::class, 'contact'])->name('contact');
        Route::post('/contact', [SettingController::class, 'updateContact'])->name('contact.update');
        Route::get('/seo', [SettingController::class, 'seo'])->name('seo');
        Route::post('/seo', [SettingController::class, 'updateSeo'])->name('seo.update');
        Route::get('/homepage', [SettingController::class, 'homepage'])->name('homepage');
        Route::post('/homepage', [SettingController::class, 'updateHomepage'])->name('homepage.update');
        Route::get('/menu', [SettingController::class, 'menu'])->name('menu');
        Route::post('/menu', [SettingController::class, 'updateMenu'])->name('menu.update');
        Route::get('/about', [SettingController::class, 'about'])->name('about');
        Route::post('/about', [SettingController::class, 'updateAbout'])->name('about.update');
        Route::get('/media', [SettingController::class, 'media'])->name('media');
        Route::post('/media', [SettingController::class, 'updateMedia'])->name('media.update');
        Route::get('/payment', [Admin\SePaySettingController::class, 'index'])->name('payment');
        Route::post('/payment/test-connection', [Admin\SePaySettingController::class, 'testConnection'])->name('payment.test-connection');
        Route::post('/payment/reconcile', [Admin\SePaySettingController::class, 'reconcile'])->name('payment.reconcile');
        Route::resource('/contact-channels', Admin\ContactChannelController::class)->except(['show']);
    });

    // Hồ sơ cá nhân
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Các route tiện ích dùng chung (Toggle AJAX, Upload tạm)
    Route::post('/common/bulk-action', [CommonController::class, 'bulkAction'])->name('common.bulk-action');
    Route::post('/common/reorder', [CommonController::class, 'reorder'])->name('common.reorder');
    Route::post('/common/toggle-field', [CommonController::class, 'toggleField'])->name('common.toggle-field');
    Route::post('/media/upload-temp', [CommonController::class, 'uploadTempMedia'])->name('media.upload.temp');
    Route::post('/media/upload-editor', [CommonController::class, 'uploadEditorImage'])->name('media.upload.editor');
    Route::get('/media/list', [CommonController::class, 'listMedia'])->name('media.list');
});

// Slug động theo domain (dùng cho các route nội dung đa hình phía ngoài)
Route::get('/{domain}/{slug}', [Frontend\SlugController::class, 'showByDomain'])->name('content.show');
