<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cookie;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
{
    /**
     *          logic
     * 
     * get fistname ,lastname ,email and password form the user
     *  validate the request to check all the data recieved is correct
     * combine the firstname and lastname to make the username
     * start the database tarnsection 
     * save the data in the database
     * there is a image prsent in the storage in with the name of deafult image store the path of that image in the profilepictre
     *  gernrate a token using passport 
     * end the database trasection 
     * if any error occur during the above delet the any data saved 
     * send token in the cookie 
     * send response user registered successfully
     * do proper error handling with try catch sending appropriate resoponse with proper define message which give peoper sense what is the reson of he error 
     */

    // Get firstname, lastname, email and password from the user
    $data = $request->only('firstname', 'lastname', 'email', 'password', 'password_confirmation');

    // Validate the request
    $validator = Validator::make($data, [
        'firstname' => 'required|string|max:10',
        'lastname' => 'required|string|max:10',
        'email' => 'required|string|email|max:50|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // If validation fails, return a JSON response with the errors
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Combine the firstname and lastname to make the username
    $data['username'] = $data['firstname'] . ' ' . $data['lastname'];

    // Hash the password
    $data['password'] = Hash::make($data['password']);

    // Start the database transaction
    DB::beginTransaction();

    try {
        // Save the data in the database
        $user = User::create($data);

        // // Get the default image from the storage and store the path in the profilepicture
        // $path = Storage::url('default.jpg');
        // $user->update(['profilepicture' => $path]);
// Get the default image from the storage and store the path in the profilepicture
            $fileName = 'default.jpg';
            $path = URL::asset('storage/' . $fileName);
            $user->update(['profilepicture' => $path]);

        // Generate a token using passport
        $token = $user->createToken('linktok_auth')->accessToken;
       
        // End the database transaction
        DB::commit();

        // Send token in the cookie
        $cookie = cookie('linktok_auth', $token, 60 * 24); // create a cookie valid for 24 hours

        // Send response user registered successfully
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ])->withCookie($cookie); // attach the cookie to the response
    } catch (Exception $e) {
        // If any error occurs, rollback the transaction and delete the token
        DB::rollBack();
        $user->tokens()->delete();

        // Send a JSON response with the exception message
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



        
    /**
     * Show the form for creating a new resource.
     */
    public function login(Request $request)
    {
        /**
         * get the email and password form the request
         * validate the formate of the passwrod and email
         * check the block table if the userid of the this user is presnt 
         * if present then send response you are blocked by the admin you can't login 
         * if user id is not present then continous beclwo steps 
         * check the email and password is same as in the database 
         * if user is authenticated then
         * gernate token 
         * make the isactive colum of that user 1 
         * save the cookie to the user browers 
         *  also send json response that loged in succesfful 
         * do proper validation and error handling use best pracitces and security practicese
         * 
         */

    // Get the email and password from the request
    $credentials = $request->only('email', 'password');

    // Validate the format of the email and password
    $validator = Validator::make($credentials, [
        'email' => 'required|string|email|max:50',
        'password' => 'required|string|min:8',
    ]);

    // If validation fails, return a JSON response with the errors
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }


    // Check if the email and password match the database records
    if (Auth::attempt($credentials)) {


        $blocked = DB::table('block')->where('user_id', Auth::user()->id)->exists();

        // If the user is blocked, return a JSON response with the error message
        if ($blocked) {
            return response()->json(['error' => 'You are blocked by the admin. You cannot login.'], 403);
        }

        // If user is authenticated, generate a token using passport
        $token = Auth::user()->createToken('linktok_auth')->accessToken;

        // Send token in the cookie
        $cookie = cookie('linktok_auth', $token, 60 * 24); // create a cookie valid for 24 hours

        // Update the user's isactive column to 1
        Auth::user()->update(['isactive' => 1]);

        // Send response user logged in successfully
        return response()->json([
            'message' => 'User logged in successfully',
            'user' => Auth::user(),
            'profilepictureURL' => asset('storage/' . Auth::user()->profilepicture),
        ])->withCookie($cookie); // attach the cookie to the response
    } else {
        // If authentication fails, return a JSON response with the error message
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function UpdateProfilePicture(Request $request)
    {
      /**
       * logic 
       * validate the image formate
       * save the image in the local storage in the folder name on userid and inside the userid should be the profilePic inside that image should be save 
       * from the request we get the id for that user whos  profile picture we want to update
       * after saving the image update the profilepicture colum of that user 
       * in the profilepicture colum save the path of that image 
       * send the json response profile picture updated succesfull 
       * do proper validation and error hanlding 
       */

// Validate the image format
$request->validate([
    'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
]);

// Get the user ID from the request
$userId = $request->id;

// Find the user by ID
$user = User::findOrFail($userId);

// Define the folder path using the user ID
$folderPath = 'profile_pictures/' . $userId . '/';

// Check if the directory exists, if not create a new one
if (!File::isDirectory($folderPath)) {
    File::makeDirectory($folderPath, 0777, true, true);
}

// Save the image in the local storage
if ($request->hasFile('profile_picture')) {
    $file = $request->file('profile_picture');
    $fileName = 'profilePic.' . $file->getClientOriginalExtension();
    $file->move($folderPath, $fileName);

    // Update the profile picture column of the user
    $user->profilepicture = $folderPath . $fileName;
    $user->save();

    // Send the JSON response
    return response()->json(['message' => 'Profile picture updated successfully'], 200);
} else {
    // Send an error response if no image is provided
    return response()->json(['message' => 'No profile picture uploaded'], 400);
}

    }   
    /**
     * Display the specified resource.
     */
    public function UpdateBio(Request $request)
    {
        /**
         * logic 
         *  /**
       * logic 
       * validate the input it should not be sql injection commands etc userbio should not be more then 250 charactors 
       * from the request we get the id for that user whos  userbio we want to update
       * in the userbio colum of that user save the userbio send in the request 
       * send the json response profile userbio updated succesfull 
       * do proper validation and error hanlding
       * do proper validation and eroor handling 
       */
      // Validate the request data.
    $validatedData = $request->validate([
        'id' => 'required|numeric',
        'userBio' => 'required|string|max:250'
    ]);

    try {
        // Retrieve the user by ID.
        $user = User::findOrFail($validatedData['id']);

        // Update the user's biography.
        $user->userBio = $validatedData['userBio'];
        $user->save();

        // Return a JSON response indicating success.
        return response()->json(['message' => 'Profile biography updated successfully.'], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Return a JSON response indicating the user was not found.
        return response()->json(['error' => 'User not found.'], 404);
    } catch (\Exception $e) {
        // Return a JSON response indicating a server error.
        return response()->json(['error' => 'Server error.'], 500);
    }   
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function resetpassword(Request $request)
    {
        /**
         * logic
         * from the request we get the oldpassword,newpassword and confirm  new passowrd 
         * do the validation on the password 
         * in the request we also get the userid 
         * check the password stored in the database match with the old password 
         * if yes change the passowrd to the new password 
         * else end an error 
         * do proper validationa and error handling 
         * and send response in json formate 
         */

         // Validate the request data.
    $validatedData = $request->validate([
        'userId' => 'required|numeric',
        'oldPassword' => 'required|string',
        'newPassword' => 'required|string|confirmed|min:8',
    ]);

    try {
        // Retrieve the user by ID.
        $user = User::findOrFail($validatedData['userId']);

        // Check if the old password matches.
        if (!Hash::check($validatedData['oldPassword'], $user->password)) {
            return response()->json(['error' => 'The provided password does not match our records.'], 401);
        }

        // Update the user's password.
        $user->password = Hash::make($validatedData['newPassword']);
        $user->save();

        // Return a JSON response indicating success.
        return response()->json(['message' => 'Password reset successfully.'], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Return a JSON response indicating the user was not found.
        return response()->json(['error' => 'User not found.'], 404);
    } catch (\Exception $e) {
        // Return a JSON response indicating a server error.
        return response()->json(['error' => 'Server error.'], 500);
    }
    }


    public function Logout(Request $request){
        /**
         * logic
         * we get the id of the user in the request
         * make the isactive fild of the user 0 
         * remove the cookie from the user browser 
         * do the proper validaiton and error handling 
         * if the token of the user expires logout the user automatically 
         */

    
    // Validate the request data.
    $validatedData = $request->validate([
        'id' => 'required|numeric'
    ]);

    try {
        // Retrieve the user by ID.
        $user = User::findOrFail($validatedData['id']);

        // Set the isActive field to 0.
        $user->isActive = 0;
        $user->save();

        // Revoke the user's token.
        // $user->token()->revoke();

        $user->tokens->each(function ($token, $key) {
            $token->revoke();
        });

        // Delete the cookie named 'token' from the user's browser.
        $cookie = Cookie::forget('linktok_auth');

        // Return a JSON response indicating success and attach the cookie to the response.
        return response()->json(['message' => 'Logged out successfully.'], 200)->withCookie($cookie);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Return a JSON response indicating the user was not found.
        return response()->json(['error' => 'User not found.'], 404);
    } catch (\Exception $e) {
        // Return a JSON response indicating a server error.
        return response()->json(['error' => 'Server error.'], 500);
    }
    }
    
}
