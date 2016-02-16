<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use App\Models\Call_Detail_Records;
use App\Models\Users;
use Illuminate\Http\Request;
use File;
use Neoxygen\NeoClient\ClientBuilder;

class DatabaseController extends Controller{
    
    public function index() {
        return view('admin.database');
    }
    
}
?>