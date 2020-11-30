<?php 

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

class Mother {

    function response($status = true, $data = []){
            $out = [
                'status' => $status
            ];
            if(is_array($data)){
                $out['data'] = $data;
            } elseif(is_object($data)){
                $out['data'] = (object) $data;
            } else {
                $out['message'] = $data;
            }

            header('content-type: application/json');
            echo json_encode($out);
            wp_die();
        }

        function adminOnly(){
            if( !current_user_can("manage_options") ) {
                $this->response(false, 'You are not authorized');
            }
            return true;
        }
        
} 
