<?php

namespace App\Http\Controllers;

class AboutUsController extends Controller{

    public function getAboutUs() {
       return view('home.about_us');
   }
}
?>
