<?php
   class server{

      protected static function post($jsonData,$option){
         $url = "http://app-873282b0-236c-480e-add3-7d08817fc520.cleverapps.io/".$option;
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
