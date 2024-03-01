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
class UserController extends Controller
{
// The function to register a new user
public function register(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'profile_picture' => 'required|image|mimes:jpg,jpeg,png',
        'interests' => 'nullable|string',
    ]);

    // If the validation fails, return an error response
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'registraion failed: ' . json_encode($validator->errors())
           
        ], 422);
    }

    // Store the profile picture in the local storage and get the path
    $path = Storage::putFile('public/profile_pictures', $request->file('profile_picture'));

    // Create a new profile picture instance with the path
    $profile_picture = new ProfilePicture();
    $profile_picture->path = $path;

    // Save the profile picture record in the database
    if ($profile_picture->save()) {
        // Create a new user instance with the request data and the profile picture id
        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->profile_picture_id = $profile_picture->id;
        $user->interests = $request->interests;

        // Save the user record in the database
        if ($user->save()) {
            // Generate a token for the user
            $token = $user->createToken('user-token')->accessToken;

            // Return a success response with the user and the token
            return response()->json([
                'status' => 'success',
                'message' => 'Registered successfully',
                'token' => $token
            ], 201);
        } else {
            // Return an error response if the user save failed
            return response()->json([
                'status' => 'error',
                'message' => 'User creation failed'
            ], 500);
        }
    } else {
        // Return an error response if the profile picture save failed
        return response()->json([
            'status' => 'error',
            'message' => 'Profile picture creation failed'
        ], 500);
    }
}

public function login(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    // If the validation fails, return an error response
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'email or passowrd is incorrect',
            'errors' => $validator->errors()
        ], 422);
    }

    // Attempt to authenticate the user with the given credentials
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        // Get the authenticated user
        $user = Auth::user();

        // Generate a token for the user
        $token = $user->createToken('user-token')->accessToken;

        // Return a success response with the token and a message
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token
        ], 200);
    } else {
        // Return an error response if the authentication failed
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }
}




public function getAllUsers(Request $request)
{
    // Get all the users from the database with the profile picture and the total likes count
    $users = User::with('profile_picture')->withCount('likes')->get();

    // Create an empty array to store the response data
    $data = [];

    // Loop through each user and add their data and image link to the array
    foreach ($users as $user) {
        // Get the profile picture path from the user's relation
        $path = $user->profile_picture->path;

        // Get the image link from the storage
        $image_url = url(Storage::url($path)); 
        

        // Add the user data, image link, and total likes to the array
        // Only include the required attributes: id, username, email, profile_picture_id, image_link, and total_likes
        $data[] = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'interests' => $user->interests,
            'profile_picture_id' => $user->profile_picture_id,
            'image_link' => $image_url,
            'total_likes' => $user->likes_count
        ];
    }

    // Return a success response with the data array in JSON format
    return response()->json([
        'data' => $data
    ], 200);
}


    public function reset(Request $request)
    {
        // Define the validation rules
        $rules = [
            'old_password' => 'required|password', // This rule checks if the old password matches the current password
            'new_password' => 'required|string|min:8|confirmed', // This rule checks if the new password is a string, has at least 8 characters, and matches the confirmation field
        ];
    
    
    
        // If the validation passes, update the user's password
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
    
        // Return a success response
        return response()->json(['message' => 'Password updated successfully'], 200);
    }
    

}
