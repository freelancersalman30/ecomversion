@php
    $subtotal = Cart::instance('pos_shopping')->subtotal();
    $subtotal = str_replace(',','',$subtotal);
    $subtotal = str_replace('.00', '',$subtotal);
    $shipping = Session::get('pos_shipping', 0);
    $total_discount = Session::get('pos_discount', 0) + Session::get('product_discount', 0);
@endphp
<tr>
    <td>Sub Total</td>
    <td>{{$subtotal}}</td>
</tr>
<tr>
    <td>Shipping Fee</td>
    <td>{{$shipping}}</td>
</tr>
<tr>
    <td>Discount</td>
    <td>{{$total_discount}}</td>
</tr>
<tr>
    <td>Total</td>
    <td>{{($subtotal + $shipping)- $total_discount}}</td>
</tr>