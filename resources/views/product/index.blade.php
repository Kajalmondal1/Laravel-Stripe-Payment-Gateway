<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50" >
      <div style="display : flex; gap : 2rem">
        @foreach ($products as $product)
          <div class="flex:1">
            <img src="{{$product->image}}" style="width: 100px;height:100px" />
            <h2>{{$product->name}}</h2>
            <p>${{$product->price}}</p>
          </div>
        @endforeach
      </div>
      <p>
          <form action="{{route('checkout')}}" method="POST">
            @csrf
            
            <button>Checkout</button>
        </form>
      </p>

    </body>
</html>
