<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use \App\models\cart as cart;
use \App\models\temporary_cart as temporary_cart;
use \App\models\cart_item as cart_item;


class addToCart extends Component
{
    public $product_id;
    public function render()
    {
        return view('livewire.add-to-cart');
    }
     
    public function mount($product_id)
    {
        $this->product_id = $product_id;
    }
    public function addToCart(Request $request)
    {
        //get current user add to cart
        $user = Auth::user();
        $product = \App\models\product::find($this->product_id);
        if(!$product){
            session()->flash('message', 'unable to add to cart');
            session()->flash('type', 'danger');
            return;
        } 

        if($user){
            $cart = $user->cart;
            if(!$cart){
                $cart = cart::create([
                    'user_id' => $user->id,
                    'status' => true,
                ]);
            }

            $cartItemToIncrement = $cart->cart_item->where('product_id',$product->id)->first();
            if($cartItemToIncrement){
                //product and cart item here
                if($product->stock <= $cartItemToIncrement->quantity){
                    session()->flash('message', 'stock limit exceeded.');
                    session()->flash('type', 'danger');
                    return;
                }
                $cartItemToIncrement->quantity++;
                $cartItemToIncrement->save();
            }else{
               cart_item::create([
                    'cart_id' => $cart->id,
                    'product_id' => $this->product_id,
                    'quantity' => 1,
                    'active' => true,
                ]);
            }
        }else{
            $uid = $request->cookie('user_id');
            if($uid){
                $temporary_cart = temporary_cart::get()->where('user_id',$uid)->first();
                if(!$temporary_cart){
                    $temporary_cart = temporary_cart::create([
                        'user_id' => $uid, 
                        'status' => true,
                    ]);
                }
                $incremented = false;
                foreach ($temporary_cart->cart_item as $item) {
                    if($product->id == $item->product_id){
                        $item->quantity++;
                        $item->save();
                        $incremented = true;
                    }
                }
                if(!$incremented){
                    cart_item::create([
                        'temporary_cart_id' => $temporary_cart->id,
                        'product_id' => $this->product_id,
                        'quantity' => 1,
                        'active' => true,
                    ]);
                }
            }
        }
        session()->flash('message', 'successfully added to cart');
        session()->flash('type', 'success');
        $this->emit('addToCart');

        
    }
}
