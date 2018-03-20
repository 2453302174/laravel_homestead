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
                    <form class="fadetoggle" action="{{ route('inoutp') }}" method="GET" style="position: sticky; top: 0; z-index: 99; background-color: #FFF;">
                        <div class="row">
                            <div class="col">
                                <div class="productcodeinput combo-func1" combo-name="code"></div>
                            </div>
                            <div class="col">
                                <div class="productname combo-func1" combo-name="name"></div>
                            </div>
                            <div class="col">
                                <div class="productsize combo-func1" combo-name="spec_size"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="shopinput combo-func1" combo-name="shop"></div>
                            </div>
                            <div class="col">
                                <div class="typeinput combo-func1" combo-name="type"></div>
                            </div>
                            <div class="col">
                                <div class="productchannel combo-func1" combo-name="channel"></div>
                            </div>
                            <div class="col">
                                <div class="productyear combo-func1" combo-name="year"></div>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary mb-2">筛选记录</button>
                            </div>
                        </div>
                        
                        <a class="fadetoggle-btn" href="javascript: {}">收起/展开</a>
                    </form>
                    <hr />
                
                    <table class="table table-bordered table-hover text-center tbs1">
                        <tr style="position: sticky; top: 10px; background-color: #fff;">
<!--                             <th>操作码</th> -->
                            <th>调整时间</th>
                            <th>调整类型</th>
                            <th>调整数量</th>
                            <th>商品编码</th>
                            <th>商品名称</th>
                            <th>产品年份</th>
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
                                    <td>{{ $productInout->product->year }}</td>
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
	$(document).ready(function(){
		$(".typeinput").jqxComboBox({
			placeHolder: '调整类型', 
			multiSelect: true, 
			source: <?php echo json_encode($cond_auto_types); ?>, 
			selectedIndex: 0, 
			height: 30
		});
		$.each(<?php echo json_encode($cond['type']); ?>, function(k, v){
			$(".typeinput").jqxComboBox('selectItem', v);
		});
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

		$('.combo-func1').on('change', function(e){
			var thisObj = $(this);
			thisObj.children('.selectedvalue').remove();
			
			var items = thisObj.jqxComboBox('getSelectedItems'); 
			$.each(items, function(k, item){
				var name = thisObj.attr('combo-name');
				thisObj.append('<input type="hidden" class="selectedvalue" name="cond['+name+'][]" value="'+item.value+'" />');
			});
		});
		$('.combo-func1').change();
	});
</script>
@parent
@endsection



