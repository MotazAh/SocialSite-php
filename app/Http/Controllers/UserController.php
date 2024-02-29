<?php

namespace App\Http\Controllers;

use App\Events\ExampleEvent;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function showCorrectHomePage() {
        if (auth()->check()) {
            return view('homepage-feed', ['posts' => request()->user()->feedPosts()->latest()->paginate(4)]);
        } else {
            return view('homepage');
        }
    }

    public function login(Request $request) {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate(); // Save session in a cookie after a successful login
            event(new ExampleEvent(['username' => auth()->user()->username, 'action' => 'login']));
            return redirect('/')->with('success', 'You have successfully logged in');
        } else {
            return redirect('/')->with('failure', 'Invalid login');
        }
    }

    public function logout() {
        event(new ExampleEvent(['username' => auth()->user()->username, 'action' => 'logout']));
        auth()->logout();
        return redirect('/')->with('success', 'You have logged out');
    }

    public function register(Request $request) 
    {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:24', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed']
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);

        auth()->login($user);

        return redirect('/')->with('success', 'Account created successfully');
    }

    private function getSharedData(User $user) {
        $posts = $user->posts()->latest()->get();
        $isFollowing = 0;

        if (auth()->check()) {
            $isFollowing = Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->count();
        }

        View::share('sharedData', ['username' => $user->username, 'postCount' => $posts->count(), 'avatar' => $user->avatar, 'isFollowing' => $isFollowing, 'followerCount' => $user->followers()->count(), 'followingCount' => $user->following()->count()]);
    }

    public function profile(User $user) {
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileRaw(User $user) {
        $posts = $user->posts()->latest()->get();
        return response()->json(['theHTML' => view('profile-posts-only', ['posts' => $posts])->render(), 'docTitle' => $user->username . "'s Profile"]);
    }

    public function profileFollowers(User $user) {
        $this->getSharedData($user);
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowersRaw(User $user) {
        $followers = $user->followers()->latest()->get();
        return response()->json(['theHTML' => view('profile-followers-only', ['followers' => $followers])->render(), 'docTitle' => $user->username . "'s Followers"]);
    }

    public function profileFollowing(User $user) {
        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->following()->latest()->get()]);
    }

    public function profileFollowingRaw(User $user) {
        $following = $user->following()->latest()->get();
        return response()->json(['theHTML' => view('profile-following-only', ['following' => $following])->render(), 'docTitle' => $user->username . "'s Followers"]);
    }
    
    public function showAvatarForm() {
        return view('avatar-form');
    }

    public function storeAvatar(Request $request) {
        $request->validate([
            'avatar' => 'required|image|max:3000'
        ]);

        $user = $request->user();

        $filename = $user->id . '-' . uniqid() . '.jpg';

        $img = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $filename, $img);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg") {
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

        return back()->with('success', 'Avatar successfully updated');
    }
}
