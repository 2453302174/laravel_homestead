<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\ProductInout;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Rules\ProductImportFile;
use League\Flysystem\Exception;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $cond = $request->input('cond', array());
        $cond = array_merge(array(
            'shop' => array(), 
            'code' => array(),
            'name' => array(),
            'channel' => array(),
            'spec_size' => array(),
        ), $cond);
        if(!empty($cond)){
            $whereStr = array();
            $whereParams = array();
            foreach($cond as $k => $values){
                if(!empty($values)){
                    $whereStr[] = "(product.{$k} IN ('". implode("', '", $values) ."') )";
                }
            }
            $whereStr = implode(' and ', $whereStr);
        }
        if(empty($whereStr)){
            $products = \App\Product::orderBy('createtime', 'desc')->get();
        }else{
            $products = \App\Product::orderBy('createtime', 'desc')
            ->whereRaw($whereStr)
            ->get();
        }
        
        $productsChunk = array();
        foreach($products as $product){
            $productsChunk[$product->code]['name'] = $product->name;
            $productsChunk[$product->code]['code'] = $product->code;
            $productsChunk[$product->code]['records'][] = $product;
        }
        
        return view('product/index', [
            'productsChunk' => $productsChunk, 
            'cond' => $cond, 

            'cond_auto_shops' => DB::table('product')->select('shop')->distinct('shop')->pluck('shop'),
            'cond_auto_codes' => DB::table('product')->select('code')->distinct('code')->pluck('code'), 
            'cond_auto_names' => DB::table('product')->select('name')->distinct('name')->pluck('name'),  
            'cond_auto_channels' => array(
                \App\Product::CHANNEL_DEFAULT => '默认',
                \App\Product::CHANNEL_PROXY => '代销'
            ), 
            'cond_auto_spec_sizes' => DB::table('product')->select('spec_size')->distinct('spec_size')->pluck('spec_size'),
        ]);
    }
    
    public function inoutadjust(Request $request)
    {
        $this->validate($request, [
            'inoutp' => 'array', 
            'inoutp.*.value' => 'nullable|numeric',
            'inoutp.*.type' => 'nullable|required_if:inoutp.*.value,value',
        ], [
            'inoutp.*.numeric' => '如果填写，必须填写数字'
        ]);
        
        $inoutp = $request->input('inoutp');
        $inout_key = ProductInout::repoDeploy($inoutp, $error);
        if($inout_key){
            return redirect(route('product'))->with('inout_status', '库存调整成功。');
        }else{
            return redirect(route('product'))->with('inout_status', $error)->withInput();
        }
    }
    
    public function inoutp(Request $request)
    {
        $cond = $request->input('cond', array());
        $cond = array_merge(array(
            'type' => array(), 
            'shop' => array(), 
            'code' => array(),
            'name' => array(),
            'channel' => array(),
            'spec_size' => array(),
        ), $cond);
        if(!empty($cond)){
            $whereStr = array();
            $whereParams = array();
            foreach($cond as $k => $values){
                if(!empty($values)){
                    if(in_array($k, array('type'))){
                        $whereStr[] = "(product_inout.{$k} IN ('". implode("', '", $values) ."') )";
                    }else{
                        $whereStr[] = "(product.{$k} IN ('". implode("', '", $values) ."') )";
                    }
                }
            }
            $whereStr = implode(' and ', $whereStr);
        }
        if(empty($whereStr)){
            $productInouts = \App\ProductInout::orderBy('createtime', 'desc')->get();
        }else{
            $productInouts = \App\ProductInout::select('product_inout.*')
            ->leftJoin('product', 'product.id_product', '=', 'product_inout.id_product')
            ->orderBy('product_inout.createtime', 'desc')
            ->whereRaw($whereStr, $whereParams)
            ->get();
        }
        
        $productInoutsChunk = array();
        foreach($productInouts as $productInout){
            $productInoutsChunk[$productInout->inout_key]['inout_key'] = $productInout->inout_key;
            $productInoutsChunk[$productInout->inout_key]['createtime'] = $productInout->createtime;
            $productInoutsChunk[$productInout->inout_key]['records'][] = $productInout;
        }
        
        return view('product/inoutp', [
            'productInoutsChunk' => $productInoutsChunk, 
            'cond' => $cond, 

            'cond_auto_types' => array(
                \App\ProductInout::TYPE_REPO_IMPORT_FROM_HUAYING => '华蓥调入',
                \App\ProductInout::TYPE_REPO_IMPORT_FROM_LINGSHUI => '邻水调入',
                \App\ProductInout::TYPE_REPO_IMPORT_FROM_CMP => '公司调入',
                \App\ProductInout::TYPE_REPO_EXPORT_TO_HUAYING => '调出至华蓥',
                \App\ProductInout::TYPE_REPO_EXPORT_TO_LINGSHUI => '调出至邻水',
                \App\ProductInout::TYPE_REPO_EXPORT_TO_CMP => '调出至公司',
                \App\ProductInout::TYPE_REFUND => '客户退货',
                \App\ProductInout::TYPE_SALEOUT => '销售出货'
            ),
            'cond_auto_shops' => DB::table('product')->select('shop')->distinct('shop')->pluck('shop'),
            'cond_auto_codes' => DB::table('product')->select('code')->distinct('code')->pluck('code'), 
            'cond_auto_names' => DB::table('product')->select('name')->distinct('name')->pluck('name'),  
            'cond_auto_channels' => array(
                \App\Product::CHANNEL_DEFAULT => '默认',
                \App\Product::CHANNEL_PROXY => '代销'
            ), 
            'cond_auto_spec_sizes' => DB::table('product')->select('spec_size')->distinct('spec_size')->pluck('spec_size'),
        ]);
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'products.coat' => [new ProductImportFile()],
            'products.trousers' => [new ProductImportFile()],
            'products.shoes' => [new ProductImportFile()],
        ]);
        
        if ($request->hasFile('products')){
            $inout_key = ProductInout::genInoutkey();
            
            if ($request->hasFile('products.coat') && $request->file('products.coat')->isValid()) {
                $path = $request->file('products.coat')->path();
                
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                $spreadsheet = $reader->load($path);
                $activesheet = $spreadsheet->getActiveSheet();
                
                $data = ProductInout::formatImportFileData($activesheet, 'coat');
                $importResult = ProductInout::importCoat($data, $inout_key, $error);
            }
            
            if ($request->hasFile('products.trousers') && $request->file('products.trousers')->isValid()) {
                $path = $request->file('products.trousers')->path();
            
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                $spreadsheet = $reader->load($path);
                $activesheet = $spreadsheet->getActiveSheet();
            
                $data = ProductInout::formatImportFileData($activesheet, 'trousers');
                $importResult = ProductInout::importTrousers($data, $inout_key, $error);
            }
            
            if ($request->hasFile('products.shoes') && $request->file('products.shoes')->isValid()) {
                $path = $request->file('products.shoes')->path();
            
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                $spreadsheet = $reader->load($path);
                $activesheet = $spreadsheet->getActiveSheet();
            
                $data = ProductInout::formatImportFileData($activesheet, 'shoes');
                $importResult = ProductInout::importShoes($data, $inout_key, $error);
            }
            
            $request->session()->flash('import_status', '导入成功');
            // success redirect
        }
        
        
        return view('product/import', [
            
        ]);
    }
}
