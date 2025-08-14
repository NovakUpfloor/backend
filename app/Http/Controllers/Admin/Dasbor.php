<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Konfigurasi as KonfigurasiModel;
use Image;
use PDF;

class Dasbor extends Controller
{


    // Index
    public function index()
    {
        if(Session()->get('username')=="") {
            $last_page = url()->current();
            return redirect('login?redirect='.$last_page)->with(['warning' => 'Mohon maaf, Anda belum login']);
        }
    	$mysite = new KonfigurasiModel();
		$site 	= $mysite->listing();
       
		$data = array(  'title'     => $site->namaweb.' - '.$site->tagline,
                        'content'   => 'admin/dasbor/index'
                    );
        return view('admin/layout/wrapper',$data);
    }
}
