<?php

namespace App\Support\Menus;

use App\Models\CompanyContent;
use App\Models\Intro;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class MenuSources
{
    private const MODELS = ['company_content' => CompanyContent::class, 'intro' => Intro::class, 'service' => Service::class, 'service_category' => ServiceCategory::class, 'project' => Project::class, 'project_category' => ProjectCategory::class, 'post' => Post::class, 'post_category' => PostCategory::class];

    private const LABELS = ['company_content' => 'Nội dung công ty', 'intro' => 'Bài giới thiệu', 'service' => 'Dịch vụ', 'service_category' => 'Danh mục dịch vụ', 'project' => 'Dự án', 'project_category' => 'Danh mục dự án', 'post' => 'Bài viết', 'post_category' => 'Danh mục bài viết'];

    public static function routes(): array
    {
        return array_filter(['home' => 'Trang chủ', 'about' => 'Trang Giới thiệu', 'services.index' => 'Dịch vụ', 'projects.index' => 'Dự án', 'news.index' => 'Tin tức', 'contact' => 'Liên hệ', 'pricing' => 'Báo giá'], fn (string $name): bool => Route::has($name), ARRAY_FILTER_USE_KEY);
    }

    private static function query(string $type): mixed
    {
        $model = self::MODELS[$type] ?? null;
        if (! $model) {
            return null;
        }
        $query = $model::query();
        if ($type === 'intro') {
            return $query->published();
        }
        if ($type === 'company_content' || $type === 'project') {
            return $query->visibleOnSite();
        }
        if (in_array($type, ['post', 'service'], true) && Schema::hasColumn($query->getModel()->getTable(), 'published_at')) {
            $query->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
        }

        return $query->where('is_active', true);
    }

    public static function groups(string $search = ''): array
    {
        $groups = [['key' => 'routes', 'label' => 'Trang hệ thống', 'items' => collect(self::routes())->map(fn (string $label, string $name): array => ['key' => 'route:'.$name, 'label' => $label, 'meta' => 'Trang hệ thống'])->values()->all()]];
        foreach (self::MODELS as $type => $model) {
            $groups[] = ['key' => $type, 'label' => self::LABELS[$type], 'items' => self::query($type)->when($search !== '', fn ($query) => $query->where(Schema::hasColumn((new $model)->getTable(), 'title') ? 'title' : 'name', 'like', '%'.$search.'%'))->orderByDesc('id')->limit(50)->get()->map(fn (Model $record): array => ['key' => $type.':'.$record->id, 'label' => (string) ($record->title ?: $record->name), 'meta' => self::LABELS[$type]])->all()];
        }

        return $groups;
    }

    public static function item(string $type, string $id): ?array
    {
        $record = $type === 'route' ? null : self::query($type)?->find($id);
        $label = $type === 'route' ? (self::routes()[$id] ?? null) : ($record?->title ?: $record?->name);
        if (! $label) {
            return null;
        }

        return ['title' => $label, 'linked_source_type' => $type === 'route' ? null : $type, 'linked_source_id' => $record?->id, 'url' => null, 'route' => $type === 'route' ? $id : null, 'target' => '_self', 'is_active' => true, 'children' => []];
    }

    public static function url(string $type, int $id): string
    {
        $record = self::query($type)?->find($id);
        if (! $record) {
            return '#';
        }
        if ($record instanceof Intro) {
            return $record->url;
        }
        if ($record instanceof CompanyContent) {
            return route('about.content.show', ['slug' => $record->routeSlug()]);
        }
        $route = match ($type) {
            'service', 'service_category' => 'services.show', 'project', 'project_category' => 'projects.show', default => 'news.show'
        };

        return route($route, ['slug' => $record->getSlug(app()->getLocale())]);
    }
}
