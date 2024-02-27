<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Authenticatable;
use App\Models\User;
use App\Models\ProfilePicture;
use App\Models\like;
class LikeController extends Controller
{


public function likeProfile(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'profile_picture_id' => 'required|integer|exists:profile_pictures,id'
        ]);

        // Check if the user has already liked the profile picture
        $like = Like::where('user_id', $request->user_id)
                    ->where('profile_picture_id', $request->profile_picture_id)
                    ->first();

        // If the like record exists, return an error response
        if ($like) {
            return response()->json([
                'status' => 'error',
                'message' => 'already liked'
            ], 409);
        }

        // If the like record does not exist, create a new one
        $like = Like::create($request->all());

        // Return a success response with the like record
        return response()->json([
            'status' => 'success',
            'message' => ' profile picture liked successfully',
            'like' => $like
        ], 201);
    }

   
    

}
