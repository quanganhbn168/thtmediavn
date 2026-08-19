<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Subscriber;
use App\Models\Testimonial;
use App\Models\User;

class DashboardService
{
    public function getDashboardStats(User $actor): array
    {
        return [
            'activePosts' => Post::query()->where('is_active', true)->count(),
            'activePages' => Page::query()->where('is_active', true)->count(),
            'activeServices' => Service::query()->where('is_active', true)->count(),
            'activeProjects' => Project::query()->where('is_active', true)->count(),
            'activeClients' => Client::query()->where('is_active', true)->count(),
            'newContacts' => Contact::query()->where('status', 'new')->count(),
            'activeSubscribers' => Subscriber::query()->where('is_active', true)->count(),
            'activeTestimonials' => Testimonial::query()->where('is_active', true)->count(),
            'recentContacts' => Contact::query()->latest()->take(6)->get(),
            'recentPosts' => Post::query()->with('category')->latest()->take(6)->get(),
        ];
    }
}
