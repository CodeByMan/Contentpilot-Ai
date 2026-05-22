<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Heading;
use Illuminate\Http\Request;

class HeadingController extends Controller
{
    public function AllHeading(){
    $heading = Heading::latest()->get();
    return view('admin.backend.heading.all_heading',compact('heading'));
   }
   //End Method

   public function AddHeading(){
    
    return view('admin.backend.heading.add_heading');
   }
   //End Method 

   public function StoreHeading(Request $request){
        Heading::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

    $notification = array(
        'message' => 'Heading Inserted Successfully',
        'alert-type' => 'success'
     ); 
     return redirect()->route('all.heading')->with($notification); 

   }
   //End Method 

   public function EditHeading($id) {
    $headings = Heading::findOrFail($id);

    return view('admin.backend.heading.edit_heading' , compact('headings'));
   }

   public function UpdateHeading(Request $request) {
    $heading_id = $request->id;

    Heading::findOrFail($heading_id)->update([
            'title' => $request->title,
            'description' => $request->description,
           
           ]);

     $notification = array(
        'message' => 'Heading is  Updated Successfully',
        'alert-type' => 'success'
     ); 
     return redirect()->route('all.heading')->with($notification); 


   }

   public function DeleteHeading($id) {
    Heading::findOrFail($id)->delete();

    $notification = array(
        'message' => 'Heading is  Delete Successfully',
        'alert-type' => 'success'
     ); 
     return redirect()->route('all.heading')->with($notification); 

   }

   public function UpdateStarted(Request $request, $id){
        $heading = Heading::findOrFail($id);
        $heading->update($request->only(['title','description']));
        return response()->json(['success' => true, 'message' => 'Updated Successfully']); 
    }
    //End Method 

    


}
