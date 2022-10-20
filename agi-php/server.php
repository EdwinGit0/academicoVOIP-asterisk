<?php
   class server{

      protected static function post($jsonData,$option){
         $url = "http://app-63c20f87-add9-4bba-bb8a-47b1404f9ad6.cleverapps.io/".$option;
         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $result = curl_exec($ch);
         curl_close($ch);

         return $result;
      }
   }
?>
