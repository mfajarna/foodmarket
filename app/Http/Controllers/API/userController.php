<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class userController extends Controller
{
    
    use PasswordValidationRules;

    // API LOGIN
    public function login(Request $request)
    {
        try {

            // validasi input
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // Mengecek credentials
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            // Jika Password tidak sesuai maka beri error
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password, [])){
                throw new \Exception('Invalid Credentials');
            }

            // Jika Berhasil maka login
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');

        } catch(Exception $error){
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }

    }
    
    // API REGISTER
    public function register(Request $request)
    {
        try{
            // Validasi dari inputan
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'uniqued:users'],
                'password' => $this->passwordRules()
            ]);
            
            // Membuat Data User
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'houseNumber' => $request->houseNumber,
                'phoneNumber' => $request->phoneNumber,
                'city' => $request->city,
                'password' => Hash::make($request->password)
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

        } catch(Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went Wrong',
                'error' => $error, 
            ], 'Authentication Failed', 500);
        }
    }

    // API LOGOUT
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        
        return ResponseFormatter::success($token, 'Token Revoked');
    }

    // API USER
    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(),'Data profile berhasil diambil');
        
    }

    // API update Profile
    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Update');
    
    }

    // API update Photo
    public function updatePhoto(Request $request)
    {
        // Validasi gambar
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048'
        ]);

        if($validator->fails()){
            return ResponseFormatter::error(
                ['error' => $validator->errors()],
                'Update Photo Failed',
                401
            );
        }

        if($request->file('file')){
            $file = $request->file->store('assets/user', 'public');

            // simpan foto ke database (url)

            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([$file], 'File Succesfully Uploaded');
        }
        // jangan lupa jalankan php artisan storage:link
    }

}
