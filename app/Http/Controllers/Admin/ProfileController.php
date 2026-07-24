<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    /**
     * Inject ProfileService.
     */
    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Display user profile.
     */
    public function index()
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Update user profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        
        $this->profileService->updateProfile($user, $request->validated());

        return redirect()->route('admin.profile')->with('success', 'Hồ sơ cá nhân đã được cập nhật thành công!');
    }
}
