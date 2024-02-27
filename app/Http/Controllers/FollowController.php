<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $user) {
        // Can not follow self or someone already followed
        if ($user->id == auth()->user()->id) {
            return back()->with('failure', 'Can not follow self');
        }

        $existFollow = Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->count();
        if ($existFollow) {
            return back()->with('failure', 'Already following user');
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followedUser = $user->id;
        $newFollow->save();

        return back()->with('success', 'User followeed successfully');
    }

    public function removeFollow(User $user) {
        Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->delete();
        return back()->with('success', 'Unfollowed user');
    }
}
