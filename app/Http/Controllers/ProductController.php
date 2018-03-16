<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProductInout;


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
        if(!empty($cond)){
            $whereStr = array();
            $whereParams = array();
            foreach($cond as $k => $v){
                if(!empty($v)){
                    $whereStr[] = "(product.{$k} LIKE ?)";
                    $whereParams[] = "%{$v}%";
                }
            }
            $whereStr = implode(' and ', $whereStr);
        }
        if(empty($whereStr)){
            $products = \App\Product::orderBy('createtime', 'desc')->get();
        }else{
            $products = \App\Product::orderBy('createtime', 'desc')
            ->whereRaw($whereStr, $whereParams)
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
            'cond' => $cond
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
        if(!empty($cond)){
            $whereStr = array();
            $whereParams = array();
            foreach($cond as $k => $v){
                if(!empty($v)){
                    if(in_array($k, array('type'))){
                        $whereStr[] = "(product_inout.{$k} = ?)";
                        $whereParams[] = $v;
                    }elseif(in_array($k, array('spec_size'))){
                        $whereStr[] = "(product.{$k} = ?)";
                        $whereParams[] = $v;
                    }else{
                        $whereStr[] = "(product.{$k} LIKE ?)";
                        $whereParams[] = "%{$v}%";
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
            'cond' => $cond
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
