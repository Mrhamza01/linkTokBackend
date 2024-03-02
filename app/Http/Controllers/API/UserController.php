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

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
{
    /**
     *          logic
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
    $data = $request->only('firstname', 'lastname', 'email', 'password');

    // Validate the request
    $validator = Validator::make($data, [
        'firstname' => 'required|string|max:10',
        'lastname' => 'required|string|max:10',
        'email' => 'required|string|email|max:50|unique:users',
        'password' => 'required|string|min:8',
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
        $token = $user->createToken('auth_token')->accessToken;
       
        // End the database transaction
        DB::commit();

        // Send token in the cookie
        $cookie = cookie('auth_token', $token, 60 * 24); // create a cookie valid for 24 hours

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
         * check the email and password is same as in the database 
         * if user is authenticated then
         * gernate token and send it in cookie 
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
        // If user is authenticated, generate a token using passport
        $token = Auth::user()->createToken('auth_token')->accessToken;

        // Send token in the cookie
        $cookie = cookie('auth_token', $token, 60 * 24); // create a cookie valid for 24 hours

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function UpdateBio(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function resetpassword(string $id)
    {
        //
    }

    
}
