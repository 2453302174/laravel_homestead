@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ route('product') }}" class="list-group-item list-group-item-action">当前库存</a>
                <a href="{{ route('inoutp') }}" class="list-group-item list-group-item-action active">调整记录</a>
                <a href="{{ route('importp') }}" class="list-group-item list-group-item-action">导入库存</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">库存调整记录</div>

                <div class="card-body">
                    <form action="{{ route('inoutp') }}" method="GET" style="position: sticky; top: 0; z-index: 99; background-color: #FFF;">
                        <div class="row">
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
                            <div class="col">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">商品尺码</span>
                                    </div>
                                    <input value="{{ isset($cond['spec_size'])? $cond['spec_size'] : '' }}" type="text" class="form-control" name="cond[spec_size]" placeholder="" />
                                </div>
                            </div>
                        </div>
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
                                        <span class="input-group-text" id="basic-addon1">调整类型</span>
                                    </div>
                                    {{
                                        Form::select(
                                            'cond[type]',
                                            [
                                                '' => '请选择', 
                                                \App\ProductInout::TYPE_REPO_IMPORT => '库存导入', 
                                                \App\ProductInout::TYPE_REPO_DEPLOY => '库存调配', 
                                                \App\ProductInout::TYPE_SALEOUT => '销售出货'
                                            ], 
                                            isset($cond['type'])? $cond['type'] : '',
                                            array(
                                                'class' => 'form-control',
                                            )
                                        )
                                    }}
                                </div>
                            </div>
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
                                <button type="submit" class="btn btn-primary mb-2">筛选记录</button>
                            </div>
                        </div>
                    </form>
                    <hr />
                
                    <table class="table table-bordered table-hover text-center tbs1">
                        <tr>
<!--                             <th>操作码</th> -->
                            <th>调整时间</th>
                            <th>调整类型</th>
                            <th>调整数量</th>
                            <th>商品编码</th>
                            <th>商品名称</th>
                            <th>所在门店</th>
                            <th>商品尺码</th>
                            <th>渠道</th>
                        </tr>
                        @foreach ($productInoutsChunk as $inout_key => $details)
                            @foreach ($details['records'] as $k => $productInout)
                                <tr>
                                    @if($k == 0)
                                        <!-- 
                                        <td class="rowspan" rowspan={{ count($details['records']) }}>{{ $details['inout_key'] }}</td>
                                         -->
                                        <td class="datetime rowspan" rowspan={{ count($details['records']) }}>{{ $details['createtime'] }}</td>
                                    @endif
                                    <td>{{ $productInout->getTypetxt() }}</td>
                                    <td>
                                        本次操作{{ $productInout->inout_num>0? '+'.$productInout->inout_num : $productInout->inout_num }}, 使原数量 {{ $productInout->before_remain_num }}--->{{ $productInout->after_remain_num }}
                                    </td>
                                    <td>{{ $productInout->product->code }}</td>
                                    <td>{{ $productInout->product->name }}</td>
                                    <td>{{ $productInout->product->shop }}</td>
                                    <td>{{ $productInout->product->spec_size }}</td>
                                    <td>{{ $productInout->product->getChanneltxt() }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </table>
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



