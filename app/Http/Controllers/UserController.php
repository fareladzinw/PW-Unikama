<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\User;
use App\Kategori;
use App\Subkategori;
use App\Data;
use DB;

class UserController extends Controller
{
    public function dashboard(){
        $user_id = Auth::id();
        $kategori = Kategori::get();

        $subkategori = Subkategori::where('user_id','=',$user_id)->select('id');
        if($subkategori != null) {
            $data = Data::whereExists(function ($query) {
                $query->select(DB::raw(4))
                      ->from('subkategoris')
                      ->orderBy('id', 'desc')
                      ->whereRaw('subkategoris.id = data.id');
                })
                ->limit(4)
                ->get();

            return view('user-side.dashboard',['kategori' => $kategori,'data' => $data]);
        }
        else{
            return view('user-side.dashboard',['kategori' => $kategori]);
        }


    }

    public function category($kategori_id){
        $user_id = Auth::id();

        $kategori = Kategori::where('id','=',$kategori_id)
            ->get();

        $subkategori = Subkategori::where('kategori_id','=',$kategori_id)
            ->where('user_id','=',$user_id)
            ->get();

        return view('user-side.category',['kategori' => $kategori, 'subkategori' => $subkategori, 'kategori_id' => $kategori_id]);
    }

    public function subcategory($subkategori_id){
        $kategori = Subkategori::join('kategoris','kategoris.id','=','subkategoris.kategori_id')
            ->where('subkategoris.id','=',$subkategori_id)
            ->get(['kategoris.id as id', 'kategoris.nama_kategori as nama_kategori']);

        $subkategori = Subkategori::where('id','=',$subkategori_id)
            ->get();

        $data = Data::where('subkategori_id','=',$subkategori_id)
            ->get();

        return view('user-side.subcategory',['data' => $data, 'subkategori' => $subkategori, 'kategori' => $kategori, 'subkategori_id' => $subkategori_id]);        
    }

    public function subcategoryStore(Request $request, $kategori_id){

        $subkategori                    = new Subkategori;
        $subkategori->kategori_id       = $kategori_id;
        $subkategori->user_id           = Auth::id();
        $subkategori->nama_subkategori  = $request->nama_subkategori;
        $subkategori->save(); 



        return redirect()->route('category',$kategori_id)->with('alert-success','Berhasil tambah data');
    }

    public function searchfile(){
        return view('user-side.findfile');        
    }

    public function addfile($subkategori_id){
        $subkategori = Subkategori::where('id','=',$subkategori_id)
            ->get();

        return view('user-side.addfile',['subkategori'=>$subkategori]);
    }

    public function addfileStore(Request $request, $subkategori_id){
        $this->validate($request, [
            'filename' => 'required',
        ]);

        $photo = $request->file('filename');
        $filename = $photo->getFilename().'.'.$photo->getClientOriginalExtension();
        Storage::disk('uploads')->put($filename,File::get($photo));

        $data                   = new Data;
        $data->subkategori_id   = $subkategori_id;
        $data->nama_data        = $request->nama_data;
        $data->filename         = $filename;
        $data->save();

        return redirect()->route('subcategory',$subkategori_id);
    }

    public function deleteSubkategori($subkategori_id){
        Data::where('subkategori_id','=',$subkategori_id)->delete();
        Subkategori::where('id','=',$subkategori_id)->delete();

        return redirect()->back()->with('alert-success','Berhasil hapus data');
    }

    public function deleteFile($data_id){
        Data::where('id','=',$data_id)->delete();

        return redirect()->back();
    }
}
