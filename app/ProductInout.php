<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use League\Flysystem\Exception;

class ProductInout extends Model
{
    const CREATED_AT = 'createtime';
    const UPDATED_AT = 'updatetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'product_inout';
    protected $primaryKey = 'id_product_inout';
    
    protected $fillable = [
        'id_product', 'type', 'inout_key', 'inout_num',  'before_remain_num', 'after_remain_num'
    ];

    const TYPE_REPO_IMPORT_FROM_HUAYING = 'repo_import_from_huaying';
    const TYPE_REPO_IMPORT_FROM_LINGSHUI = 'repo_import_from_lingshui';
    const TYPE_REPO_IMPORT_FROM_CMP = 'repo_import_from_cmp';
    const TYPE_REPO_EXPORT_TO_HUAYING = 'repo_export_to_huaying';
    const TYPE_REPO_EXPORT_TO_LINGSHUI = 'repo_export_to_lingshui';
    const TYPE_REPO_EXPORT_TO_CMP = 'repo_export_to_cmp';
    const TYPE_REFUND = 'refund';
    const TYPE_SALEOUT = 'saleout';
    
    public static function repoDeploy($inoutp, &$error)
    {
        $inout_key = self::genInoutkey();
        
        try{
            DB::beginTransaction();
            foreach($inoutp as $id_product => $data){
                if(!empty($data['value'])){
                    if(empty($data['type'])){
                        $error = '请指定填写数量的调整类型。';
                        return false;
                    }
                    $product = Product::find($id_product);
            
                    $productInout = new self([
                        'id_product' => $id_product,
                        'type' => $data['type'],
                        'inout_key' => $inout_key,
                        'inout_num' => $data['value'],
                        'before_remain_num' => $product->remain_num,
                        'after_remain_num' => $product->remain_num + $data['value']
                    ]);
                    $productInout->save();
            
                    $product->remain_num = $productInout->after_remain_num;
                    $product->save();
                }
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
        }
        
        return $inout_key;
    }
    
    public static function formatImportFileData($activesheet, $type = 'coat')
    {
        $data = array();
        if($type == 'coat'){
            foreach ($activesheet->getRowIterator(4) as $k => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);
                $line = array();
                foreach ($cellIterator as $kk => $cell) {
                    $v = $cell->getValue();
                    if(in_array($kk, array('I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'))){
                        if(!empty($v)){
                            $line['size'][$kk] = $v;
                        }
                    }else{
                        $line[] = empty($v)? '' : $v;
                    }
                }
                $data[] = $line;
            }
        }else if($type == 'trousers'){
            foreach ($activesheet->getRowIterator(4) as $k => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);
                $line = array();
                foreach ($cellIterator as $kk => $cell) {
                    $v = $cell->getValue();
                    if(in_array($kk, array('I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'))){
                        if(!empty($v)){
                            $line['size'][$kk] = $v;
                        }
                    }else{
                        $line[] = empty($v)? '' : $v;
                    }
                }
                $data[] = $line;
            }
        }else if($type == 'shoes'){
            foreach ($activesheet->getRowIterator(4) as $k => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);
                $line = array();
                foreach ($cellIterator as $kk => $cell) {
                    $v = $cell->getValue();
                    if(in_array($kk, array('I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'))){
                        if(!empty($v)){
                            $line['size'][$kk] = $v;
                        }
                    }else{
                        $line[] = empty($v)? '' : $v;
                    }
                }
                $data[] = $line;
            }
        }
        
        return $data;
    }
    public static function importCoat($data, $type, $inout_key = null, &$error)
    {
        $inout_key = ($inout_key === null)? self::genInoutkey() : $inout_key;
    
        $sizeMatch = array(
            'J' => '42',
            'K' => '44',
            'L' => '46',
            'M' => '48',
            'N' => '50',
            'O' => '52',
            'P' => '54',
            'Q' => '56'
        );
        
        try{
            DB::beginTransaction();
            foreach($data as $line){
                $code = empty($line[0])? '' : $line[0];
                $name = empty($line[1])? '' : $line[1];
                $brand = empty($line[2])? '' : $line[2];
                $channel = $line[3] == '2'? Product::CHANNEL_PROXY : Product::CHANNEL_DEFAULT;
                $shop = empty($line[4])? '' : $line[4];
                $year = empty($line[5])? '' : $line[5];
                $colorcode = empty($line[6])? '' : $line[6];
                $color = empty($line[7])? '' : $line[7];
                $price = empty($line[9])? '' : $line[9];
                $price = round($price, 2);
                foreach($line['size'] as $sizeRow => $spec_size_num){
                    if($spec_size_num > 0){
                        $spec_size = isset($sizeMatch[$sizeRow])? $sizeMatch[$sizeRow] : null;
                        if($spec_size === null){
                            $error = '衣服文件：商品编码'.$code.': 尺寸不能识别，衣服文件整体取消导入';
                            DB::rollBack();
                            return false;
                        }
                        
                        $product = Product::where('code', $code)
                        ->where('spec_size', $spec_size)
                        ->where('channel', $channel)
                        ->where('shop', $shop)
                        ->first();
                        if(empty($product)){
                            $product = new Product([
                                'code' => $code,
                                'name' => $name,
                                'brand' => $brand,
                                'channel' => $channel,
                                'shop' => $shop,
                                'year' => $year,
                                'colorcode' => $colorcode,
                                'color' => $color,
                                'price' => $price,
                                'spec_size' => $spec_size,
                                'remain_num' => 0, 
                            ]);
                            $product->save();
                        }
                        
                        $productInout = new self([
                            'id_product' => $product->id_product,
                            'type' => $type,
                            'inout_key' => $inout_key,
                            'inout_num' => $spec_size_num,
                            'before_remain_num' => $product->remain_num,
                            'after_remain_num' => $product->remain_num + $spec_size_num
                        ]);
                        $productInout->save();
                        
                        $product->remain_num = $productInout->after_remain_num;
                        $product->save();
                    }
                }
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
        }
    
        return $inout_key;
    }
    
    public static function importTrousers($data, $type, $inout_key = null, &$error)
    {
        $inout_key = ($inout_key === null)? self::genInoutkey() : $inout_key;
    
        $sizeMatch = array(
            'J' => '29',
            'K' => '30',
            'L' => '31',
            'M' => '32',
            'N' => '33',
            'O' => '34',
            'P' => '35',
            'Q' => '36',
            'R' => '37',
            'S' => '38',
            'T' => '39',
            'U' => '40',
            'V' => '41',
            'W' => '42'
        );
    
        try{
            DB::beginTransaction();
            foreach($data as $line){
                $code = empty($line[0])? '' : $line[0];
                $name = empty($line[1])? '' : $line[1];
                $brand = empty($line[2])? '' : $line[2];
                $channel = $line[3] == '2'? Product::CHANNEL_PROXY : Product::CHANNEL_DEFAULT;
                $shop = empty($line[4])? '' : $line[4];
                $year = empty($line[5])? '' : $line[5];
                $colorcode = empty($line[6])? '' : $line[6];
                $color = empty($line[7])? '' : $line[7];
                $price = empty($line[9])? '' : $line[9];
                $price = round($price, 2);
                foreach($line['size'] as $sizeRow => $spec_size_num){
                    if($spec_size_num > 0){
                        $spec_size = isset($sizeMatch[$sizeRow])? $sizeMatch[$sizeRow] : null;
                        if($spec_size === null){
                            $error = '裤子文件：商品编码'.$code.': 尺寸不能识别，裤子文件整体取消导入。';
                            DB::rollBack();
                            return false;
                        }
    
                        $product = Product::where('code', $code)
                        ->where('spec_size', $spec_size)
                        ->where('channel', $channel)
                        ->where('shop', $shop)
                        ->first();
                        if(empty($product)){
                            $product = new Product([
                                'code' => $code,
                                'name' => $name,
                                'brand' => $brand,
                                'channel' => $channel,
                                'shop' => $shop,
                                'year' => $year,
                                'colorcode' => $colorcode,
                                'color' => $color,
                                'price' => $price,
                                'spec_size' => $spec_size,
                                'remain_num' => 0,
                            ]);
                            $product->save();
                        }
    
                        $productInout = new self([
                            'id_product' => $product->id_product,
                            'type' => $type,
                            'inout_key' => $inout_key,
                            'inout_num' => $spec_size_num,
                            'before_remain_num' => $product->remain_num,
                            'after_remain_num' => $product->remain_num + $spec_size_num
                        ]);
                        $productInout->save();
    
                        $product->remain_num = $productInout->after_remain_num;
                        $product->save();
                    }
                }
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
        }
    
        return $inout_key;
    }
    
    public static function importShoes($data, $type, $inout_key = null, &$error)
    {
        $inout_key = ($inout_key === null)? self::genInoutkey() : $inout_key;
    
        $sizeMatch = array(
            'Q' => '36',
            'R' => '37',
            'S' => '38',
            'T' => '39',
            'U' => '40',
            'V' => '41',
            'W' => '42',
            'X' => '43',
            'Y' => '44'
        );
    
        try{
            DB::beginTransaction();
            foreach($data as $line){
                $code = empty($line[0])? '' : $line[0];
                $name = empty($line[1])? '' : $line[1];
                $brand = empty($line[2])? '' : $line[2];
                $channel = $line[3] == '2'? Product::CHANNEL_PROXY : Product::CHANNEL_DEFAULT;
                $shop = empty($line[4])? '' : $line[4];
                $year = empty($line[5])? '' : $line[5];
                $colorcode = empty($line[6])? '' : $line[6];
                $color = empty($line[7])? '' : $line[7];
                $price = empty($line[9])? '' : $line[9];
                $price = round($price, 2);
                foreach($line['size'] as $sizeRow => $spec_size_num){
                    if($spec_size_num > 0){
                        $spec_size = isset($sizeMatch[$sizeRow])? $sizeMatch[$sizeRow] : null;
                        if($spec_size === null){
                            $error = '鞋子文件：商品编码'.$code.': 尺寸不能识别，鞋子文件整体取消导入';
                            DB::rollBack();
                            return false;
                        }
    
                        $product = Product::where('code', $code)
                        ->where('spec_size', $spec_size)
                        ->where('channel', $channel)
                        ->where('shop', $shop)
                        ->first();
                        if(empty($product)){
                            $product = new Product([
                                'code' => $code,
                                'name' => $name,
                                'brand' => $brand,
                                'channel' => $channel,
                                'shop' => $shop,
                                'year' => $year,
                                'colorcode' => $colorcode,
                                'price' => $price,
                                'spec_size' => $spec_size,
                                'remain_num' => 0,
                            ]);
                            $product->save();
                        }
    
                        $productInout = new self([
                            'id_product' => $product->id_product,
                            'type' => $type,
                            'inout_key' => $inout_key,
                            'inout_num' => $spec_size_num,
                            'before_remain_num' => $product->remain_num,
                            'after_remain_num' => $product->remain_num + $spec_size_num
                        ]);
                        $productInout->save();
    
                        $product->remain_num = $productInout->after_remain_num;
                        $product->save();
                    }
                }
            }
            DB::commit();
        }catch (Exception $e){
            DB::rollBack();
        }
    
        return $inout_key;
    }
    
    public function getTypetxt()
    {
        if($this->type == self::TYPE_REPO_IMPORT_FROM_HUAYING){
            return '华蓥调入';
        }else if($this->type == self::TYPE_REPO_IMPORT_FROM_LINGSHUI){
            return '邻水调入';
        }else if($this->type == self::TYPE_REPO_IMPORT_FROM_CMP){
            return '公司调入';
        }else if($this->type == self::TYPE_REPO_EXPORT_TO_HUAYING){
            return '调出至华蓥';
        }else if($this->type == self::TYPE_REPO_EXPORT_TO_LINGSHUI){
            return '调出至邻水';
        }else if($this->type == self::TYPE_REPO_EXPORT_TO_CMP){
            return '调出至公司';
        }else if($this->type == self::TYPE_REFUND){
            return '客户退货';
        }else if($this->type == self::TYPE_SALEOUT){
            return '销售出货';
        }
    }
    
    public static function genInoutkey()
    {
        return rand(100000, 999999) . date('YmdHis');
    }
    
    public function product()
    {
        return $this->hasOne('App\Product', 'id_product', 'id_product');
    }
}
