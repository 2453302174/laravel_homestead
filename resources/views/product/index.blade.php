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
                    <form id="pexcel-form" action="{{ route('exportexcelp') }}" method="GET" target="__blank"></form>
                
                    <form class="forbidden fadetoggle" action="{{ route('product') }}" method="GET" style="position: sticky; top: 0; z-index: 99; background-color: #FFF;">
                        <div class="row">
                            <div class="col">
                                <div class="shopinput combo-func1" combo-name="shop"></div>
                            </div>
                            <div class="col">
                                <div class="productcodeinput combo-func1" combo-name="code"></div>
                            </div>
                            <div class="col">
                                <div class="productname combo-func1" combo-name="name"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="productchannel combo-func1" combo-name="channel"></div>
                            </div>
                            <div class="col">
                                <div class="productsize combo-func1" combo-name="spec_size"></div>
                            </div>
                            <div class="col">
                                <div class="productyear combo-func1" combo-name="year"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="productbrand combo-func1" combo-name="brand"></div>
                            </div>
                            <div class="col">
                                <input class="form-control" type="text" name="cond[pageSize]" value="{{ $cond['pageSize'] }}" />
                            </div>
                            <div class="col">
                                <button type="button" class="submit-btn btn btn-primary mb-2">筛选记录</button>
                                <button id="export-excel-btn" type="button" class="btn btn-primary mb-2">导出成excel</button>
                            </div>
                        </div>
                        
                        <a class="fadetoggle-btn" href="javascript: {}">收起/展开</a>
                    </form>
                    <hr />
                
                    <form method="POST" action="{{ route('inoutadjust') }}">
                        @csrf
                        <table class="table table-bordered table-hover text-center tbs1">
                            <tr style="position: sticky; top: 10px; z-index: 98;">
                                <th>商品代码</th>
                                <th>商品名称</th>
                                <th>年份</th>
                                <th>所在门店</th>
                                <th>品牌</th>
                                <th>渠道</th>
                                <th>尺码</th>
                                <th>库存余量</th>
                                <th>单价</th>
                                <th>库存总额</th>
                                <th>入库时间</th>
                                <th>库存调整</th>
                            </tr>
                            <?php  
                                $remain_num_sum = 0; 
                                $remain_amount = 0;
                            ?>
                            @foreach ($productsChunk as $code => $details)
                                @foreach ($details['records'] as $k => $product)
                                    <?php 
                                        $remain_num_sum += $product->remain_num;
                                        $remain_amount += $product->price * $product->remain_num;
                                    ?>
                                    <tr>
                                        @if($k == 0)
                                            <td class="rowspan" rowspan={{ count($details['records']) }}>{{ $details['code'] }}</td>
                                            <td class="rowspan" rowspan={{ count($details['records']) }}>{{ $details['name'] }}</td>
                                        @endif
                                        <td>{{ $product->year }}</td>
                                        <td>{{ $product->shop }}</td>
                                        <td>{{ $product->brand }}</td>
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
                                                        \Collective\Html\FormFacade::select(
                                                            'inoutp[' . $product->id_product . '][type]',
                                                            [
                                                                '' => '请选择', 
                                                                \App\ProductInout::TYPE_REPO_IMPORT_FROM_HUAYING => '华蓥调入',
                                                                \App\ProductInout::TYPE_REPO_IMPORT_FROM_LINGSHUI => '邻水调入',
                                                                \App\ProductInout::TYPE_REPO_IMPORT_FROM_CMP => '公司调入',
                                                                \App\ProductInout::TYPE_REPO_EXPORT_TO_HUAYING => '调出至华蓥',
                                                                \App\ProductInout::TYPE_REPO_EXPORT_TO_LINGSHUI => '调出至邻水',
                                                                \App\ProductInout::TYPE_REPO_EXPORT_TO_CMP => '调出至公司',
                                                                \App\ProductInout::TYPE_REFUND => '客户退货',
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
                            
                            <tr>
                                <td>小计</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><?php echo $remain_num_sum; ?></td>
                                <td></td>
                                <td><?php echo $remain_amount; ?>元</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                        
                        {{ $products->links() }}
                        
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
	$(document).ready(function(){
		$(".shopinput").jqxComboBox({
			placeHolder: '所在门店', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_shops); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['shop']); ?>, function(k, v){
			$(".shopinput").jqxComboBox('selectItem', v);
		});
		$(".productcodeinput").jqxComboBox({
			placeHolder: '商品编码', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_codes); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['code']); ?>, function(k, v){
			$(".productcodeinput").jqxComboBox('selectItem', v);
		});
		$(".productname").jqxComboBox({
			placeHolder: '商品名称', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_names); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['name']); ?>, function(k, v){
			$(".productname").jqxComboBox('selectItem', v);
		});
		$(".productchannel").jqxComboBox({
			placeHolder: '渠道', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_channels); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['channel']); ?>, function(k, v){
			$(".productchannel").jqxComboBox('selectItem', v);
		});
		$(".productsize").jqxComboBox({
			placeHolder: '商品尺码', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_spec_sizes); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['spec_size']); ?>, function(k, v){
			$(".productsize").jqxComboBox('selectItem', v);
		});
		$(".productyear").jqxComboBox({
			placeHolder: '产品年份', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_years); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['year']); ?>, function(k, v){
			$(".productyear").jqxComboBox('selectItem', v);
		});
		$(".productbrand").jqxComboBox({
			placeHolder: '品牌', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_brands); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['brand']); ?>, function(k, v){
			$(".productbrand").jqxComboBox('selectItem', v);
		});

		$('.combo-func1').on('change', function(e){
			var thisObj = $(this);
			thisObj.children('.selectedvalue').remove();
			
			var items = thisObj.jqxComboBox('getSelectedItems'); 
			$.each(items, function(k, item){
				var name = thisObj.attr('combo-name');
				thisObj.append('<input type="hidden" class="selectedvalue" name="cond['+name+'][]" value="'+item.value+'" />');
				$('#pexcel-form').append('<input type="hidden" class="selectedvalue" name="cond['+name+'][]" value="'+item.value+'" />');
			});
		});
		$('.combo-func1').change();


		$('#export-excel-btn').on('click', function(){
			$('#pexcel-form').submit();
			return false;
		});
	});
</script>
@parent
@endsection