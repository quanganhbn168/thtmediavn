<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAboutSettingRequest;
use App\Http\Requests\Admin\UpdateContactSettingRequest;
use App\Http\Requests\Admin\UpdateGeneralSettingRequest;
use App\Http\Requests\Admin\UpdateHomepageSettingRequest;
use App\Http\Requests\Admin\UpdateMediaSettingRequest;
use App\Http\Requests\Admin\UpdateMenuSettingRequest;
use App\Http\Requests\Admin\UpdateSeoSettingRequest;
use App\Models\Menu;
use App\Models\SiteAsset;
use App\Services\SettingService;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
use App\Settings\MediaSettings;
use App\Settings\MenuSettings;
use App\Settings\SeoSettings;

class SettingController extends Controller
{
    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Cài đặt chung & Website
     */
    public function general(GeneralSettings $settings)
    {
        return view('admin.settings.general', ['settings' => $settings, 'assets' => SiteAsset::current()]);
    }

    public function updateGeneral(UpdateGeneralSettingRequest $request, GeneralSettings $settings)
    {
        $this->settingService->updateGeneral(
            $request->validated(),
            $settings
        );

        return redirect()->back()->with('success', 'Cập nhật cấu hình chung thành công!');
    }

    public function contact(ContactSettings $settings)
    {
        return view('admin.settings.contact', compact('settings'));
    }

    public function updateContact(UpdateContactSettingRequest $request, ContactSettings $settings)
    {
        $this->settingService->updateContact($request->validated(), $settings);

        return redirect()->back()->with('success', 'Cập nhật thông tin liên hệ thành công!');
    }

    /**
     * Cấu hình SEO
     */
    public function seo(SeoSettings $settings)
    {
        return view('admin.settings.seo', ['settings' => $settings, 'assets' => SiteAsset::current()]);
    }

    public function updateSeo(UpdateSeoSettingRequest $request, SeoSettings $settings)
    {
        $this->settingService->updateSeo($request->validated(), $settings);

        return redirect()->back()->with('success', 'Cập nhật cấu hình SEO thành công!');
    }

    /**
     * Cấu hình Trang chủ
     */
    public function homepage(HomepageSettings $settings)
    {
        return view('admin.settings.homepage', compact('settings'));
    }

    public function updateHomepage(UpdateHomepageSettingRequest $request, HomepageSettings $settings)
    {
        $this->settingService->updateHomepage($request->validated(), $settings);

        return redirect()->back()->with('success', 'Cập nhật cấu hình Trang chủ thành công!');
    }

    /**
     * Cấu hình Trang giới thiệu
     */
    public function about(AboutSettings $settings)
    {
        return view('admin.settings.about', ['settings' => $settings, 'assets' => SiteAsset::current()]);
    }

    public function updateAbout(UpdateAboutSettingRequest $request, AboutSettings $settings)
    {
        $this->settingService->updateAbout($request->validated(), $settings);

        return redirect()->back()->with('success', 'Cập nhật cấu hình trang Giới thiệu thành công!');
    }

    /**
     * Cấu hình Media & Banners
     */
    public function media(MediaSettings $settings)
    {
        return view('admin.settings.media', ['settings' => $settings, 'assets' => SiteAsset::current()]);
    }

    public function updateMedia(UpdateMediaSettingRequest $request, MediaSettings $settings)
    {
        $this->settingService->updateMedia($request->validated(), $settings);

        return redirect()->back()->with('success', 'Cập nhật cấu hình Media thành công!');
    }

    /** Cấu hình menu hiển thị ở header, mega menu và footer. */
    public function menu(MenuSettings $settings)
    {
        $menus = Menu::query()
            ->where('is_active', true)
            ->whereIn('location', ['header', 'footer'])
            ->withCount('allItems')
            ->orderBy('name')
            ->get();

        $headerMenus = $menus->where('location', 'header')
            ->mapWithKeys(fn (Menu $menu) => [$menu->id => sprintf('%s · %d liên kết', $menu->getTranslation('name', 'vi'), $menu->all_items_count)])
            ->all();
        $footerMenus = $menus->where('location', 'footer')
            ->mapWithKeys(fn (Menu $menu) => [$menu->id => sprintf('%s · %d liên kết', $menu->getTranslation('name', 'vi'), $menu->all_items_count)])
            ->all();

        return view('admin.settings.menu', compact('settings', 'headerMenus', 'footerMenus'));
    }

    public function updateMenu(UpdateMenuSettingRequest $request, MenuSettings $settings)
    {
        $this->settingService->updateMenu($request->validated(), $settings);

        return redirect()->back()->with('success', 'Cập nhật cài đặt menu thành công!');
    }

}
