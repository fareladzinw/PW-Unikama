<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\Aktivasi;
use Carbon\Carbon;
use App\Mail\UserActivation;

class AuthController extends Controller
{ 
    public function indexLogin() 
    {
        return view('login');
    }

    public function checkLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);
        $credentials = $request->only('username', 'password');
        if (Auth::attempt($credentials)) {
            return redirect('/home');
        }
        else {
            return redirect('/login')->with('alert-fail','Email atau password salah!');
        }
    }

    public function indexRegister() 
    {
        return view('signUp');
    }


    public function postRegister(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'username' => 'required',
            'password' => 'required',
            ]);

        $data = new User();
        $data->nama = $request->name;
        $data->username = $request->username;
        $data->password = bcrypt($request->password);
        $data->save();

        return redirect('/login');
    }
    public function logout()
    {
        Auth::logout();
        return redirect('login')->with('alert-logout','Berhasil Logout!');
    }
}