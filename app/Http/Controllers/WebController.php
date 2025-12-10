<?php

namespace App\Http\Controllers;

use App\Models\Seo;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Models\TermCondition;
use App\Models\AboutUs;
class WebController extends Controller
{
    //

    public function homepage()
    {
       $seo = Seo::where('page', 'home')->first();

return view('web.homepage', [
    'seo_title' => $seo->title,
    'seo_description' => $seo->description,
    'seo_keywords' => $seo->keywords,
    'seo_og_title' => $seo->og_title,
    'seo_og_description' => $seo->og_description,
]);

    }

//     public function aboutpage()
//     {
//        $seo = Seo::where('page', 'about')->first();

// return view('web.aboutpage', [
//     'seo_title' => $seo->title,
//     'seo_description' => $seo->description,
//     'seo_keywords' => $seo->keywords,
//     'seo_og_title' => $seo->og_title,
//     'seo_og_description' => $seo->og_description,
// ]);

//     }

    public function Contactuspage()
    {
       $seo = Seo::where('page', 'contact')->first();

return view('web.contact', [
    'seo_title' => $seo->title,
    'seo_description' => $seo->description,
    'seo_keywords' => $seo->keywords,
    'seo_og_title' => $seo->og_title,
    'seo_og_description' => $seo->og_description,
]);

    }

	 public function termsConditionspage() {

        $data = TermCondition::first();
        return view('web.termsConditions', compact('data'));
    }



    public function privacyPolicy() {

        $data = PrivacyPolicy::first();
        return view('web.privacy', compact('data'));
    }


    public function Aboutpage() {
	 $data = AboutUs::first();
    return view('web.aboutpage', compact('data'));
}



}