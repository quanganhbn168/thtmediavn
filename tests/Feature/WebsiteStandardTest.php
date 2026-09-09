<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Filament\Pages\Settings;
use App\Filament\Resources\Menus\MenuResource;
use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Models\Intro;
use App\Models\User;
use App\Settings\HomepageSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class WebsiteStandardTest extends TestCase
{
    use DatabaseTransactions;

    private function intro(array $data = []): Intro
    {
        return Intro::create(array_replace(['title' => 'QA bài giới thiệu', 'slug' => 'qa-standard-'.uniqid(), 'kind' => 'article', 'is_active' => true, 'content' => '<p>Nội dung</p>'], $data));
    }

    public function test_only_published_articles_are_menu_sources(): void
    {
        $published = $this->intro();
        $draft = $this->intro(['is_active' => false]);
        $future = $this->intro(['published_at' => now()->addDay()]);
        $block = $this->intro(['kind' => 'block']);
        $keys = collect(MenuResource::sourceGroups())->flatMap(fn (array $group) => array_column($group['items'], 'key'));
        $this->assertContains('intro:'.$published->id, $keys);
        foreach ([$draft, $future, $block] as $hidden) {
            $this->assertNotContains('intro:'.$hidden->id, $keys);
        }
    }

    public function test_builder_saves_content_reference_and_keeps_child_on_edit(): void
    {
        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        $panel->boot();
        $user = User::query()->firstOrFail();
        $intro = $this->intro();
        $component = Livewire::actingAs($user, $panel->getAuthGuard())->test(CreateMenu::class);
        $component->set('data.name', 'QA standard '.uniqid());
        $model = MenuResource::getModel();
        if (is_subclass_of($model, 'Datlechin\\FilamentMenuBuilder\\Models\\Menu')) {
            $component->set('data.is_visible', true);
        } else {
            $component->set('data.location', 'header')->set('data.is_active', true);
        }
        $component->call('addMenuItemFromSource', 'intro:'.$intro->id)->call('create')->assertHasNoFormErrors();
        $menu = $model::query()->orderByDesc('id')->firstOrFail();
        $parent = $menu->topLevelItems()->firstOrFail();
        $child = $parent->replicate();
        $child->parent_id = $parent->id;
        $child->save();
        $grandchild = $child->replicate();
        $grandchild->parent_id = $child->id;
        $grandchild->save();
        Livewire::test(EditMenu::class, ['record' => $menu->id])->call('save')->assertHasNoFormErrors();
        $this->assertSame($child->id, $grandchild->fresh()->parent_id);
        $this->assertSame($parent->id, $child->fresh()->parent_id);
        $this->assertSame($menu->id, $child->fresh()->menu_id);
        $parent = $parent->fresh();
        $url = $parent->link ?? $parent->href ?? $parent->url;
        $this->assertSame($intro->url, $url);
        $intro->update(['is_active' => false]);
        $parent = $parent->fresh();
        $this->assertSame('#', $parent->link ?? $parent->href ?? $parent->url);
    }

    public function test_saving_homepage_leaves_about_settings_unchanged(): void
    {
        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        $panel->boot();
        $user = User::query()->firstOrFail();
        $home = app(HomepageSettings::class);
        $field = property_exists($home, 'homepage_about_title') ? 'homepage_about_title.vi' : (property_exists($home, 'intro_title') ? 'homepage_intro_title' : 'about_title.vi');
        $page = class_exists(ManageSettings::class) ? ManageSettings::class : Settings::class;
        $snapshot = fn () => DB::table('settings')->where('group', 'about')->orWhere(fn ($query) => $query->where('group', 'page')->where('name', 'like', 'about_%'))->get()->mapWithKeys(fn ($row): array => [$row->group.'.'.$row->name => json_decode($row->payload, true)])->all();
        $before = $snapshot();
        Livewire::actingAs($user, $panel->getAuthGuard())->test($page)->set('data.'.$field, 'Giới thiệu trang chủ QA')->call('save')->assertHasNoFormErrors();
        $this->assertSame($before, $snapshot());
    }
}
