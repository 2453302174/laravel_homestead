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
                    if(!in_array($kk, array('H', 'I', 'J', 'K', 'L', 'M', 'N'))){
                        $line[] = $cell->getValue();
                    }else{
                        $line['size'][$kk] = $cell->getValue();
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
                    if(!in_array($kk, array('H', 'I', 'J', 'K', 'L', 'M', 'N'))){
                        $line[] = $cell->getValue();
                    }else{
                        $line['size'][$kk] = $cell->getValue();
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
                    if(!in_array($kk, array('I', 'J', 'K', 'L', 'M', 'N', 'O'))){
                        $line[] = $cell->getValue();
                    }else{
                        $line['size'][$kk] = $cell->getValue();
                    }
                }
                $data[] = $line;
            }
        }
        
        return $data;
    }
    public static function importCoat($data, $inout_key = null, &$error)
    {
        $inout_key = ($inout_key === null)? self::genInoutkey() : $inout_key;
    
        $sizeMatch = array(
            'H' => 42, 
            'I' => 44, 
            'J' => 46, 
            'K' => 48, 
            'L' => 50, 
            'M' => 52, 
            'N' => 54,     
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
                $price = empty($line[8])? '' : $line[8];
                foreach($line['size'] as $sizeRow => $spec_size_num){
                    if($spec_size_num > 0){
                        $spec_size = isset($sizeMatch[$sizeRow])? $sizeMatch[$sizeRow] : null;
                        if($spec_size === null){
                            $error = '商品编码'.$code.': 尺寸不能识别';
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
                            'type' => self::TYPE_REPO_IMPORT,
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
    
    public static function importTrousers($data, $inout_key = null, &$error)
    {
        $inout_key = ($inout_key === null)? self::genInoutkey() : $inout_key;
    
        $sizeMatch = array(
            'H' => 29,
            'I' => 30,
            'J' => 31,
            'K' => 32,
            'L' => 33,
            'M' => 34,
            'N' => 35,
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
                $price = empty($line[8])? '' : $line[8];
                foreach($line['size'] as $sizeRow => $spec_size_num){
                    if($spec_size_num > 0){
                        $spec_size = isset($sizeMatch[$sizeRow])? $sizeMatch[$sizeRow] : null;
                        if($spec_size === null){
                            $error = '商品编码'.$code.': 尺寸不能识别';
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
                            'type' => self::TYPE_REPO_IMPORT,
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
    
    public static function importShoes($data, $inout_key = null, &$error)
    {
        $inout_key = ($inout_key === null)? self::genInoutkey() : $inout_key;
    
        $sizeMatch = array(
            'I' => 37,
            'J' => 38,
            'K' => 39,
            'L' => 40,
            'M' => 41,
            'N' => 42,
            'O' => 43,
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
                $price = empty($line[9])? '' : $line[9];
                foreach($line['size'] as $sizeRow => $spec_size_num){
                    if($spec_size_num > 0){
                        $spec_size = isset($sizeMatch[$sizeRow])? $sizeMatch[$sizeRow] : null;
                        if($spec_size === null){
                            $error = '商品编码'.$code.': 尺寸不能识别';
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
                            'type' => self::TYPE_REPO_IMPORT,
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
