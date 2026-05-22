<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Slider;
use Illuminate\Http\Request;
use App\Http\Requests\Client\StoreContactRequest;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class HomeController extends Controller
{
     public function HomeSlider(){
        $slider = Slider::find(1);
        return view('admin.backend.slider.get_slider',compact('slider'));
    }
    //End Method 

     public function UpdateSlider(Request $request){

        $slider_id = $request->id;
        $slider = Slider::find($slider_id);

        if ($request->file('image')) {
           $image = $request->file('image');
           $manager = new ImageManager(new Driver());
           $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();  
           $img = $manager->read($image);
           $img->resize(1696,729)->save(public_path('upload/slider/'.$name_gen));
           $save_url = 'upload/slider/'.$name_gen;

           if (file_exists(public_path($slider->image))) {
             @unlink(public_path($slider->image));
           }

           Slider::findOrFail($slider_id)->update([
            'title' => $request->title,
            'description' => $request->description,
            'link' => $request->link,
            'image' => $save_url,
           ]);

     $notification = array(
        'message' => 'Slider Updated Successfully',
        'alert-type' => 'success'
     ); 
     return redirect()->back()->with($notification); 

     } else {

        Slider::findOrFail($slider_id)->update([
            'title' => $request->title,
            'description' => $request->description,
            'link' => $request->link, 
           ]);

      $notification = array(
        'message' => 'Slider Updated Successfully',
        'alert-type' => 'success'
     ); 
     return redirect()->back()->with($notification); 

     }

    }
     //End Method 

    public function UpdateSliders(Request $request, $id){
        $slider = Slider::findOrFail($id);
        $slider->update($request->only(['title','description']));
        return response()->json(['success' => true, 'message' => 'Updated Successfully']); 
    }
    //End Method 

    public function UpdateSliderImage(Request $request, $id){
        $slider = Slider::findOrFail($id);

        if ($request->file('image')) {
           $image = $request->file('image');
           $manager = new ImageManager(new Driver());
           $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();  
           $img = $manager->read($image);
           $img->resize(1696,729)->save(public_path('upload/slider/'.$name_gen));
           $save_url = 'upload/slider/'.$name_gen;

           if (file_exists(public_path($slider->image))) {
             @unlink(public_path($slider->image));
           }

           $slider->update([ 
            'image' => $save_url,
           ]);

           return response()->json([
            'success' => true,
            'image_url' => asset($save_url),
            'message' => 'Image updated successfully'
           ]); 

        }

        return response()->json(['success' =>  false, 'message' => 'Image upload Failed'],400);
    }
     //End Method 

   public function UseCase(){
    return view('home.page.use_case');
   }
   //End Method 


   public function Features(){
    return view('home.page.features');
   }
   //End Method

     public function Pricing(){
    return view('home.page.pricing');
   }
   //End Method 

   

     public function Contact(){
    return view('home.page.contact');
   }
   //End Method 

   public function StoreContact(StoreContactRequest $request){
        
    Contact::create($request->validated());

    $notification = array(
        'message' => 'Contact submitted successfully',
        'alert-type' => 'success'
     ); 
     return redirect()->back()->with($notification); 

   }
   //End Method 

    public function ContactMessage(){
    $contact =  Contact::orderBy('id','desc')->get();
    return view('admin.backend.contact.all_contact',compact('contact'));
   }
    //End Method 

    public function DeleteContactMessage($id){

        Contact::findOrFail($id)->delete();

        $notification = array(
        'message' => 'Contact deleted successfully',
        'alert-type' => 'success'
     ); 
     return redirect()->back()->with($notification);  
    }
     //End Method 




   







     


}
