<div>
    <button type="button" class="btn btn-primary btn-block btn-md mr-1 mb-2" wire:click="addToCart"><i class="fas fa-shopping-basket"></i> Sepete Ekle</button>

    @if (session()->has('message'))
            <div class="alert alert-{{session()->get('type')}}">
            {{ session('message') }}
        </div>
    @endif
</div>
