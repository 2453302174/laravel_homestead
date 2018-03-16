@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ route('product') }}" class="list-group-item list-group-item-action">当前库存</a>
                <a href="{{ route('inoutp') }}" class="list-group-item list-group-item-action">调整记录</a>
                <a href="{{ route('importp') }}" class="list-group-item list-group-item-action active">导入库存</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">批量导入库存</div>

                <div class="card-body">
                    <form action="{{ route('importp') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <div class="card small">
                                    <div class="card-header">衣服</div>
                                    <div class="card-body">
                                        <input name="products[coat]" type="file" />
                                        @if ($errors->has('products.coat'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('products.coat') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card small">
                                    <div class="card-header">裤子</div>
                                    <div class="card-body">
                                        <input name="products[trousers]" type="file" />
                                        @if ($errors->has('products.trousers'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('products.trousers') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card small" style="margin-top: 20px;">
                                    <div class="card-header">鞋子</div>
                                    <div class="card-body">
                                        <input name="products[shoes]" type="file" />
                                        @if ($errors->has('products.shoes'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('products.shoes') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col">
                                @if (session('import_status'))
                                    <div class="alert alert-success" style="margin-top: 20px;">
                                        {{ session('import_status') }}
                                    </div>
                                @endif
                                <button type="submit" class="btn btn-primary btn-block mb-2" style="margin-top: 20px;">开始导入</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pageend')
<script>
	
</script>
@parent
@endsection



