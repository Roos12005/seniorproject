<?php

namespace App\Http\Controllers;

class PageController extends Controller{

    public function getAboutUs() {
       return view('home.about_us');
   }
}
?>
