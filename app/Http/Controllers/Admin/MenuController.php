<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Http\Requests\Admin\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    private function getSystemMenuPages(): array
    {
        return [
            'system_home' => [
                'id' => 'system_home',
                'title' => ['vi' => 'Trang chủ'],
                'url' => '/',
            ],
            'system_about' => [
                'id' => 'system_about',
                'title' => ['vi' => 'Giới thiệu'],
                'url' => '/gioi-thieu',
            ],
            'system_products' => [
                'id' => 'system_products',
                'title' => ['vi' => 'Sản phẩm'],
                'url' => '/san-pham',
            ],
            'system_posts' => [
                'id' => 'system_posts',
                'title' => ['vi' => 'Tin tức'],
                'url' => '/tin-tuc',
            ],
            'system_contact' => [
                'id' => 'system_contact',
                'title' => ['vi' => 'Liên hệ'],
                'url' => '/lien-he',
            ],
        ];
    }

    /**
     * Giao diện trình quản lý menu.
     */
    public function index(Request $request)
    {
        $menus = Menu::orderBy('id', 'desc')->get();

        $activeMenu = null;
        if ($request->filled('id')) {
            $activeMenu = Menu::find($request->input('id'));
        }

        if (! $activeMenu && $menus->isNotEmpty()) {
            $activeMenu = $menus->first();
        }

        $menuItems = [];
        if ($activeMenu) {
            // Chỉ lấy các item gốc (parent_id = null) kèm theo mối quan hệ đệ quy
            $menuItems = $activeMenu->items()->with('children')->orderBy('sort_order')->get();
        }

        // Lấy danh sách các thực thể để add vào menu
        $pages = Page::orderBy('id', 'desc')->get();
        $posts = Post::orderBy('id', 'desc')->get();
        $productCategories = ProductCategory::query()
            ->orderByRaw('COALESCE(parent_id, id)')
            ->orderByRaw('parent_id is not null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $systemPages = collect($this->getSystemMenuPages());

        return view('admin.menus.index', compact('menus', 'activeMenu', 'menuItems', 'pages', 'posts', 'productCategories', 'systemPages'));
    }

    /**
     * Tạo mới Menu cha.
     */
    public function store(StoreMenuRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $menu = Menu::create($data);

        return redirect()
            ->route('admin.menus.index', ['id' => $menu->id])
            ->with('success', 'Tạo menu mới thành công!');
    }

    /**
     * Cập nhật Menu cha.
     */
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $menu->update($data);

        return redirect()
            ->route('admin.menus.index', ['id' => $menu->id])
            ->with('success', 'Cập nhật menu thành công!');
    }

    /**
     * Xóa Menu cha.
     */
    public function destroy(Menu $menu)
    {
        $menu->delete(); // cascade sẽ xóa toàn bộ menu_items con

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Xóa menu thành công!');
    }

    /**
     * Thêm các item vào menu hiện tại.
     */
    public function addItems(Request $request, Menu $menu)
    {
        $type = $request->input('type');

        if ($type === 'custom') {
            $request->validate([
                'custom_title.vi' => 'required|string|max:255',
                'custom_url' => 'required|string|max:255',
            ], [
                'custom_title.vi.required' => 'Tiêu đề link tự chọn không được để trống.',
                'custom_url.required' => 'Đường dẫn link tự chọn không được để trống.',
            ]);

            MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => null,
                'title' => [
                    'vi' => $request->input('custom_title.vi'),
                ],
                'url' => $request->input('custom_url'),
                'target' => $request->input('custom_target', '_self'),
                'icon' => $request->input('custom_icon'),
                'sort_order' => MenuItem::where('menu_id', $menu->id)->max('sort_order') + 1,
                'is_active' => true,
            ]);

        } elseif (in_array($type, ['pages', 'posts', 'product_categories'])) {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một liên kết để thêm.');
            }

            $maxOrder = MenuItem::where('menu_id', $menu->id)->max('sort_order') ?? 0;
            $systemPages = $this->getSystemMenuPages();

            foreach ($ids as $id) {
                $idString = (string) $id;
                $maxOrder++;
                $title = [];
                $url = '';

                switch ($type) {
                    case 'pages':
                        if (str_starts_with($idString, 'system_') && isset($systemPages[$idString])) {
                            $title = $systemPages[$idString]['title'];
                            $url = $systemPages[$idString]['url'];
                            break;
                        }

                        $page = Page::find($id);
                        if ($page) {
                            $title = $page->getTranslations('name');
                            $url = route('content.show', [
                                'domain' => 'trang',
                                'slug' => $page->getSlug('vi'),
                            ]);
                        }
                        break;

                    case 'posts':
                        $post = Post::find($id);
                        if ($post) {
                            $title = $post->getTranslations('name');
                            $domain = $post->category?->slug ?: 'tin-tuc';
                            $url = route('content.show', [
                                'domain' => $domain,
                                'slug' => $post->getSlug('vi'),
                            ]);
                        }
                        break;

                    case 'product_categories':
                        $category = ProductCategory::find($id);
                        if ($category) {
                            $title = ['vi' => $category->name];
                            $url = route('content.show', [
                                'domain' => 'danh-muc',
                                'slug' => $category->slug,
                            ]);
                        }
                        break;

                }

                if (! empty($title)) {
                    MenuItem::create([
                        'menu_id' => $menu->id,
                        'parent_id' => null,
                        'title' => $title,
                        'url' => $url,
                        'sort_order' => $maxOrder,
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.menus.index', ['id' => $menu->id])
            ->with('success', 'Đã thêm các liên kết vào menu thành công!');
    }

    /**
     * AJAX: Cập nhật thứ tự sắp xếp và phân cấp của Menu Items từ Nestable2.
     */
    public function updateItemsOrder(Request $request, Menu $menu)
    {
        $structure = $request->input('structure', []);

        if (empty($structure)) {
            return response()->json(['success' => false, 'message' => 'Cấu trúc menu trống.'], 400);
        }

        $order = 1;
        $this->saveMenuItemsOrderRecursive($structure, null, $order);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật cấu trúc menu thành công!',
        ]);
    }

    /**
     * Lưu đệ quy thứ tự và phân cấp cho menu item
     */
    private function saveMenuItemsOrderRecursive(array $items, ?int $parentId, &$order)
    {
        foreach ($items as $item) {
            $itemId = $item['id'];
            $menuItem = MenuItem::find($itemId);

            if ($menuItem) {
                $menuItem->update([
                    'parent_id' => $parentId,
                    'sort_order' => $order++,
                ]);

                if (isset($item['children']) && is_array($item['children']) && count($item['children']) > 0) {
                    $this->saveMenuItemsOrderRecursive($item['children'], $itemId, $order);
                }
            }
        }
    }

    /**
     * AJAX: Cập nhật thông tin chi tiết của một MenuItem đơn lẻ.
     */
    public function updateItem(Request $request, MenuItem $item)
    {
        $request->validate([
            'title.vi' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'url' => 'required|string|max:255',
            'target' => 'required|string|max:20',
            'icon' => 'nullable|string|max:100',
        ]);

        $item->update([
            'title' => [
                'vi' => $request->input('title.vi'),
                'en' => $request->input('title.en') ?: $request->input('title.vi'),
            ],
            'url' => $request->input('url'),
            'target' => $request->input('target'),
            'icon' => $request->input('icon'),
            'is_active' => $request->has('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật liên kết thành công!',
        ]);
    }

    /**
     * AJAX: Xóa một MenuItem và các con của nó.
     */
    public function deleteItem(MenuItem $item)
    {
        // DB level cascade sẽ tự xóa các con, ta chỉ cần gọi delete() trên item này
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa liên kết thành công!',
        ]);
    }
}
