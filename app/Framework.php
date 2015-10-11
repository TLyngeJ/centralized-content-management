<?php
/**
 * Base class for the application.
 */
 
 /**
  * This class bootstraps the application and handles all requests.
  */
 class Framework {
     
     /**
      * Handle requests.
      */
     public function run($request, $response) {
        $response->writeHead(200, array('Content-Type' => 'text/plain'));
        $response->end("Hello World!!\n");
     }
 }