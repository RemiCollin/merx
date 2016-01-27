<?php

use Dvlpp\Merx\Exceptions\CartClosedException;
use Dvlpp\Merx\Exceptions\EmptyCartException;
use Dvlpp\Merx\Exceptions\NoCurrentCartException;
use Dvlpp\Merx\Exceptions\NoCurrentClientException;
use Dvlpp\Merx\Exceptions\OrderWithThisRefAlreadyExist;
use Dvlpp\Merx\Models\Cart;
use Dvlpp\Merx\Models\CartItem;
use Dvlpp\Merx\Models\Order;

class OrderTest extends TestCase
{

    /** @test */
    public function we_can_make_a_new_order()
    {
        $cart = Cart::create();
        session()->put("merx_cart_id", $cart->id);

        $client = $this->loginClient();

        $cart->addItem(new CartItem($this->itemAttributes()));

        $order = Order::create([
            "ref" => "123"
        ]);

        $this->seeInDatabase('merx_orders', [
            "id" => $order->id,
            "ref" => "123",
            "cart_id" => $cart->id,
            "client_id" => $client->id
        ]);

        $this->assertEquals("closed", $order->cart->state);
    }

    /** @test */
    public function we_cant_make_a_new_order_with_an_empty_cart()
    {
        $cart = Cart::create();
        session()->put("merx_cart_id", $cart->id);

        $this->loginClient();

        $this->setExpectedException(EmptyCartException::class);

        Order::create([
            "ref" => "123"
        ]);
    }

    /** @test */
    public function we_cant_make_a_new_order_without_a_client()
    {
        $cart = Cart::create();
        session()->put("merx_cart_id", $cart->id);

        $this->setExpectedException(NoCurrentClientException::class);

        Order::create([
            "ref" => "123"
        ]);
    }

    /** @test */
    public function we_cant_make_a_new_order_without_a_cart()
    {
        $this->loginClient();

        $this->setExpectedException(NoCurrentCartException::class);

        Order::create([
            "ref" => "123"
        ]);
    }

    /** @test */
    public function we_cant_make_an_new_order_with_a_closed_cart()
    {
        $cart = Cart::create();
        session()->put("merx_cart_id", $cart->id);

        $this->loginClient();

        $cart->addItem(new CartItem($this->itemAttributes()));

        $cart->close();

        $this->setExpectedException(CartClosedException::class);

        Order::create([
            "ref" => "123"
        ]);
    }

    /** @test */
    public function we_cant_make_create_an_order_with_an_existing_ref()
    {
        $this->loginClient();

        $this->setExpectedException(OrderWithThisRefAlreadyExist::class);

        for ($k = 0; $k < 2; $k++) {
            $cart = Cart::create();
            session()->put("merx_cart_id", $cart->id);

            $cart->addItem(new CartItem($this->itemAttributes()));

            Order::create([
                "ref" => "aaa"
            ]);
        }
    }
}
