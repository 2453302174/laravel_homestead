@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="list-group" style="position: sticky; top: 10px;">
                <a href="{{ route('product') }}" class="list-group-item list-group-item-action active">当前库存</a>
                <a href="{{ route('inoutp') }}" class="list-group-item list-group-item-action">调整记录</a>
                <a href="{{ route('importp') }}" class="list-group-item list-group-item-action">导入库存</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">当前库存</div>

                <div class="card-body">
                    <form action="{{ route('product') }}" method="GET" style="position: sticky; top: 0; z-index: 99; background-color: #FFF;">
                        <div class="row">
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">所在门店</span>
                                    </div>
                                    <input value="{{ isset($cond['shop'])? $cond['shop'] : '' }}" type="text" class="form-control" name="cond[shop]" placeholder="" />
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">商品编码</span>
                                    </div>
                                    <input value="{{ isset($cond['code'])? $cond['code'] : '' }}" type="text" class="form-control" name="cond[code]" placeholder="" />
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">商品名称</span>
                                    </div>
                                    <input value="{{ isset($cond['name'])? $cond['name'] : '' }}" type="text" class="form-control" name="cond[name]" placeholder="" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">渠道</span>
                                    </div>
                                    {{
                                        Form::select(
                                            'cond[channel]',
                                            [
                                                '' => '请选择', 
                                                \App\Product::CHANNEL_DEFAULT => '默认', 
                                                \App\Product::CHANNEL_PROXY => '代销'
                                            ], 
                                            old('cond.channel'),
                                            array(
                                                'class' => '',
                                            )
                                        )
                                    }}
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">商品尺码</span>
                                    </div>
                                    <input value="{{ isset($cond['spec_size'])? $cond['spec_size'] : '' }}" type="text" class="form-control" name="cond[spec_size]" placeholder="" />
                                </div>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary mb-2">筛选记录</button>
                            </div>
                        </div>
                    </form>
                    <hr />
                
                    <form method="POST" action="{{ route('inoutadjust') }}">
                        @csrf
                        <table class="table table-bordered table-hover text-center tbs1">
                            <tr>
                                <th>商品代码</th>
                                <th>商品名称</th>
                                <th>所在门店</th>
                                <th>渠道</th>
                                <th>尺码</th>
                                <th>库存余量</th>
                                <th>单价</th>
                                <th>库存总额</th>
                                <th>入库时间</th>
                                <th>库存调整</th>
                            </tr>
                            @foreach ($productsChunk as $code => $details)
                                @foreach ($details['records'] as $k => $product)
                                    <tr>
                                        @if($k == 0)
                                            <td class="rowspan" rowspan={{ count($details['records']) }}>{{ $details['code'] }}</td>
                                            <td class="rowspan" rowspan={{ count($details['records']) }}>{{ $details['name'] }}</td>
                                        @endif
                                        <td>{{ $product->shop }}</td>
                                        <td>{{ $product->getChanneltxt() }}</td>
                                        <td>{{ $product->spec_size }}</td>
                                        <td>{{ $product->remain_num }}</td>
                                        <td>{{ $product->price }}元</td>
                                        <td>{{ $product->price * $product->remain_num }}元</td>
                                        <td class="datetime">{{ $product->createtime }}</td>
                                        <td>
                                            <div class="input-group">
                                                <input value="{{ old('inoutp.' . $product->id_product . '.value') }}" type="text" class="form-control{{ $errors->has('inoutp.' . $product->id_product) ? ' is-invalid' : '' }}" name="inoutp[{{ $product->id_product }}][value]" />
                                                <div class="input-group-append">
                                                    {{
                                                        Form::select(
                                                            'inoutp[' . $product->id_product . '][type]',
                                                            [
                                                                '' => '请选择', 
                                                                \App\ProductInout::TYPE_REPO_DEPLOY => '库存调配', 
                                                                \App\ProductInout::TYPE_SALEOUT => '销售出货'
                                                            ], 
                                                            old('inoutp.' . $product->id_product . 'type'),
                                                            array(
                                                                'class' => '',
                                                            )
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                            @if ($errors->has('inoutp.' . $product->id_product . '.value'))
                                                <span class="invalid-feedback">
                                                    <strong>{{ $errors->first('inoutp.' . $product->id_product . '.value') }}</strong>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </table>
                        
                        <div class="form-group row mb-0" style="position: sticky; bottom: 0; padding: 10px 0; background-color: #fff;">
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    批量调整库存
                                </button>
                            </div>
                            <div class="col-md-9">
                                @if (session('inout_status'))
                                    <div class="alert alert-success mb-0" style="width: 500px;">
                                        {{ session('inout_status') }}
                                    </div>
                                @endif
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