<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $garden = null;

        if ($user->role === 'farmer') {
            $garden = $user->gardens()->first();
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'garden' => $garden,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->role === 'farmer' && isset($validated['garden'])) {
            $gardenData = $validated['garden'];
            if (!empty($gardenData) && !empty($gardenData['name'])) {
                $garden = $user->gardens()->first();
                
                if ($request->hasFile('garden_photo')) {
                    if ($garden && $garden->photo_path) {
                        Storage::disk('public')->delete($garden->photo_path);
                    }
                    $gardenData['photo_path'] = $request->file('garden_photo')->store('gardens', 'public');
                } elseif (isset($validated['delete_garden_photo']) && $validated['delete_garden_photo']) {
                    if ($garden && $garden->photo_path) {
                        Storage::disk('public')->delete($garden->photo_path);
                    }
                    $gardenData['photo_path'] = null;
                }

                if ($request->hasFile('garden_cover')) {
                    if ($garden && $garden->cover_path) {
                        Storage::disk('public')->delete($garden->cover_path);
                    }
                    $gardenData['cover_path'] = $request->file('garden_cover')->store('gardens/covers', 'public');
                } elseif (isset($validated['delete_garden_cover']) && $validated['delete_garden_cover']) {
                    if ($garden && $garden->cover_path) {
                        Storage::disk('public')->delete($garden->cover_path);
                    }
                    $gardenData['cover_path'] = null;
                }

                if ($garden) {
                    $garden->update($gardenData);
                } else {
                    $user->gardens()->create($gardenData);
                }
            }
        }

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
