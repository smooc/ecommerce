<?php

namespace App\Http\Controllers;

class xmlToSqlController extends Controller
{
    public function xmltosql(){
         set_time_limit(3000);
         ini_set('memory_limit','512M');

        $xmlFileUrl = ''; // link for the XML file

       try {
            $xmlString=file_get_contents($xmlFileUrl);
            $xmlObject = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
            $json = json_encode($xmlObject);
            $array = json_decode($json,TRUE);
            for ($i=0; $i < count($array['product']); $i++){
                $this->addToDatabase($array['product'][$i]);
            }


       } catch (\Throwable $th) {
           //throw $th;
           echo $th;
       }
        
    }

    function addToDatabase($array){
        $brands = \App\models\brand::all();
        $marka_link = preg_replace('/[[:space:]]+/', '-', $array['marka']);
        $marka_link = $this->replaceTurkishChar($marka_link);
        $marka = $this->replaceTurkishChar($array['marka']);
        if (count($brands) > 0) {
            foreach($brands as $brand){
                if (strpos($brand->link, $marka_link) !== false) {
                    $brand_id = $brand->id;
                }
            }
        }
        $gender = '';
        if(isset($array['ozellikler'])){
            foreach ($array['ozellikler'] as $ozellik) {
                foreach ($ozellik as $value) {
                    if(isset($value['adi'])){
                        if(strtolower($value['adi']) == 'cinsiyet'){
                            $gender = strtoupper($value['degeri']);
                        }   
                    }
                }
            }
        }
        $color = '';
        if(isset($array['ozellikler'])){
            foreach ($array['ozellikler'] as $ozellik) {
                foreach ($ozellik as $value) {
                    if(isset($value['adi'])){
                        if(strtolower($value['adi']) == 'renk'){
                            $color = $value['degeri'];
                        }   
                    }
                }
            }
        }
        if(!isset($brand_id)){
            $newBrand = \App\models\brand::create([
                'brand_code' => $array['kategorikodu'],
                'brand_name' => $marka,
                'link' => $marka_link,
                'image' => ''
            ]);
            $brand_id = $newBrand->id;
        }   

        $product_link = preg_replace('/[[:space:]]+/', '-', $array['urunadi']);
        $product_link = $this->replaceTurkishChar($product_link);
        if(is_array($array['indirimlifiyat'])){
            if(count($array['indirimlifiyat']) < 1){
                $array['indirimlifiyat'] = $array['satisfiyati'];
                $array['indirimlifiyatvergili'] = $array['satisfiyativergili'];
            }
        }
        $product = \App\models\product::create([
            'brand_id' => $brand_id,
            'name' => $array['urunadi'],
            'stock' => $array['stoksayisal'],
            'price' => $array['satisfiyati'],
            'vat_price' => $array['satisfiyativergili'],
            'discounted_price' => $array['indirimlifiyat'],
            'discounted_vat_price' => $array['indirimlifiyatvergili'],
            'warranty' => $array['garantisuresi'],
            'model' => $array['model'],
            'barcode' => $array['urunkodu'],
            'gender' => $gender,
            'color' => $color,
            'link' => $product_link
        ]);
        if(isset($array['ayrintilar2'])){
            \App\models\product_specification::create([
                'specifications' => $array['ayrintilar2'],
                'product_id' => $product->id,
            ]);
        }
        foreach ($array['resimler'] as $images) {
             $i = 0;
            if(is_array($images)){
                foreach ($images as $image) {
                    \App\models\product_image::create([
                        'product_id' => $product->id,
                        'image' => $image,
                        'order' => $i, 
                    ]);
                    $i++;
                }
            }else{
                \App\models\product_image::create([
                    'product_id' => $product->id,
                    'image' => $images,
                    'order' => 0, 
                ]);
            }
        }
    }

    function replaceTurkishChar($stringToChange){
        $lookForIt = array ('ı', 'İ', 'ç', 'Ç', 'Ü', 'ü', 'Ö', 'ö', 'ş', 'Ş', 'ğ', 'Ğ');
        $replaceToThis = array ('i', 'I', 'c', 'C', 'U', 'u', 'O', 'o', 's', 'S', 'g', 'G');
        $stringToChange = str_replace($lookForIt, $replaceToThis, $stringToChange);
        return $stringToChange;
    }
}
