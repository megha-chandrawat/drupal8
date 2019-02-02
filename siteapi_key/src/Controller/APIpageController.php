<?php
/**
 * @file
 * Contains \Drupal\siteapi_key\Controller.
 */

namespace Drupal\siteapi_key\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult; 
use Drupal\node\Entity\Node;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Config\ConfigFactoryInterface;

class APIpageController extends ControllerBase {

  public function APIpage() {
    $parameters = \Drupal::request()->getpathInfo();
    $arg  = explode('/',$parameters);
    #Check Node exist in the system or not
    $exist_node = \Drupal::entityQuery('node')->condition('nid',$arg[3])->execute();
    $node = isset($exist_node) ? Node::load($arg[3]): null;
    #Check node type
    $node_type= isset($node) ? $node->get('type')->getValue()[0]['target_id'] == 'page' : false;
    #Site API key saved in the system
    $site_api_key=\Drupal::config('system.site')->get('site_api_key');
    #Get API key from Url
    $api_key_check = $arg[2];
    if($exist_node && $node_type && ($api_key_check === $site_api_key))
     {
        $field_defs = $node->getFieldDefinitions();
        #Get all node fields in an array
        $node_fields = array();
        foreach ($field_defs as $field_name => $val) {
          $node_fields[$field_name] = trim($node->get($field_name)->getValue()[0]['value']);
        }
        $node_fields['siteapi_key'] = $site_api_key;
        header("Content-type: application/json"); 
        #Json encode the node fields
        $res = json_encode($node_fields);
        return array(
          '#markup' => $res
        );
    }
    else{
        throw new AccessDeniedHttpException();
    }

  }
}