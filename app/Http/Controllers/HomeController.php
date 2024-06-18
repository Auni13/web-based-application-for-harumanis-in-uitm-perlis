<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use DB;
use Hash;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;

class HomeController extends Controller
{
    //

    public function index()
    {
        $product=Product::all();
        return view('home.userpage',compact('product'));
    }    

    public function redirect()
    {
        $usertype=Auth::user()->usertype;

        if($usertype=='1')
        {
            return view('admin.home');
        }
        else
        {
            $product=Product::paginate(3);
            return view('home.userpage',compact('product'));
        }
    }

    public function product_details($id)
    {
        $product=product::find($id);
        return view('home.product_details', compact('product'));
    }

    public function add_cart(Request $request, $id)
    {
        if(Auth::id())
        {
            $user=Auth::user();

            $product=product::find($id);
            $cart=new cart;

            $cart->name=$user->name;
            $cart->email=$user->email;
            $cart->phone=$user->phone;
            $cart->address=$user->address;
            $cart->user_id=$user->id;
            $cart->product_title=$product->title;
            $cart->price=$product->price * $request->quantity;
            $cart->image=$product->image;
            $cart->product_id=$product->id;
            $cart->quantity=$request->quantity;
            $cart->save();

            return redirect()->back();
        }
        else
        {
            return view('auth.login');
        }
        
    }

    public function show_cart()
    {
        if(Auth::id())
        {
            $id=Auth::user()->id;
            $cart=cart::where('user_id','=',$id)->get();
            return view('home.showcart', compact('cart'));
        }
        else
        {
            return view('auth.login');
        }
        
    }

    public function remove_cart($id)
    {
        $cart=cart::find($id);
        $cart->delete();

        return redirect()->back();
    }

    public function pay_order()
{
    $user = Auth::user();
    $userid = $user->id;
 
    $cartItems = cart::where('user_id', '=', $userid)->get();

    if ($cartItems->isEmpty()) {
        // Handle the case when the cart is empty or no cart items found
        // You can redirect or display an error message as per your requirement
        return redirect()->back()->with('error', 'Cart is empty');
    }

    $orders = []; // Create an empty array to store orders

    foreach ($cartItems as $item) {
        $order = new order;
        $order->name = $item->name;
        $order->email = $item->email;
        $order->phone = $item->phone;
        $order->address = $item->address;
        $order->user_id = $item->user_id;
        $order->product_title = $item->product_title;
        $order->price = $item->price;
        $order->quantity = $item->quantity;
        $order->image = $item->image;
        $order->product_id = $item->product_id;

        $order->payment_status = 'Online Banking';
        $order->delivery_status = 'Processing';

        $order->save();

        $orders[] = $order; // Add the order to the array
    }

    return view('home.pay_order', compact('orders'));
}



    public function payment_confirm(Request $request, $id)
    {
        $order=order::find($id);
        $order->name=$order->name;
        $order->email=$order->email;
        $order->phone=$order->phone;
        $order->address=$order->address;
        $order->user_id=$order->user_id;
        $order->product_title=$order->product_title;
        $order->price=$order->price;
        $order->quantity=$order->quantity;
        $order->product_id=$order->product_id;

        $payment=$request->payment_proof;
        $paymentpr=time().'.'.$payment->getClientOriginalExtension();
        $request->payment_proof->move('payment', $paymentpr);
        $order->payment_proof=$paymentpr;

        $order->delivery_status='Processing';

        $order->save();
        return redirect()->back()->with('message','Payment Success');
    }
}
